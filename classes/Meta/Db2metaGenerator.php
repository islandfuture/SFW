<?php
namespace IslandFuture\Sfw\Meta;

/**
 * Класс предназначен для генерации различного рода блоков по мета-модели или обычной модели
 */
class Db2metaGenerator extends Generator
{
    // declare in parents: protected $arParams = array();
    
    /**
     * Загружаем данные из мета модели или из обычной модели, для последующей генерации нужного кода
     */
    public function loadMetaModel($sMetaName)
    {
        $this->sTablename = $sMetaName;
        $this->sClassname = ucfirst($sMetaName);
        
        $sSql = "SELECT database() as `database`";
        $arFields = \IslandFuture\Sfw\Data\Storages::one()->queryAll($sSql);
        if (empty($arFields[0]['database'])) {
            throw new \Exception('Не выбрана БД');
        }
        $this->sDatabase = $arFields[0]['database'];

        $sSql = "SHOW FULL COLUMNS FROM `".$this->sTablename."` FROM `".$this->sDatabase."`";
        echo "Run: $sSql\n";
        $arFields = \IslandFuture\Sfw\Data\Storages::one()->queryAll($sSql);
        
        $sFileds = '';
        $sRelations = '';
        
        foreach ($arFields as $arField) {
            $iPos = strpos($arField['Type'], '(', 1);
            if (false === $iPos) {
                $sType = $arField['Type'];
                $iLength = 0;
            } else {
                $iLength = (int)substr($arField['Type'], $iPos+1, strpos($arField['Type'], ')', $iPos)-$iPos-1);
                $sType = substr($arField['Type'], 0, $iPos);
                
                if(in_array($sType, array('int', 'bigint')) ) {
                    $iLength = 0;
                }
            }
            
            $isPrimary = ($arField['Key'] == 'PRI' ? 'yes' : 'no');
            $isNull = ($arField['Key'] == 'Null' ? 'yes' : 'no');
            
            if ($sType == 'char'
                || $sType == 'varchar'
                || $sType == 'text'
            ) {
                if ($sType == 'char'
                    && $iLength >= 36
                    && (                    $arField['Key'] == 'PRI'
                    || $arField['Default'] == 'UUID()'
                    || $arField['Key'] == 'MUL')
                ) {
                    $sCodepage = 'ascii';
                    $sBinary = 'yes';
                } else {
                    if (isset($arField['Collation']) && $arField['Collation'] == 'ascii_bin') {
                        $sCodepage = 'ascii';
                        $sBinary = 'bin';
                    } else {
                        $sCodepage = 'utf8';
                        $sBinary = 'no';
                    }
                }
    
                if ('text' == $sType) {
                    $sFileds .= "        '".$arField['Field']."' => array(\n".
                        "            'sType' => '".$sType."',\n".
                        "            'sPrimary' => '".$isPrimary."',\n".
                        "            'sCodepage' => '".$sCodepage."',\n".
                        "            'sBinary' => '".$sBinary."',\n".
                        "            'sDefault' => '".$arField['Default']."',\n".
                        "            'sComment' => '".$arField['Comment']."',\n".
                        "            'isNull' => '".$isNull."'\n".
                    "        ),\n";
                } else {
                    $sFileds .= "        '".$arField['Field']."' => array(\n".
                        "            'sType' => '".$sType."',\n".
                        "            'sPrimary' => '".$isPrimary."',\n".
                        "            'iLength' => '".$iLength."',\n".
                        "            'sCodepage' => '".$sCodepage."',\n".
                        "            'sBinary' => '".$sBinary."',\n".
                        "            'sDefault' => '".$arField['Default']."',\n".
                        "            'sComment' => '".$arField['Comment']."',\n".
                        "            'isNull' => '".$isNull."'\n".
                    "        ),\n";
                }
                
            } else {
            
                if ($arField['Extra'] == 'auto_increment') {
                    $arField['Default'] = $arField['Extra'];
                }
                
                $sFileds .= "        '".$arField['Field']."' => array(\n".
                    "            'sType' => '".$sType."',\n".
                    "            'sPrimary' => '".$isPrimary."',\n".
                    "            'sDefault' => '".$arField['Default']."',\n".
                    "            'sComment' => '".$arField['Comment']."',\n".
                    "            'isNull' => '".$isNull."'\n".
                "        ),\n";
                
            } //end if else
            
        } /* end foreach $arFields */

        $sSql = "SELECT * FROM `information_schema`.`KEY_COLUMN_USAGE` WHERE CONSTRAINT_SCHEMA like '".$this->sDatabase."' AND TABLE_NAME = '".$this->sTablename."' AND REFERENCED_TABLE_NAME is not null";
        echo "Create relations (one) for table [$sSql]\n";
        $arRelations = \IslandFuture\Sfw\Data\Storages::one()->queryAll($sSql);
        if ($arRelations) {
            foreach ($arRelations as $arRelation) {
                $iPos = strpos($arRelation['COLUMN_NAME'], 'Id');
                if (false === $iPos) {
                    $sRelname = $arRelation['COLUMN_NAME'];
                } else {
                    $sRelname = substr($arRelation['COLUMN_NAME'], 0, $iPos);
                }
                $sRelations .= "        '".$sRelname."' => array('ONE','".$arRelation['COLUMN_NAME']."','".$arRelation['REFERENCED_TABLE_NAME']."','".$arRelation['REFERENCED_COLUMN_NAME']."'),\n";
            }//end foreach

        } // end if

        //формируем обратные отношения (многие к одному)
        $sSql = "SELECT * FROM `information_schema`.`KEY_COLUMN_USAGE` WHERE CONSTRAINT_SCHEMA like '".$this->sDatabase."' AND REFERENCED_TABLE_NAME LIKE '".$this->sTablename."'";
        echo "Create relations (multi) for table [$sSql]\n";

        $arRelations = \IslandFuture\Sfw\Data\Storages::one()->queryAll($sSql);
        if ($arRelations) {
            foreach ($arRelations as $arRelation) {
                $sRelname = $arRelation['TABLE_NAME'];
                $sRelations .= "        '".$sRelname."' => array('MULTI','".$arRelation['REFERENCED_COLUMN_NAME']."','".$arRelation['TABLE_NAME']."','".$arRelation['COLUMN_NAME']."'),\n";
            }//end foreach
        }
        
        $this->sFileds = <<<EOT
<?php
return array(
    'sTable' => '{$this->sTablename}',
    'sClassname' => '{$this->sClassname}',
    'sDatabase' => '{$this->sDatabase}',
    'arFields' => array(
$sFileds
    ),
    'arRelations' => array(
        $sRelations
    )
);

EOT;
    }
    
    public function generate()
    {
        
        if (! file_exists($this->sPathMetaGen.'meta')) {
            echo "Creade dir: ".$this->sPathMetaGen.'meta'."\n";
            mkdir($this->sPathMetaGen.'meta');
        }

        echo "Save to file: ".$this->sPathMetaGen.'meta'.DIRECTORY_SEPARATOR.$this->sClassname.'.php';
        $rFile = fopen($this->sPathMetaGen.'meta'.DIRECTORY_SEPARATOR.$this->sClassname.'.php', 'w');
        fwrite($rFile, $this->sFileds);
        fclose($rFile);
    }
}
