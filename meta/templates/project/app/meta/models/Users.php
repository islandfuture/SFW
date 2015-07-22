<?php
return array(
    'sTable' => 'Users',
    'sClassname' => 'Users',
    'sDatabase' => '',
    'sCodepage' => 'utf8',
    'sTitle' => 'Пользователи',
    'sComment' => 'Хранятся данные пользователей',
    'arFields' => array(
        'id' => array(
            'sTitle' => 'Код',
            'sType' => 'char',
            'sPrimary' => 'yes',
            'iLength' => '36',
            'sCodepage' => 'ascii',
            'sBinary' => 'yes',
            'sDefault' => 'UUID',
            'sComment' => 'Код',
            'isNull' => 'no'
        ),
        'sName' => array(
            'sTitle' => 'Имя',
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '64',
            'sCodepage' => 'utf8',
            'sBinary' => 'no',
            'sDefault' => '',
            'sComment' => '',
            'isNull' => 'no'
        ),
        'sPasswd' => array(
            'sTitle' => 'Пароль',
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '64',
            'sCodepage' => 'utf8',
            'sBinary' => 'no',
            'sDefault' => '',
            'sComment' => '',
            'isNull' => 'no'
        ),
        'sEmail' => array(
            'sTitle' => 'Почта',
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '256',
            'sCodepage' => 'utf8',
            'sBinary' => 'no',
            'sDefault' => '',
            'sComment' => '',
            'isNull' => 'no'
        ),
        'sDescription' => array(
            'sTitle' => 'Описание',
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '1024',
            'sCodepage' => 'utf8',
            'sBinary' => 'no',
            'sDefault' => '',
            'sComment' => 'Кратко о себе',
            'isNull' => 'no'
        ),
        'iSexId' => array(
            'sTitle' => 'Пол',
            'sType' => 'int',
            'sPrimary' => 'no',
            'sDefault' => '0',
            'sComment' => '0 - неизвестно, 1 - мужчина, 2 - женщина',
            'isNull' => 'no'
        ),
        'sActivation' => array(
            'sTitle' => 'Код активации',
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '36',
            'sCodepage' => 'ascii',
            'sBinary' => 'bin',
            'sDefault' => '',
            'sComment' => '',
            'isNull' => 'no'
        ),
        'sCheckword' => array(
            'sTitle' => 'Проверочный код',
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '36',
            'sCodepage' => 'ascii',
            'sBinary' => 'bin',
            'sDefault' => '',
            'sComment' => 'код для смены пароля/емайла',
            'isNull' => 'no'
        ),
        'tBirthDate' => array(
            'sTitle' => 'Дата рождения',
            'sType' => 'timestamp',
            'sPrimary' => 'no',
            'sDefault' => '0000-00-00 00:00:00',
            'sComment' => 'день рождения',
            'isNull' => 'no'
        ),
        'sPhone' => array(
            'sTitle' => 'Телефон',
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '11',
            'sCodepage' => 'utf8',
            'sBinary' => 'no',
            'sDefault' => '',
            'sComment' => 'номер телефона',
            'isNull' => 'no'
        ),
        'tCreated' => array(
            'sTitle' => 'Зарегистрировался',
            'sType' => 'timestamp',
            'sPrimary' => 'no',
            'sDefault' => 'CURRENT_TIMESTAMP',
            'sComment' => 'время создания записи',
            'isNull' => 'no'
        ),
        'tModified' => array(
            'sTitle' => 'Обновлено',
            'sType' => 'timestamp',
            'sPrimary' => 'no',
            'sDefault' => '0000-00-00 00:00:00',
            'sComment' => 'время обновления записи',
            'isNull' => 'no'
        ),
        'tLastActivity' => array(
            'sTitle' => 'Последняя активность',
            'sType' => 'timestamp',
            'sPrimary' => 'no',
            'sDefault' => '0000-00-00 00:00:00',
            'sComment' => '',
            'isNull' => 'no'
        ),
        'tLastLogin' => array(
            'sTitle' => 'Последний логин',
            'sType' => 'timestamp',
            'sPrimary' => 'no',
            'sDefault' => '0000-00-00 00:00:00',
            'sComment' => '',
            'isNull' => 'no'
        ),
        'sLastIp' => array(
            'sTitle' => 'IP',
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '15',
            'sCodepage' => 'ascii',
            'sBinary' => 'bin',
            'sDefault' => '0.0.0.0',
            'sComment' => 'адрес последнего захода юзера',
            'isNull' => 'no'
        ),
        'iRoleId' => array(
            'sTitle' => 'Роль',
            'sType' => 'int',
            'sPrimary' => 'no',
            'sDefault' => '1',
            'sComment' => '0-guest, 1-user, 2-moderator, 3-admin',
            'isNull' => 'no'
        ),
        'iStatusId' => array(
            'sTitle' => 'Состояние',
            'sType' => 'int',
            'sPrimary' => 'no',
            'sDefault' => '0',
            'sComment' => '0 - не активирован, 1 - активирован, 2 - блокирован, 3 - удален',
            'isNull' => 'no'
        ),

    ),

    'arRelations' => array(
        'posts' => array('MULTI','id','Posts','sCreatorId'),
        'files' => array('MULTI','id','Attachments','sCreatorId'),

        'role' => array('VIRTUAL','iRoleId', array(
                '0' => array('гость'), 
                '1' => array('пользователь'),
                '2' => array('модератор'),
                '3' => array('админ')
            )
        ),
        'status' => array('VIRTUAL', 'iStatusId', array(
                '0' => array('не активирован'),
                '1' => array('активирован'),
                '2' => array('заблокирован'),
                '3' => array('удален')
            )
        ),
        'sex' => array('VIRTUAL', 'iSexId', array(
                '0' => array('неизвестно'),
                '1' => array('мужской'),
                '2' => array('женский')
            )
        )
    )
);
