<?php
namespace IslandFuture\Sfw;

/**
 * Класс предназначен для работы с блоками и их шаблонами.
 * Класс открывает указанный блок, выполняет его, а затем открывает шаблон,
 * в котором можно вставлять переданные переменные
 *
 * @link    https://github.com/islandfuture/SFW
 * @author  Michael Akimov <michael@island-future.ru>
 * @version GIT: $Id$
 * 
 * @example 
 *  \IslandFuture\Sfw\Block::run(
 *      'bloks.action',
 *      array('param1'=>value1,...),
 *      array('template' => 'action', 'buffered' => true)
 *  );
 *      
 * Template [action.php]:
 *      <html><?=$this->param1; ?></html>
 *      
 * Output:
 *      <html>value1</html>
 */
class Block extends Only
{
    // @var array массив параметров используемых в блоке и потом в шаблоне
    protected $arParams = array();
    
    public $arBuffered = array();
    
    // @var integer уровень глубины вызова блока (нужно когда внутри блока вызываешь еще один блок и т.д.)
    protected $iCur = -1;
    
    /**
     * Метод отвечает за запуск блоков, их отображение, а также за их кеширование.
     *
     * @param string $name        название блока (ищет файл blocks/$name/block.php )
     * @param array  $arParams    параметры инициализации блока.
     * @param array  $arSysParams параметры для работы блока (например буферизировать вывод или нет, кешировать или нет)
     *
     * @return \IslandFuture\Sfw\Block
     */
    public function run($sBlockName, $arParams=array(), $arSysParams=array())
    {
        $this->iCur++;
        $this->arParams[$this->iCur] = array();
        $sTemplate = false;
        try
        {
            
            $sPrefix = str_replace('.', '_', $sBlockName);
            $oApp = \IslandFuture\Sfw\Application::one();
            
            if(is_array($arParams) ) {
                $this->arParams[ $this->iCur ] = $arParams;
            }
            $arParams = array();

            $this->sBlockName = $sBlockName;
            $oBlock->arBuffered[$sBlockName.':result'] = null;
            
            $sFileName = $oApp->PATH_APP.'blocks'.DIRECTORY_SEPARATOR.$sBlockName.DIRECTORY_SEPARATOR.'block.php';
            if(!file_exists($sFileName) ) {
                throw new \Exception("блок [$sBlockName] не найден в разделе блоков");
                return '';
            }
                
            if(empty($this->arBuffered[$sBlockName.':html']) ) {
                $this->arBuffered[$sBlockName.':html'] = '';
            }
    
            $isBuffered = isset($arSysParams['buffered']) && $arSysParams['buffered'] ? true : false ;

            if(!empty($arSysParams['template']) ) {
                $sTemplate = 'blocks'.DIRECTORY_SEPARATOR.$sBlockName.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$arSysParams['template'];
                $this->sTemplatePath = $oApp->PATH_APP.$sTemplate.'.php';
                if(!file_exists($this->sTemplatePath) ) {
                    throw new \Exception("Шаблон [".($arSysParams['template'])."] для блока [$sBlockName] не найден.");
                }
            }
    
            $isDebug = isset($arSysParams['debug']) && $arSysParams['debug'] ? true : false;
    
            if($isBuffered ) {
                ob_start();
            }
            
            if($isDebug) {
                echo '<div class="sfw_block_cont sfw_block_'.$sPrefix.'" blockfile="'.$sFileName.'">';
            }
    
            include $sFileName;

            if($sTemplate ) {
                echo $this->show();
            }
            
            if($isDebug) {
                echo '</div>';
            }
    
            if($isBuffered ) {
                $this->arBuffered[$sBlockName.':html'] = ob_get_contents();
                ob_end_clean();
            }

            $this->arParams[$this->iCur] = array();      
            $this->iCur--;
        }
        catch( \PdoException $e)
        {
            $this->arParams[$this->iCur] = array();      
            $this->iCur--;
            $oBlock->arBuffered[$sBlockName.':result'] = false;
            throw $e;
        }
        catch( \Exception $e)
        {
            $this->arParams[$this->iCur] = array();      
            $this->iCur--;
            $oBlock->arBuffered[$sBlockName.':result'] = false;
            throw $e;
        }
        
        return $this;
    }
    
    /**
     * Функция отображает содержимое переданного шаблона
     * @var string название шаблона
     * @var array массив переменных для использования в шаблоне
     */
    public function show() 
    {
        include $this->sTemplatePath;
    }
    
    public function __get($name)
    {
        return isset($this->arParams[$this->iCur][$name]) ? $this->arParams[$this->iCur][$name] : '' ;
    }

    public function __set($name, $val)
    {
        $this->arParams[$this->iCur][$name] = $val ;
    }
    
    /**
     * возвращаем все параметры для текущего блока
     */
    public function getParams()
    {
        if (isset($this->arParams[$this->iCur])) {
            return $this->arParams[$this->iCur];
        }
        
        return array();
    }

    public function clearParams()
    {
        $this->arParams[$this->iCur] = array();
        return $this;
    }

}
