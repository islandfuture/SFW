<?php
namespace IslandFuture\Sfw\Meta;

/**
 * Класс предназначен для генерации различного рода блоков по мета-модели или обычной модели
 *
 */
class TableGenerator extends Generator
{
    // declare in parents: protected $arParams = array();
    
    public function generate ()
    {
        
        if (! file_exists($this->sPathMetaGen.$this->sClassname)) {
            echo "Creade dir: ".$this->sPathMetaGen.$this->sClassname."\n";
            mkdir($this->sPathMetaGen.$this->sClassname);
        }

        if (! file_exists($this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'sql')) {
            echo "Creade dir: ".$this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR."sql\n";
            mkdir($this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'sql');
        }
        
        if (! $this->arFields || sizeof($this->arFields) == 0) {
            echo "Not found field for model [".$this->sClassname."]\n";
            return false;
        }

        $this->saveSql();
    }
    
    public function saveSql ()
    {
        $arVars = array();
        $arVars['classname'] = $this->sClassname;
        $arVars['tablename'] = $this->sTablename;
        $arVars['database'] = $this->sDatabase;
        $arVars['titlename'] = $this->sTitlename;

        $arVars['sql_fields'] = '';
        $arVars['defaults'] = '';
        $arVars['relations_fields'] = '';

        foreach ($this->arFields as $sField => $arField) {
            
            if ($arField['sType'] == 'guid') {
                $arField['sType'] = 'char';
                $arField['iLength'] = 36;
                $arField['sCodepage'] = 'ascii';
                $arField['sBinary'] = 'yes';
            }

            $arVars['sql_fields'] .= "    ".'`'.$sField.'` '.$arField['sType'].' ';

            if (isset($arField['iLength']) &&  $arField['iLength'] > 0) {
                $arVars['sql_fields'] .= '('.$arField['iLength'].') ';
            }

            if (isset($arField['sCodepage']) ) {
                $arVars['sql_fields'] .= ' CHARACTER SET '.
                        $arField['sCodepage'].
                    ' COLLATE '.
                        $arField['sCodepage'].
                            ( isset($arField['sBinary']) && $arField['sBinary'] == 'yes' ? '_bin' : '_general_ci' ).
                    ' ';
            }

            if (empty($arField['isNull']) || $arField['isNull'] == 'no' ) {
                $arVars['sql_fields'] .= 'NOT NULL ';
            }

            if (isset($arField['sDefault']) ) {
                if ($arField['sDefault'] == 'AUTOINC' || $arField['sDefault'] == 'auto_increment') {
                    $arVars['sql_fields'] .= ' auto_increment ';
                } else {
                    if( in_array(strtolower($arField['sDefault']), array('now()','uuid()','current_timestamp')) ) {
                        $arVars['sql_fields'] .= "DEFAULT ".$arField['sDefault']." ";
                    } else {
                        if( $arField['sDefault'] == 'UUID' ) {
                            $arField['sDefault'] = '';
                        }
                        
                        if( $arField['sDefault'] != 'NULL' ){
                            $arVars['sql_fields'] .= "DEFAULT '".$arField['sDefault']."' ";
                        }
                    }
                }
            }

            if( isset($arField['sComment']) ) {
                $arVars['sql_fields'] .= "COMMENT '".$arField['sComment']."'";
            } else if( isset($arField['sTitle']) ){
                $arVars['sql_fields'] .= "COMMENT '".$arField['sTitle']."'";
            }

            if( isset($arField['sPrimary']) && $arField['sPrimary'] == 'yes') {
                $arVars['primary_key'] = $sField;
            }

            $arVars['sql_fields'] .= "\n";
        }
        
        $arVars['sql_fields'] .= '    PRIMARY KEY  (`'.$arVars['primary_key'].'`)';

        $arVars['codepage'] = $this->sCodepage;
        $arVars['comment'] = $this->sComment;

        
        /* open */
        \IslandFuture\Sfw\Template::one()->parse($this->sPathMetaTemplates.'sql'.DIRECTORY_SEPARATOR.'create.sql',$arVars);
        echo 'Save SQL to file: '.$this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'create.sql'."\n";
        \IslandFuture\Sfw\Template::one()->saveTo($this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'create.sql');

        \IslandFuture\Sfw\Template::one()->parse($this->sPathMetaTemplates.'sql'.DIRECTORY_SEPARATOR.'drop.sql',$arVars);
        echo 'Save SQL to file: '.$this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'drop.sql'."\n";
        \IslandFuture\Sfw\Template::one()->saveTo($this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'drop.sql');
        
        
        if (is_array($this->arRelations) && sizeof($this->arRelations ) > 0) {
            echo "Generate SQL constraint\n";
            $sSQL = '';
            $sSQL2 = '';
            
            foreach ($this->arRelations as $sRelation => $arRelation) {
                $sTypeId  = $arRelation[0];

                if ('ONE' == $sTypeId) {
                    $sLocalId = $arRelation[1];
                    $sLocalTable = $this->sTablename;
                    $sRefId   = $arRelation[3];
                    $sRefModel = $arRelation[2]; /* model name */

                    /* открываем файл с описанием мета модели */
                    if (file_exists($this->sPathMetaModels.$sRefModel.'.php')) {
                        $arMeta = include $this->sPathMetaModels.$sRefModel.'.php';
                        $sRefTable = $arMeta['sTable'];
                    } else {
                        $sRefTable = $sRefModel::getTable();
                    }
            
                    $sConstraint = 'fk_'.$sLocalTable.'_'.$sLocalId.'_'.$sRefTable;

/****************************************************************************/
/***  шаблон для записи в файл отношения связи с другими таблицами        ***/
/****************************************************************************/
$sSQL .= <<<EOT

ALTER TABLE `{$sLocalTable}` ADD CONSTRAINT `{$sConstraint}` FOREIGN KEY (`{$sLocalId}`)
    REFERENCES `{$sRefTable}` (`{$sRefId}`)
    ON DELETE RESTRICT
    ON UPDATE RESTRICT;
    
EOT;

$sSQL2 .= <<<EOT

ALTER TABLE `{$sLocalTable}` DROP FOREIGN KEY `{$sConstraint}`;

EOT;

/****************************************************************************/
                }
            }
            //end foreach

            if ($sSQL > '') {
                echo 'Save SQL to file: '.$this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'foreign.sql'."\n";
                $rFile = fopen($this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'foreign.sql','w');
                fwrite($rFile, $sSQL);
                fclose($rFile);

                echo 'Save SQL to file: '.$this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'foreign_drop.sql'."\n";
                $rFile = fopen($this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'foreign_drop.sql','w');
                fwrite($rFile, $sSQL2);
                fclose($rFile);
            }
        }
        // end if arRelations


    }
    //end function saveSql
}
