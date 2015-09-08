<?php
namespace IslandFuture\Sfw;

/**
 * Класс отвечающий за ссесию текущего юзера и за связь с данными юзера в БД
 *
 * @link    https://github.com/islandfuture/SFW
 * @author  Michael Akimov <michael@island-future.ru>
 * @version GIT: $Id$
 *
 * @example в рамках выполнения скрипта сессия всегда одна.
 *      \IslandFuture\Sfw\ActiveUser::one()->iRoleId = 1
 **/

class ActiveUser extends \IslandFuture\Sfw\Only
{
    // @var string в свойстве хранится название класса, которое отвечает за хранение данных юзера в БД
    public static $sUserClassName = 'Users';
    
    // @var \IslandFuture\Sfw\Data\Model клас отвечающий за данные о пользователе
    protected $oCurrentUser = null;

    // @var boolean равен true, если загружали данные из БД в сессию
    protected $isSynchronized = false;
    
    protected $hasError = false;
    
    public function hasError()
    {
        return $this->hasError;
    }

    public function __get($sName)
    {
        if (empty($_SESSION['SFW_USER'][$sName])) {
            $_SESSION['SFW_USER'][$sName] = '';
        }

        return $_SESSION['SFW_USER'][$sName];
    }
    
    public function __set($sName, $sVal)
    {
        $_SESSION['SFW_USER'][$sName] = $sVal;
    }

    protected function afterConstruct($arParams)
    {
        if ($arParams && ! empty($arParams['sModel'])) {
            static::$sUserClassName = $arParams['sModel'];
        }

        $this->hasError = (! session_start());

        if (empty($_SESSION['SFW_USER'])) {
            $_SESSION['SFW_USER'] = array();
        }
        
        if (static::$sUserClassName != 'none') {
            $this->oCurrentUser = \IslandFuture\Sfw\Data\Storages::model(static::$sUserClassName);
            $this->oCurrentUser->attributes($_SESSION['SFW_USER']);
        } else {
            $this->oCurrentUser = new \stdClass();
            $this->oCurrentUser = $_SESSION['SFW_USER'];
        }
        return true;
    }
    
    /**
     * Функция возвращает объект с данными текущего юзера, при необходимости берет из БД
     */
    public function getModel($sKey = 'id', $isNeedSynchro = true)
    {
        if (! $this->isSynchronized && $isNeedSynchro && ($this->__get($sKey) > '')) {
            $this->oCurrentUser = \IslandFuture\Sfw\Data\Storages::getOne(
                array(
                    'sModel' => static::$sUserClassName,
                    'arFilter' => array(
                        $sKey => array('=' => $this->__get($sKey) )
                    )
                )
            );

            if ($this->oCurrentUser) {
                $arFields = $this->oCurrentUser->__getFields();
                foreach ($arFields as $sKey => $sVal) {
                    $_SESSION['SFW_USER'][$sKey] = $sVal;
                }
                $this->isSynchronized = true;
            }
        }

        return $this->oCurrentUser;
    }

    public function getName()
    {
        if (static::$sUserClassName != 'none') {
            return $this->oCurrentUser->getName();
        } else {
            return 'none';
        }
    }

    public function login($arParams = array())
    {
        foreach ($arParams as $key => $val) {
            $this->__set($key, $val);
        }
        
        if (static::$sUserClassName != 'none') {
            $this->oCurrentUser->attributes($_SESSION['SFW_USER']);
        } else {
            $this->oCurrentUser = $_SESSION['SFW_USER'];
        }
    }

    public function logout()
    {
        $this->id = null;
        if (static::$sUserClassName != 'none') {
            $this->oCurrentUser = \IslandFuture\Sfw\Data\Storages::model(static::$sUserClassName);
        } else {
            $this->oCurrentUser = new \stdClass();
        }
        $_SESSION['SFW_USER'] = array();
    }
    
    /**
     * Проверяем доступ на просмотр страницы $sPage
     * @return boolean true - если доступ есть
     * @throws \IslandFuture\Sfw\Exceptions\Http403
     */
    public function validateAccess($sPage, &$arAccess)
    {
        /* arAccess defined in \IslandFuture\Sfw\Application::init() method */
        if (! $arAccess || sizeof($arAccess) == 0) {
            return true;
        }
        if (substr($sPage,0,1) != '/') {
            $sPage = '/'.$sPage;
        }

        /* если в классе юзера есть класс проверки доступа, то передаем управление туда */
        if (static::$sUserClassName != 'none' && method_exists($this->oCurrentUser,'validateAccess')) {
            return $this->oCurrentUser->validateAccess($sPage, $arAccess);
        }

        $iRoleId = $this->iRoleId;
        $iStop = 30;
        $isResult = false;
        do {
            if (! empty($arAccess[$sPage]))
            {
                foreach ($arAccess[$sPage] as $iGroup => $sGrant) {
                    if ($iGroup == 0 && $sGrant == 'allow') {
                        $isResult = true;
                        break 2;
                    }
                    
                    if (($iRoleId && $iGroup) == $iGroup) {
                        $isResult = ($sGrant == 'allow' ? true : false);
                        break 2;
                    }
                }
            }
            if( substr($sPage,-1) == '/') {
                $sPage = substr($sPage,0,-1);
            } else {
                $iPos = strrpos($sPage,'/');
                if ($iPos === false) {
                    $sPage = '';
                } else {
                    $sPage = substr($sPage,0,$iPos+1);
                }
                
            }
            $iStop--;
        } while($sPage > '' && $iStop > 0);

        if ($isResult === false) {
            throw new \IslandFuture\Sfw\Exceptions\Http403();
        }
        
        return $isResult;
    }

}
//end class ActiveUser
