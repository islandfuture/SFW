<?php
namespace IslandFuture\Sfw;
/**
 * Класс предназначен для работы с шаблонами. Фактически происходит
 * открытие файла и замена в нем слов определенного формата на переданные
 * значения и отображение получившегося
 *
 * @link    https://github.com/islandfuture/SFW
 * @author  Michael Akimov <michael@island-future.ru>
 * @version GIT: $Id$
 * 
 * @example
 *     $result = \IslandFuture\Sfw\Template::one()->parse('file.tpl', array('param1'=>'value1',...));
 *     замена спец слов на переданные параметры
 *
 * @example \IslandFuture\Sfw\Template::one()->print('file.tpl', array('param1'=>'value1',...));
 *     парсинг и печать 
 */
class Template extends \IslandFuture\Sfw\Only
{
    protected $_cache;
    protected $_last;
    
    protected $sPath = '';
    
    public function __construct()
    {
    }//end class

    public function makeDir($sPath) 
    {
        if (substr($sPath, 0, 1) != DIRECTORY_SEPARATOR && substr($sPath, 1, 2)!=':'.DIRECTORY_SEPARATOR ) {
            $sPath = getcwd().DIRECTORY_SEPARATOR.$sPath;
        }

        if (substr($sPath, 1, 2) == ':'.DIRECTORY_SEPARATOR  ) {
            $tmppath = substr($sPath, 0, 2);
            $sPath = substr($sPath, 2, strlen($sPath));
        } else {
            $tmppath = '';
        }
        $arStruct = explode(DIRECTORY_SEPARATOR, $sPath);
        //поднимаемся по указанному пути, пока не доберемся до директории, которая уже существует
        for ($i = 0; $i < sizeof($arStruct);$i++) {
            if ($arStruct[$i] == "") {
                continue;
            }

            $tmppath .= DIRECTORY_SEPARATOR.$arStruct[$i];

            //echo "Проверяем: $tmppath\n";

            if(! file_exists($tmppath)) {
                //echo "Не существует\n";
                break;
            }
        }

        if(! file_exists($tmppath)) {
            echo "Create directory: $tmppath\n";
            mkdir($tmppath);
        }

        for($j=$i+1; $j<sizeof($arStruct); $j++) {
            $tmppath .= DIRECTORY_SEPARATOR.$arStruct[$j];
            if(!file_exists($tmppath) ) {
                echo "create directory: $tmppath\n";
                mkdir($tmppath);
            }
        }//end for

    }
    
    public function setPath($sPath)
    {
        $this->sPath = $sPath;
        return $this;
    }

    public function parseContent($sContent, $arParams=array(),$lbracket='<:', $rbracket=':>')
    {
        $arValues = array();
        $arVars = array();
        foreach($arParams as $sKey => $sVal) {
            $arVars[] = '/'.$lbracket.$sKey.$rbracket.'/i';
            $arValues[] = $sVal;
        }
        $this->_last = preg_replace($arVars, $arValues, $sContent);
        return $this->_last;
    }
    
    public function parse($sFile, $arParams=array())
    {
        if(! file_exists($sFile)) {
            if(!file_exists($this->sPath.$sFile)  ) {
                throw new \Exception("File ".$sFile." not found\n");
            }
            $sFullName = $this->sPath.$sFile;
        } else {
            $sFullName = $sFile;
        }

        $sContent = file_get_contents($sFullName);
        $arValues = array();
        $arVars = array();
        foreach($arParams as $sKey => $sVal) {
            $arVars[] = '/<:'.$sKey.':>/i';
            $arValues[] = $sVal;
        }
        $this->_last = preg_replace($arVars, $arValues, $sContent);
        return $this->_last;
    }

    public function show($sFile, $arParams=array())
    {
        echo $this->parse($sFile, $arParams);
    }

    public function saveTo($sFile)
    {
        $sDirname = dirname($sFile);
        if(! file_exists($sDirname)) {
            echo "Сохраняем файл: $sFile \n";
            $this->makeDir($sDirname);
        }
        
        $f = fopen($sFile, 'w');
        fwrite($f, $this->_last);
        fclose($f);
    }


} /* end class */
