<?php
namespace IslandFuture\Sfw;

/**
 * Класс "Приложение", отвечающий за инициализацию, определения страницы отображения,
 * первичной обработки входных данных, контролем текущих процессов и отображением выходных данных
 *
 * @link    https://github.com/islandfuture/SFW
 * @author  Michael Akimov <michael@island-future.ru>
 * @version GIT: $Id$
 *
 * @example Application::one()->init()->run();
 */
class Application extends \IslandFuture\Sfw\Only
{
    //@var Array массив параметров конфигурации
    private $arConfig;
    
    //@var string название текущей страницы (точнее запрашиваемой)
    public $sCurPage = 'portal/index';

    //@var string name of templates directory
    public $sLayout = 'main';
    
    //@var string основное содержание страницы
    public $sPageContent = '';
    
    //@var array массив для обмена переменными между блоками
    public $arBlockVars;

    protected $modules = array();

    public function __get($name)
    {
        if (empty($this->arConfig[$name])) {
            return null;
        }
        
        return $this->arConfig[$name];
    }
    
    public function __set($name, $val)
    {
        $this->arConfig[$name] = $val;
        return $this;
    }


    /**
     * Инициализируем все первоначальные данные
     */
    public function init()
    {
        if (empty($_SERVER)) {
            $_SERVER = array();
        }

        /* DOCUMENT_ROOT must set to public directory, and all code set up level */
        if (empty($_SERVER['DOCUMENT_ROOT'])) {
            $_SERVER['DOCUMENT_ROOT'] = dirname(realpath(__DIR__ .DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..')).DIRECTORY_SEPARATOR.'public';
        } else {
            $_SERVER['DOCUMENT_ROOT'] = realpath($_SERVER['DOCUMENT_ROOT']);
        }
        
        $sPublicPath = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR;
        $sAppPath = dirname($_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR;
        $sVendorPath = dirname($_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR;
        $sConfig = dirname($_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';
        $sAccess = dirname($_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'access.php';
        $sSfwPath = dirname(__DIR__).DIRECTORY_SEPARATOR;

        if (! file_exists($sConfig)) {
            throw new \Exception('Cannot load config file');
        }

        ob_start();
        $this->arConfig = (include_once $sConfig);
        $this->arAccess = file_exists($sAccess) ? (include_once $sAccess) : array() ;
        ob_end_clean();

        if (empty($this->arConfig['debug']) || $this->arConfig['debug'] != 'Y') {
            $this->arConfig['debug'] = 'N';
        }

        if (isset($this->arConfig['include'])) {
            // регистрируем автозагрузчик классов
            spl_autoload_register(array($this, 'appAutoload' ), false);
        }

        $this->arConfig['PATH_ROOT'] = dirname($_SERVER['DOCUMENT_ROOT']).DIRECTORY_SEPARATOR;
        $this->arConfig['PATH_APP'] = $sAppPath;
        $this->arConfig['PATH_PAGES'] = $sAppPath.'pages'.DIRECTORY_SEPARATOR;
        $this->arConfig['PATH_PUBLIC'] = $sPublicPath;
        $this->arConfig['PATH_VENDOR'] = $sVendorPath;
        $this->arConfig['PATH_SFW'] = $sSfwPath;
 
        $this->arBlockVars['lasterror'] = '';
        $this->arBlockVars['lastmessage'] = '';
        $this->arBlockVars['js_bottom'] = array();

        set_error_handler(array($this,'appError'), E_ALL | E_STRICT);
        ignore_user_abort(true);

        if ($this->debug == 'Y') {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
        }

        return $this;
    }

    /**
     * метод используется для обработки веб-запросов
     */
    public function run()
    {
        try {
            if (empty( $_REQUEST['page'] ) && ! empty($_SERVER['REQUEST_URI'])) {
                $arTmp = explode('?', $_SERVER['REQUEST_URI']);
                $_REQUEST['page'] = $arTmp[0];
            }
    
            if (! empty($_REQUEST['page'])) {
                $this->sCurPage = $_REQUEST['page'];
                if (substr($this->sCurPage, 0, 1) == '/') {
                    $this->sCurPage = substr($this->sCurPage, 1);
                }

                if (substr($this->sCurPage, -1, 1) == '/') {
                    $this->sCurPage = substr($this->sCurPage, 0, -1);
                }
            }//end if

            ActiveUser::$sUserClassName = $this->user > '' ? $this->user : 'none';
            $session = ActiveUser::one();
            if ($session->hasError()) {
                throw new \Exception('Cannot start user session');
            }

            $sPath = $this->PATH_PAGES;
            $this->route();
            $arAccess = $this->arAccess;
            $isAccess = $session->validateAccess($this->sCurPage, $arAccess);

            ob_start();
            include $sPath.$this->sCurPage.'.php';
            $this->sPageContent = ob_get_contents();
            ob_end_clean();

            if (file_exists($this->PATH_APP.'layout'.DIRECTORY_SEPARATOR.$this->sLayout.'.php')) {
                include $this->PATH_APP.'layout'.DIRECTORY_SEPARATOR.$this->sLayout.'.php';
            } else {
                echo $this->sPageContent;
            }
        } catch (\PDOException $e) {
            if ($this->debug == 'Y') {
                header('Content-type: text/html; charset=utf-8');
                if (isset($e->xdebug_message)) {
                    echo '<table>'.$e->xdebug_message.'</table>';
                } else {
                    echo "DB Exception: [".$e->getMessage()."] in file [".$e->getFile()."] in line ".$e->getLine();
                    var_dump($e->getTrace());
                }
            } else {
                //@todo добавить логирование ошибок и отправку писем
                header('Content-type: text/html; charset=utf-8');
                echo "Произошла ошибка. Разработчики уже уведомлены и работают над проблемой";
            }
        } catch (\IslandFuture\Sfw\Exceptions\Http404 $e) {
            header('HTTP/1.0 404 Not found');
            if (file_exists($sPath . '404.php')) {
                include $sPath . '404.php';
            } else {
                die('Page not found. ' . $this->sCurPage);
            }
        } catch (\IslandFuture\Sfw\Exceptions\Http403 $e) {
            header('HTTP/1.0 403 Forbidden');
            die('Access denied '.$this->sCurPage);
        } catch (\Exception $e) {
            if ($this->debug == 'Y') {
                header('Content-type: text/html; charset=utf-8');

                if (isset($e->xdebug_message)) {
                    echo '<table>'.$e->xdebug_message.'</table>';
                    var_dump($e->getTrace());
                } else {
                    echo "Exception: [".$e->getMessage()."] in file [".$e->getFile()."] in line ".$e->getLine();
                    var_dump($e->getTrace());
                }
            } else {
                //@todo добавить логирование ошибок и отправку писем
                header('Content-type: text/html; charset=utf-8');
                echo "Произошла ошибка. Разработчики уже уведомлены и работают над проблемой";
            }
        }
    }//end function run

    /**
     * метод используется для обработки консольных запросов
     */
    public function console()
    {
        try {
            global $argv;
            if (empty($argv[1])) {
                echo "Select command to need run in console\n";
                return false;
            }
            $sCommand = $argv[1];
            parse_str(implode('&', array_slice($argv, 2)), $_GET);
            
            $sName = $this->PATH_APP.'meta'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.$sCommand.'.php';
    
            if (! file_exists($sName)) {
                $sName = $this->PATH_SFW.'bin'.DIRECTORY_SEPARATOR.$sCommand.'.php';
                if (! file_exists($sName)) {
                    echo "<pre>";
                    throw new \Exception('Cannot find script: '."\r\n".$sName."\r\n".$this->PATH_APP.'meta'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.$sCommand.'.php');
                }
            }

            include $sName;
        } catch (\PDOException $e) {
            if ($this->debug == 'Y') {
                if (isset($e->xdebug_message)) {
                    echo ''.$e->xdebug_message.'';
                } else {
                    echo "DB Exception: [".$e->getMessage()."] in file [".$e->getFile()."] in line ".$e->getLine()."\r\n";
                    var_dump($e->getTrace());
                }
            } else {
                //@todo добавить логирование ошибок и отправку писем
                echo "Произошла ошибка. Разработчики уже уведомлены и работают над проблемой";
            }
        } catch (\Exception $e) {
            if ($this->debug == 'Y') {
                if (isset($e->xdebug_message)) {
                    echo ''.$e->xdebug_message.'';
                } else {
                    echo "Exception: [".$e->getMessage()."] in file [".$e->getFile()."] in line ".$e->getLine()."\r\n";
                    var_dump($e->getTrace());
                    echo "============\r\n";
                }
            } else {
                //@todo добавить логирование ошибок и отправку писем
                echo "Произошла ошибка. Разработчики уже уведомлены и работают над проблемой\r\n";
            }
        }

    }//end function console

    // функция для автозагрузки классов
    public function appAutoload($sClassName)
    {
        
        if (array_key_exists($sClassName, $this->arConfig['include'])) {
            if (substr($this->arConfig['include'][ $sClassName ], 0, 1) == '/') {
                $sPath = $this->arConfig['include'][ $sClassName ];
            } else {
                $sPath = $this->PATH_APP.DIRECTORY_SEPARATOR.$this->arConfig['include'][ $sClassName ];
            }
            
            if (file_exists($sPath)) {
                include_once $sPath;
                return true;
            } else {
                throw new \Exception('Class ['.$sClassName.'] not found in path ['.$sPath.']');
            }
        } else {
            throw new \Exception('Class ['.$sClassName.'] not exists in include settings');
        }
    }

    /**
     * @return bool
     */
    public function appError($errno, $errstr, $errfile = __FILE__, $errline = __LINE__, $errcontext  =array())
    {
        throw new \Exception($errstr." [file: $errfile in line: $errline]", $errno);
    }
    
    /**
     * метод нужен для избежания не правильной инициализции класса в качестве синглтона.
     */
    protected function afterConstruct()
    {
        if ($this->additionalConfigs > '' && is_array($this->additionalConfigs)) {
            foreach ($this->additionalConfigs as $fname) {
                if (file_exists($this->PATH_ROOT.'configs'.DIRECTORY_SEPARATOR.$fname)) {
                    $this->arConfig = include $this->PATH_ROOT.'configs'.DIRECTORY_SEPARATOR.$fname;
                     //= array_merge_recursive($this->arConfig, $arTmp);
                }
            }
        }
    }
    
    /**
     * Превращает текущий веб-путь в дисковый путь
     *
     * @return string
     */
    public function getCurDir()
    {
        $str = str_replace('/', DIRECTORY_SEPARATOR, $this->sCurPage);
        return realpath(dirname($this->PATH_APP.'pages'.DIRECTORY_SEPARATOR.$str)).DIRECTORY_SEPARATOR;
    }
    
    /**
     * Метод отвечает за запуск блоков, их отображение, а также за их кеширование.
     * @param string $name      название блока (ищет файл blocks/$name/block.php )
     * @param array  $params    параметры инициализации блока.
     * @param array  $sysparams параметры для работы блока  (например буферизировать вывод или нет, кешировать или нет) \
     *     (например буферизировать вывод или нет, кешировать или нет) \
     * @return \IslandFuture\Sfw\Block
     */
    public function block($sBlockName, $params = array(), $sysparams = array())
    {
        return \IslandFuture\Sfw\Block::one()->run($sBlockName, $params, $sysparams);
    }
    

    public function redirect($sLocalUrl='')
    {
        if ($sLocalUrl > '') {
            $this->sCurPage = $sLocalUrl;
            return header('Location: '.$this->sCurPage);
        }

        if (! empty($_REQUEST['page'])) {
            $this->sCurPage = $_REQUEST['page'];
            if (substr($this->sCurPage, 0, 1) == '/') {
                $this->sCurPage = substr($this->sCurPage, 1);
            }

            if (substr($this->sCurPage, -1, 1) == '/') {
                $this->sCurPage = substr($this->sCurPage, 0, -1);
            }
            
        }//end if
        
        return header('Location: /index.php?page='.$this->sCurPage);
    }

    /**
     * Функция ведет учет клиентских JS скриптов, для дальнейшей вставки в конец документа
     */
    public function addClientJs($sName, $sScript, $sPosition='top')
    {
        if (empty($this->arBlockVars['js'])) {
            $this->arBlockVars['js'] = array();
        }
        if (empty($this->arBlockVars['js'][$sPosition]) || ! is_array($this->arBlockVars['js'][$sPosition])) {
            $this->arBlockVars['js'][$sPosition] = array();
        }

        $this->arBlockVars['js'][$sPosition][$sName] = $sScript;
    }

    public function getClientJs($sPosition='')
    {
        if ($sPosition == '') {
            $arTmp = array();
            foreach ($this->arBlockVars['js'] as $arScript) {
                $arTmp += $arScript;
            }
        } elseif (isset($this->arBlockVars['js'][$sPosition])) {
            $arTmp = $this->arBlockVars['js'][$sPosition];
        } else {
            $arTmp = array();
        }
        
        $sHtml = '';
        foreach ($arTmp as $sScript) {
            if(substr($sScript, 0, 4) == 'http' || substr($sScript, 0, 1) == '/') {
                $sHtml .= '<script type="text/javascript" src="'.$sScript.'"></script>'."\n";
            } else {
                $sHtml .= '<script type="text/javascript">'."\n".$sScript."\n".'</script>'."\n";
            }
        }

        return "\n<!-- BEGIN: all external script $sPosition -->\n".$sHtml."\n<!-- END: all external script $sPosition -->\n";
    }

    /**
     * Функция ведет учет клиентских JS скриптов, для дальнейшей вставки в конец документа
     */
    public function addClientCss($sName, $sScript, $sPosition='top')
    {
        if (empty($this->arBlockVars['css'])) {
            $this->arBlockVars['css'] = array();
        }
        if (empty($this->arBlockVars['css'][$sPosition]) || ! is_array($this->arBlockVars['css'][$sPosition])) {
            $this->arBlockVars['css'][$sPosition] = array();
        }

        $this->arBlockVars['css'][$sPosition][$sName] = $sScript;
    }

    public function getClientCss($sPosition = '')
    {
        if ($sPosition == '') {
            $arTmp = array();
            foreach ($this->arBlockVars['css'] as $arScript) {
                $arTmp += $arScript;
            }
        }
        elseif (isset($this->arBlockVars['css'][$sPosition])) {
            $arTmp = $this->arBlockVars['css'][$sPosition];
        }
        else {
            $arTmp = array();
        }
        
        $sHtml = '';
        foreach ($arTmp as $sScript) {
            if(substr($sScript, 0, 4) == 'http' || substr($sScript, 0, 1) == '/') {
                $sHtml .= '<link rel="stylesheet" href="'.$sScript.'" />' . "\n";
            } else {
                $sHtml .= '<style type="text/css">' . "\n" . $sScript . "\n" . '</style>' . "\n";
            }
        }
        
        return "\n<!-- BEGIN: all external css $sPosition -->\n" . $sHtml . "\n<!-- END: all external css $sPosition -->\n";
    }

    /**
     * метод используется для запуска тестовых сценариев
     * @deprecated
     *
     * @todo переделать на phpUnit
     */
    public function test($test_name, $params = array())
    {
        $name = $this->PATH_APP.'tests'.DIRECTORY_SEPARATOR.$test_name.'.php';

        if (! file_exists($name)) {
            $name = $this->PATH_APP.'tests'.DIRECTORY_SEPARATOR.$test_name.DIRECTORY_SEPARATOR.'index.php';
            
            if (! file_exists($name)) {
                echo "<pre>";
                throw new SFW_Exception('не могу найти тест: '.$this->PATH_APP.'tests'.DIRECTORY_SEPARATOR.$test_name.'.php');
            }
        }

        if (is_array($params)) {
            $prefix = str_replace(DIRECTORY_SEPARATOR, '_', $test_name);
            extract($params, EXTR_PREFIX_ALL, $prefix);
        }

        include $name;
    }//end function test

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setH1($title)
    {
        $this->h1 = $title;
    }

    public function getTitle()
    {
        if($this->title > '') {
            return $this->title;
        } elseif ($this->h1 > '') {
            return $this->h1;
        } else {
            return 'Страница: ' . $this->sCurPage;
        }
    }//end function

    public function getH1()
    {
        if($this->h1 > '') {
            return $this->h1;
        } elseif ($this->title > '') {
            return $this->title;
        } else {
            return '';
        }
    }//end function
    
    public function setProperty($name, $value)
    {
        $this->properies[$name] = $value;
    }//end function
    
    public function getProperty($name)
    {
        if (isset($this->properies[$name])) {
            return $this->properies[$name];
        } else {
            return '';
        }
    }

    /**
     * функция подключает внешний модуль, если он не был подключен до этого
     * @return boolean возвращает true если модуль подключен и false в противном случае
     */
    public function externalModule($module,$path)
    {
        if (! empty($this->modules[$module])) {
            return true;
        }
        
        if (file_exists($this->PATH_APP.'externals'.DIRECTORY_SEPARATOR.$path)) {
            $this->modules[$module] = $path;
            include_once $this->PATH_APP.'externals'.DIRECTORY_SEPARATOR.$path;
            return true;
        }

        if (file_exists($this->PATH_APP.'externals'.DIRECTORY_SEPARATOR.$path)) {
            $this->modules[$module] = $path;
            include_once $this->PATH_APP.'externals'.DIRECTORY_SEPARATOR.$path;
            return true;
        }
        
        return false;
    }//end function
    
    /**
     * метод входящий ЧПУ роутит на нужный скрипт. Конфиг роутинга лежит в /config/route.php
     * @return boolean
     */
    public function route()
    {
        $sPath = $this->PATH_PAGES;
        if (file_exists($sPath.$this->sCurPage.'.php')) {
            return true;
        }
        
        if (file_exists($sPath.$this->sCurPage.DIRECTORY_SEPARATOR.'index.php')) {
            $this->sCurPage = $this->sCurPage.'/index';
            return true;
        }
        $sFname = $this->route > '' ? $this->route : 'route.php';
        $sRouteFile = $this->PATH_ROOT.'config'.DIRECTORY_SEPARATOR.$sFname;

        if (! file_exists($sRouteFile)) {
            throw new \Exception('Cannot find route file ['.$sFname.']');
        }
        
        $arRoutes = include_once $sRouteFile;
        
        $arParts = explode('/', $this->sCurPage);
        $this->sCurPage = '';
        $arCurRoute = $arRoutes;
        foreach ($arParts as $iDepth => $sPart) {
            if (isset($arCurRoute[$sPart])) {
                $this->sCurPage .= '/'.$sPart;
                $arCurRoute = $arCurRoute[$sPart];
            } elseif (is_numeric($sPart) && isset($arCurRoute[':num:'])) {
                if (isset($arCurRoute[':num:']['+'])) {
                    $sKey = $arCurRoute[':num:']['+'];
                    if (isset($_GET[$sKey])) {
                        if (! is_array($_GET[$sKey])) {
                            $_GET[$sKey] = array($_GET[$sKey]);
                        }
                        $_GET[$sKey][] = $sPart;
                        $_REQUEST[$sKey] = $_GET[$sKey];
                    } else {
                        $_REQUEST[$sKey] = $_GET[$sKey] = $sPart;
                    }
                }
                
                if (isset($arCurRoute[':num:']['=>'])) {
                    $arCurRoute = $arCurRoute[':num:']['=>'];
                }
            } elseif (is_string($sPart) && isset($arCurRoute[':str:'])) {
                if (isset($arCurRoute[':str:']['+'])) {
                    $sKey = $arCurRoute[':str:']['+'];
                    if (isset($_GET[$sKey])) {
                        if (! is_array($_GET[$sKey])) {
                            $_GET[$sKey] = array($_GET[$sKey]);
                        }
                        $_GET[$sKey][] = $sPart;
                        $_REQUEST[$sKey] = $_GET[$sKey];
                    } else {
                        $_REQUEST[$sKey] = $_GET[$sKey] = $sPart;
                    }
                }
                
                if (isset($arCurRoute[':str:']['=>'])) {
                    $arCurRoute = $arCurRoute[':str:']['=>'];
                }
            } else {
                $this->sCurPage .= '/'.$sPart;
                $arCurRoute = array();
            }
        }
        $this->sCurPage .= '/'.(isset($arCurRoute[':end:']) ? $arCurRoute[':end:'] : 'index');
        if (file_exists($sPath.$this->sCurPage.'.php')) {
            return true;
        }

        $this->arBlockVars['lasterror'] = $this->sCurPage;
        throw new \IslandFuture\Sfw\Exceptions\Http404('Страница: "'.$this->sCurPage.DIRECTORY_SEPARATOR.'index.php" или "'.DIRECTORY_SEPARATOR.$this->sCurPage.'.php" не найдена');
    }

}
