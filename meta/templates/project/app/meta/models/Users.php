<?php
return array(
    'sTable' => 'Users',
    'sClassname' => 'Users',
    'sDatabase' => 'gus',
    'arFields' => array(
        'id' => array(
            'sType' => 'char',
            'sPrimary' => 'yes',
            'iLength' => '36',
            'sCodepage' => 'ascii',
            'sBinnary' => 'yes',
            'sDefault' => '',
            'sComment' => 'Код',
            'isNull' => 'no'
        ),
        'sName' => array(
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '64',
            'sCodepage' => 'utf8',
            'sBinnary' => 'no',
            'sDefault' => '',
            'sComment' => '',
            'isNull' => 'no'
        ),
        'sPasswd' => array(
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '64',
            'sCodepage' => 'utf8',
            'sBinnary' => 'no',
            'sDefault' => '',
            'sComment' => '',
            'isNull' => 'no'
        ),
        'sEmail' => array(
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '256',
            'sCodepage' => 'utf8',
            'sBinnary' => 'no',
            'sDefault' => '',
            'sComment' => '',
            'isNull' => 'no'
        ),
        'sDescription' => array(
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '1024',
            'sCodepage' => 'utf8',
            'sBinnary' => 'no',
            'sDefault' => '',
            'sComment' => 'Кратко о себе',
            'isNull' => 'no'
        ),
        'iSexId' => array(
            'sType' => 'int',
            'sPrimary' => 'no',
            'sDefault' => '0',
            'sComment' => '0 - неизвестно, 1 - мужчина, 2 - женщина',
            'isNull' => 'no'
        ),
        'sActivation' => array(
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '36',
            'sCodepage' => 'ascii',
            'sBinnary' => 'bin',
            'sDefault' => '',
            'sComment' => '',
            'isNull' => 'no'
        ),
        'sCheckword' => array(
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '36',
            'sCodepage' => 'ascii',
            'sBinnary' => 'bin',
            'sDefault' => '',
            'sComment' => 'код для смены пароля/емайла',
            'isNull' => 'no'
        ),
        'tBirthDate' => array(
            'sType' => 'timestamp',
            'sPrimary' => 'no',
            'sDefault' => '0000-00-00 00:00:00',
            'sComment' => 'день рождения',
            'isNull' => 'no'
        ),
        'iAccountId' => array(
            'sType' => 'bigint',
            'sPrimary' => 'no',
            'sDefault' => '',
            'sComment' => 'номер счета',
            'isNull' => 'no'
        ),
        'iGeoId' => array(
            'sType' => 'bigint',
            'sPrimary' => 'no',
            'sDefault' => '',
            'sComment' => 'Город',
            'isNull' => 'no'
        ),
        'sTokenFb' => array(
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '128',
            'sCodepage' => 'ascii',
            'sBinnary' => 'bin',
            'sDefault' => '',
            'sComment' => '',
            'isNull' => 'no'
        ),
        'sTokenVk' => array(
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '128',
            'sCodepage' => 'ascii',
            'sBinnary' => 'bin',
            'sDefault' => '',
            'sComment' => '',
            'isNull' => 'no'
        ),
        'tCreated' => array(
            'sType' => 'timestamp',
            'sPrimary' => 'no',
            'sDefault' => 'CURRENT_TIMESTAMP',
            'sComment' => 'время создания записи',
            'isNull' => 'no'
        ),
        'tModified' => array(
            'sType' => 'timestamp',
            'sPrimary' => 'no',
            'sDefault' => '0000-00-00 00:00:00',
            'sComment' => 'время обновления записи',
            'isNull' => 'no'
        ),
        'tLastActivity' => array(
            'sType' => 'timestamp',
            'sPrimary' => 'no',
            'sDefault' => '0000-00-00 00:00:00',
            'sComment' => '',
            'isNull' => 'no'
        ),
        'tLastLogin' => array(
            'sType' => 'timestamp',
            'sPrimary' => 'no',
            'sDefault' => '0000-00-00 00:00:00',
            'sComment' => '',
            'isNull' => 'no'
        ),
        'sLastIp' => array(
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '15',
            'sCodepage' => 'ascii',
            'sBinnary' => 'bin',
            'sDefault' => '0.0.0.0',
            'sComment' => 'адрес последнего захода юзера',
            'isNull' => 'no'
        ),
        'iRoleId' => array(
            'sType' => 'int',
            'sPrimary' => 'no',
            'sDefault' => '1',
            'sComment' => '0-guest, 1-user, 2-teacher, 3-admin',
            'isNull' => 'no'
        ),
        'sPhone' => array(
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '11',
            'sCodepage' => 'utf8',
            'sBinnary' => 'no',
            'sDefault' => '',
            'sComment' => 'номер телефона',
            'isNull' => 'no'
        ),
        'sOrgName' => array(
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '256',
            'sCodepage' => 'utf8',
            'sBinnary' => 'no',
            'sDefault' => '',
            'sComment' => 'организация',
            'isNull' => 'no'
        ),
        'sPosition' => array(
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '256',
            'sCodepage' => 'utf8',
            'sBinnary' => 'no',
            'sDefault' => '',
            'sComment' => 'должность',
            'isNull' => 'no'
        ),
        'iStatusId' => array(
            'sType' => 'int',
            'sPrimary' => 'no',
            'sDefault' => '0',
            'sComment' => '0 - не активирован, 1 - активирован, 2 - блокирован, 3 - удален',
            'isNull' => 'no'
        ),

    ),

    'arRelations' => array(
        'categories' => array('MULTI','id','categories','owner_id'),
        'courses' => array('MULTI','id','courses','owner_id'),
        'threads' => array('MULTI','id','threads','owner_id'),
        'threadusers' => array('MULTI','id','threadusers','owner_id'),

    )
);
