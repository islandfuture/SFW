<?php
return array(
    'sTable' => 'Attachments',
    'sClassname' => 'Attachments',
    'sDatabase' => 'gus',
    'sCodepage' => 'utf8',
    'sTitle' => 'Файлы',
    'sComment' => 'Реестр заруженных файлов',
    'arFields' => array(
        'id' => array(
            'sTitle' => 'Код',
            'sType' => 'varchar',
            'sPrimary' => 'yes',
            'iLength' => '64',
            'sCodepage' => 'utf8',
            'sBinary' => 'no',
            'sDefault' => '',
            'sComment' => '',
            'isNull' => 'no'
        ),
        'sOriginalName' => array(
            'sTitle' => 'Имя файла',
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '255',
            'sCodepage' => 'utf8',
            'sBinary' => 'no',
            'sDefault' => '',
            'sComment' => 'оригинальное имя файла',
            'isNull' => 'no'
        ),
        'sDir' => array(
            'sTitle' => 'Подкаталог',
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '255',
            'sCodepage' => 'utf8',
            'sBinary' => 'no',
            'sDefault' => '/assests/',
            'sComment' => 'путь где лежит файл',
            'isNull' => 'no'
        ),
        'sMimeType' => array(
            'sTitle' => 'Тип файла',
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '64',
            'sCodepage' => 'utf8',
            'sBinary' => 'no',
            'sDefault' => 'image/jpeg',
            'sComment' => 'миме-тип',
            'isNull' => 'no'
        ),
        'iWidth' => array(
            'sTitle' => 'Ширина',
            'sType' => 'int',
            'sPrimary' => 'no',
            'sDefault' => '0',
            'sComment' => 'ширина в пикселях',
            'isNull' => 'no'
        ),
        'iHeight' => array(
            'sTitle' => 'Высота',
            'sType' => 'int',
            'sPrimary' => 'no',
            'sDefault' => '0',
            'sComment' => 'высота в пикселях',
            'isNull' => 'no'
        ),
        'iSize' => array(
            'sTitle' => 'Размер',
            'sType' => 'int',
            'sPrimary' => 'no',
            'sDefault' => '0',
            'sComment' => 'размер файла в байтах',
            'isNull' => 'no'
        ),
        'sModuleId' => array(
            'sType' => 'char',
            'sPrimary' => 'no',
            'iLength' => '64',
            'sCodepage' => 'utf8',
            'sBinary' => 'no',
            'sDefault' => 'core',
            'sComment' => 'название модуля, в рамках которого загружен файл (namespace)',
            'isNull' => 'no'
        ),
        'iStatusId' => array(
            'sTitle' => 'Состояние',
            'sType' => 'int',
            'sPrimary' => 'no',
            'sDefault' => '1',
            'sComment' => 'состояние: 1-новое, 2-одобрено,3-отклонено,4-спам,5-удалено',
            'isNull' => 'no'
        ),
        'sCreatorId' => array(
            'sTitle' => 'Создатель',
            'sType' => 'char',
            'sPrimary' => 'no',
            'iLength' => '64',
            'sCodepage' => 'utf8',
            'sBinary' => 'no',
            'sDefault' => '0',
            'sComment' => 'владедец',
            'isNull' => 'no'
        ),
        'tCreated' => array(
            'sTitle' => 'Создано',
            'sType' => 'timestamp',
            'sPrimary' => 'no',
            'sDefault' => 'CURRENT_TIMESTAMP',
            'sComment' => 'дата создания',
            'isNull' => 'no'
        ),

    ),
    'arRelations' => array(
        'creator' => array('ONE', 'sCreatorId', 'Users', 'id'),
        'status' => array('VIRTUAL', 'iStatusId', array(
                '0' => array('без статуса'),
                '1' => array('одобрено'),
                '2' => array('отклонено'),
                '3' => array('опубликовано'),
                '4' => array('спам'),
                '5' => array('удалено')
            )
        )
    )
);
