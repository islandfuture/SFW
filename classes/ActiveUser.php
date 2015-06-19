<?php
namespace IslandFuture\Sfw;

class ActiveUser extends Only
{
    // @var string в свойстве хранится название класса, которое отвечает за хранение данных юзера в БД
    public $sUserClassName = 'Users';
    
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
            $this->sUserClassName = $arParams['sModel'];
        }

        $this->hasError = (! session_start());

        if (empty($_SESSION['SFW_USER'])) {
            $_SESSION['SFW_USER'] = array();
        }
        $this->oCurrentUser = \IslandFuture\Sfw\Data\Storages::model($this->sUserClassName);
        $this->oCurrentUser->attributes($_SESSION['SFW_USER']);
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
                    'sModel' => $this->sUserClassName,
                    'arFilter' => array(
                        $sKey => array('=' => $this->__get($sKey) )
                    )
                )
            );

            $arFields = $this->oCurrentUser->__getFields();
            foreach ($arFields as $sKey => $sVal) {
                $_SESSION['SFW_USER'][$sKey] = $sVal;
            }
            $this->isSynchronized = true;
        }

        return $this->oCurrentUser;
    }

    public function getName()
    {
        return $this->sName > '' ? $this->sName : 'Мистер Х';
    }

    public function login($arParams = array())
    {
        foreach ($arParams as $key => $val) {
            $this->__set($key, $val);
        }
    }

    public function logout()
    {
        $this->id = null;
        $this->oCurrentUser = \IslandFuture\Sfw\Data\Storages::model($this->sUserClassName);
        $_SESSION['SFW_USER'] = array();
    }
}
//end class ActiveUser
