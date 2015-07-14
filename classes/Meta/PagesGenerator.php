<?php
namespace IslandFuture\Sfw\Meta;

/**
 * Класс предназначен для генерации различного рода блоков по мета-модели или обычной модели
 *
 */
class PagesGenerator extends Generator
{
    // declare in parents: protected $arParams = array();
    
    public function generate ()
    {
        if (! file_exists($this->sPathMetaGen.$this->sClassname)) {
            echo "Creade dir: ".$this->sPathMetaGen.$this->sClassname."\n";
            mkdir($this->sPathMetaGen.$this->sClassname);
        }

        if (! file_exists($this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'pages')) {
            echo "Creade dir: ".$this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR."pages\n";
            mkdir($this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'pages');
        }
        
        if (! $this->arFields || sizeof($this->arFields) == 0) {
            echo "Not found field for model [".$this->sClassname."]\n";
            return false;
        }

        $this->savePages();
    }
    
    public function savePages()
    {
        $arVars = array();
        $arVars['classname'] = $this->sClassname;
        $arVars['classlower'] = strtolower($this->sClassname);
        $arVars['tablename'] = $this->sTablename;
        $arVars['database'] = $this->sDatabase;
        $arVars['titlename'] = $this->sTitlename;

        foreach ($this->arFields as $sField => $arField) {
            
            if (isset($arField['sPrimary']) && 'yes'==$arField['sPrimary']) {
                $arVars['id_name'] = $sField;
                if ('char' == $arField['sType'] || 'varchar' == $arField['sType']) {
                    $arVars['primary_type'] = '(string)';
                }
            }
        }

        $arFiles =  \IslandFuture\Sfw\Tools::getListFilesEx($this->sPathMetaTemplates.'pages'.DIRECTORY_SEPARATOR, 0, $this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.$arVars['classlower'].DIRECTORY_SEPARATOR, $arVars);

        foreach($arFiles as $sParse => $sFile) {
            \IslandFuture\Sfw\Template::one()->parse($sParse,$arVars);
            echo 'Save file: '.$sFile."\n";
            \IslandFuture\Sfw\Template::one()->saveTo($sFile);
        }

    }
    //end function saveListPage
}
