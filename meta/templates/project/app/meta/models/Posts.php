<?php
return array(
    'sTable' => 'Posts',
    'sClassname' => 'Posts',
    'sDatabase' => '',
    'sCodepage' => 'utf8',
    'sTitle' => 'Заметки',
    'sComment' => 'Используется для работы с заметками пользователей',
    'arFields' => array(
        'id' => array(
            'sTitle' => 'Код',
            'sType' => 'bigint',
            'sPrimary' => 'yes',
            'sDefault' => 'AUTOINC',
            'sComment' => 'код',
            'isNull' => 'no'
        ),
        'sPreviewId' => array(
            'sTitle' => 'Картинка',
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '64',
            'sCodepage' => 'ascii',
            'sBinary' => 'bin',
            'sDefault' => '0',
            'sComment' => 'код картинки превью',
            'isNull' => 'no'
        ),
        'sTitle' => array(
            'sTitle' => 'Заголовок',
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '512',
            'sCodepage' => 'utf8',
            'sBinary' => 'no',
            'sDefault' => '',
            'sComment' => 'название поста',
            'isNull' => 'no'
        ),
        'sDescription' => array(
            'sTitle' => 'Описание',
            'sType' => 'text',
            'sPrimary' => 'no',
            'sCodepage' => 'utf8',
            'sBinary' => 'no',
            'sDefault' => '',
            'sComment' => 'описание заметки',
            'isNull' => 'no'
        ),
        'sTags' => array(
            'sTitle' => 'Метки',
            'sType' => 'varchar',
            'sPrimary' => 'no',
            'iLength' => '512',
            'sCodepage' => 'utf8',
            'sBinary' => 'no',
            'sDefault' => '',
            'sComment' => 'ключевые слова поста',
            'isNull' => 'no'
        ),
        'sCreatorId' => array(
            'sTitle' => 'Создатель',
            'sType' => 'char',
            'sPrimary' => 'no',
            'iLength' => '36',
            'sCodepage' => 'ascii',
            'sBinary' => 'bin',
            'sDefault' => '',
            'sComment' => 'создатель поста',
            'isNull' => 'no'
        ),
        'iLikeCnt' => array(
            'sTitle' => 'Понравилось',
            'sType' => 'int',
            'sPrimary' => 'no',
            'sDefault' => '0',
            'sComment' => 'скольким понравилось',
            'isNull' => 'no'
        ),
        'iViewCnt' => array(
            'sTitle' => 'Просмотры',
            'sType' => 'int',
            'sPrimary' => 'no',
            'sDefault' => '0',
            'sComment' => 'сколько посмотрело',
            'isNull' => 'no'
        ),
        'iCommentCnt' => array(
            'sTitle' => 'Комментарии',
            'sType' => 'int',
            'sPrimary' => 'no',
            'sDefault' => '0',
            'sComment' => 'количество комментов',
            'isNull' => 'no'
        ),
        'iCommentAccessId' => array(
            'sTitle' => 'Право комментирования',
            'sType' => 'int',
            'sPrimary' => 'no',
            'sDefault' => '1',
            'sComment' => 'кто может комментировать (0 - без комментариев, 1 - юзеры, 2 - учителя)',
            'isNull' => 'no'
        ),
        'iCommentLastId' => array(
            'sTitle' => 'Последний комментарий',
            'sType' => 'bigint',
            'sPrimary' => 'no',
            'sDefault' => '0',
            'sComment' => 'код последнего комментария',
            'isNull' => 'no'
        ),
        'tCommentLastDate' => array(
            'sTitle' => 'Откомментировано',
            'sType' => 'timestamp',
            'sPrimary' => 'no',
            'sDefault' => '0000-00-00 00:00:00',
            'sComment' => 'дата последнего комментария',
            'isNull' => 'no'
        ),
        'iStatusId' => array(
            'sTitle' => 'Состояние',
            'sType' => 'int',
            'sPrimary' => 'no',
            'sDefault' => '1',
            'sComment' => 'состояние: 0-неопубликовано, 1-опубликовано,2-модерация,3-удалено',
            'isNull' => 'no'
        ),
        'tPublished' => array(
            'sTitle' => 'Опубликовано',
            'sType' => 'timestamp',
            'sPrimary' => 'no',
            'sDefault' => '0000-00-00 00:00:00',
            'sComment' => 'время публикации',
            'isNull' => 'no'
        ),
        'tCreated' => array(
            'sTitle' => 'Создано',
            'sType' => 'timestamp',
            'sPrimary' => 'no',
            'sDefault' => 'CURRENT_TIMESTAMP',
            'sComment' => 'время создания записи',
            'isNull' => 'no'
        ),
        'tModified' => array(
            'sTitle' => 'Изменено',
            'sType' => 'timestamp',
            'sPrimary' => 'no',
            'sDefault' => '0000-00-00 00:00:00',
            'sComment' => 'время изменения',
            'isNull' => 'no'
        ),

    ),
    'arRelations' => array(
        'creator' => array('ONE', 'sCreatorId', 'Users', 'id'),
        'preview' => array('ONE', 'sPreviewId', 'Attachments', 'id'),
        'status' => array('VIRTUAL', 'iStatusId', array(
                '0' => array('не активирован'),
                '1' => array('активирован'),
                '2' => array('заблокирован'),
                '3' => array('удален')
            )
        ),
        
    )
);
