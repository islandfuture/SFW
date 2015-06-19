<?php
namespace IslandFuture\Sfw\Meta;

/**
 * Класс предназначен для генерации различного рода блоков по мета-модели или обычной модели
 *
 */
class Generator extends Task
{
    // declare in parents: protected $arParams = array();
    
    /**
     * Загружаем данные из мета модели или из обычной модели, для последующей генерации нужного кода
     */
    public function loadMetaModel($sMetaName)
    {
        if (! file_exists($this->sPathMetaModels.$sMetaName.'.php')) {
            throw new \Exception('File meta-model ['.$sMetaName.'] not found');
        }

        $arMeta = include $this->sPathMetaModels.$sMetaName.'.php';
        
        $this->sClassname = $arMeta['sClassname'];
        $this->sDatabase = $arMeta['sDatabase'];
        $this->sTablename = $arMeta['sTable'];
        $this->sTitlename = empty($arMeta['sTitle']) ? $arMeta['sClassname'] : $arMeta['sTitle'];

        $this->arFields = $arMeta['arFields'];
        $this->arRelations = $arMeta['arRelations'];
    }
    
    public function run()
    {
        $this->sPathApp = \IslandFuture\Sfw\Application::one()->PATH_APP;
        $this->sPathMeta = $this->sPathApp.'meta'.DIRECTORY_SEPARATOR;
        $this->sPathMetaGen = $this->sPathApp.'meta'.DIRECTORY_SEPARATOR.'gen'.DIRECTORY_SEPARATOR;
        $this->sPathMetaModels = $this->sPathApp.'meta'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR;
        $this->sPathMetaTemplates = \IslandFuture\Sfw\Application::one()->PATH_SFW.'meta'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR;

        if (! file_exists($this->sPathMeta)) {
            echo "Creade dir: ".$this->sPathMeta."\n";
            mkdir($this->sPathMeta);
        }

        if (! file_exists($this->sPathMetaGen)) {
            echo "Creade dir for generate code: ".$this->sPathMetaGen."\n";
            mkdir($this->sPathMetaGen);
        }

        if (isset($_GET['model'])) {
            $this->loadMetaModel($_GET['model']);
        }
        
        $this->generate();
    }
    
    public function generate()
    {
        die('Define generate function for current task'."\n");
    }
}
