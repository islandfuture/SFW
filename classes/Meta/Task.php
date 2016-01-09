<?php
namespace IslandFuture\Sfw\Meta;

/**
 * Класс предназначен для генерации различного рода блоков по мета-модели или обычной модели
 */
abstract class Task
{
    protected $arParams = array();
    
    
    
    public function __construct($arParams=array())
    {
        $this->arParams = $arParams;
    }

    public function __get($sParamName)
    {
        if(!isset($this->arParams[$sParamName]) )
            $this->arParams[$sParamName] = '';
        
        return $this->arParams[$sParamName];
    }
    
    public function __set($sParamName, $sVal)
    {
        $this->arParams[$sParamName] = $sVal;
    }

    abstract public function run();
    
    
}
