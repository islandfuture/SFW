<?php
/**
 * Автогенерация класса для работы с данными Busers
 * Если хотите добавить или изменить свойства, то делать это нужно в
 * дочернем классе Busers
 */
class UsersAuto extends \IslandFuture\Sfw\Data\Model  {
    
    public $uidMin=1000000;
    public $uidMax=10000000;
    
    /**
     * функция возвращает название таблицы в которой хранятся сущности данного класса
     * @return string
     */
    public static function getTable() {
        return 'Users';
    }

    /**
     * функция возвращает название первичного ключа
     * @return string
     */
    public static function getIdName() {
        return 'id';
    }

    /**
     * функция возвращает способ генерации значения первичного ключа
     * @return string
     */
    public static function getIdDefault() {
        return 'UUID'; //'AUTOINC';
    }

    /**
     * функция возвращает название модели
     * @return string
     */
    public static function getTitle() {
        return 'Пользователи';
    }
    

    public static function getClearFields() {
        return array
        (
            'id'=> null,
            'sName'=> null,
            'sPasswd'=> null,
            'sEmail'=> null,
            'sDescription'=> null,
            'iSexId'=> null,
            'sActivation'=> null,
            'sCheckword'=> null,
            'tBirthdate'=> null,
            'tCreated'=> null,
            'tModified'=> null,
            'tLastActivity'=> null,
            'tLastLogin'=> null,
            'sLastIp'=> null,
            'iRoleId'=> null,
            'sPhone' => null,
            'iStatusId' => null,
        );
    }

    public static function getDefault() {
        return array(
            'iSexId'=> '0',
            'sCheckword'=> '',
            'tBirthdate'=> '0000-00-00 00:00:00',
            'iGeoId'=> 'NULL',
            'tCreated'=> 'now()',
            'tModified'=> '0000-00-00 00:00:00',
            'tLastActivity'=> '0000-00-00 00:00:00',
            'tLastLogin'=> '0000-00-00 00:00:00',
            'sLastIp'=> '0.0.0.0',
            'iRoleId'=> '1',
            'iStatusId' => '0'
        );
    }

    /**
     * функция возвращает массив отношений текущей сущности с другими типами сущностей
     */
    public function status($isOne=true, $idx=0)
    {
        $arRelations = array(
            '0' => array('не активирован'),
            '1' => array('активирован'),
            '2' => array('заблокирован'),
            '3' => array('удален')
        );
        return $isOne
            ? (
                isset($arRelations[$this->iStatusId])
                ? $arRelations[$this->iStatusId][$idx]
                : null
            )
            : $arRelations;
    }

    public function role($isOne=true, $idx=0)
    {
        $arRelations = array(
            '0' => array('гость'), 
            '1' => array('пользователь'),
            '2' => array('наставник'),
            '3' => array('админ')
        );
        return $isOne
            ? (
                isset($arRelations[$this->iStatusId])
                ? $arRelations[$this->iStatusId][$idx]
                : null
            )
            : $arRelations;
    }

    public function sex($isOne=true, $idx=0)
    {
        $arRelations = array(
            '0' => array('неизвестно'),
            '1' => array('мужской'),
            '2' => array('женский')
        );
        return $isOne
            ? (
                isset($arRelations[$this->iStatusId])
                ? $arRelations[$this->iStatusId][$idx]
                : null
            )
            : $arRelations;
    }


}
