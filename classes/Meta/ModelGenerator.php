<?php
namespace IslandFuture\Sfw\Meta;

/**
 * Класс предназначен для генерации различного рода блоков по мета-модели или обычной модели
 *
 */
class ModelGenerator extends Generator
{
    // declare in parents: protected $arParams = array();
    
    public function generate ()
    {
        
        if (! file_exists($this->sPathMetaGen.$this->sClassname)) {
            echo "Creade dir: ".$this->sPathMetaGen.$this->sClassname."\n";
            mkdir($this->sPathMetaGen.$this->sClassname);
        }

        if (! file_exists($this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'models')) {
            echo "Creade dir: ".$this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR."models\n";
            mkdir($this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'models');
        }
        
        $this->saveModel();
    }
    
    public function saveModel ()
    {
        $arVars = array();
        $arVars['classname'] = $this->sClassname;
        $arVars['tablename'] = $this->sTablename;
        $arVars['database'] = $this->sDatabase;
        $arVars['titlename'] = $this->sTitlename;

        $arVars['clear_fields'] = '';
        $arVars['defaults'] = '';
        $arVars['relations_fields'] = '';

        foreach ($this->arFields as $sField => $arField) {
            if (isset($arField['sPrimary']) && 'yes'==$arField['sPrimary']) {
                $arVars['id_default'] = $sField;
            }

            $arVars['clear_fields'] .= "            '".$sField."' => null,\n";
            
            if (isset($arField['sDefault'])) {
                $arVars['defaults'] .= "            '".$sField."' => '".$arField['sDefault']."',\n";
            }
        }

        // $arVars['clear_fields'] = "        return array\n        (".$arVars['clear_fields']."        );\n";

        foreach ($this->arRelations as $sRelation => $arRelation) {
            if ('VIRTUAL' == $arRelation[0]) {
                $sValues = '';
                if (is_array($arRelation[2])) {
                    foreach($arRelation[2] as $idx => $arVals) {
                        $sValues .= "            '$idx' => array('".implode("','",$arVals)."'),\n";
                    }
                }
                
                $arVars['relations_fields'] .= <<<EOT

    public function {$sRelation}(\$isOne=true, \$idx=0)
    {
        \$arRelations = array(
$sValues
        );
        return \$isOne
            ? (
                isset(\$arRelations[\$this->{$arRelation[1]}])
                ? \$arRelations[\$this->{$arRelation[1]}][\$idx]
                : null
            )
            : \$arRelations;
    }

EOT;
                
                //"            '".$sRelation."' => array('VIRTUAL','".$arRelation[1]."',array(\n$sValues)),\n";
            } else {
                
                $arVars['relations_fields'] .=<<<EOT
                
    /**
     * @return \\${arRelation[2]}
     */    
    public function ${sRelation}()
    {
        return \\IslandFuture\\Sfw\\Data\\Storages::getOne(
            array(
                'sModel' => '${arRelation[2]}',
                'arFilter' => array(
                    '${arRelation[3]}' => array('=' => \$this->${arRelation[1]} )
                )
            )
        );
    }
    
EOT;
                
                //"            '".$sRelation."' => array('".$arRelation[0]."','".$arRelation[1]."','".$arRelation[2]."','".$arRelation[3]."'),\n";
            }
        }
        
        /* open class auto */
        \IslandFuture\Sfw\Template::one()->parse($this->sPathMetaTemplates.'models'.DIRECTORY_SEPARATOR.'class_auto.php',$arVars);
        echo 'Save model auto to file: '.$this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.$this->sClassname.'Auto.php'."\n";
        \IslandFuture\Sfw\Template::one()->saveTo($this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.$this->sClassname.'Auto.php');

        \IslandFuture\Sfw\Template::one()->parse($this->sPathMetaTemplates.'models'.DIRECTORY_SEPARATOR.'class.php',$arVars);
        echo 'Save model to file: '.$this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.$this->sClassname.'.php'."\n";
        \IslandFuture\Sfw\Template::one()->saveTo($this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.$this->sClassname.'.php');
    }
}