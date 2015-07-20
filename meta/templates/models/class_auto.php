<?php
/**
 * Автогенерация класса для работы с данными <:classname:>
 * Если хотите добавить или изменить свойства, то делать это нужно в
 * дочернем классе <:classname:>
 */
class <:classname:>Auto extends \IslandFuture\Sfw\Data\Model
{
    <:primary_id_param:>
    /**
     * функция возвращает название таблицы в которой хранятся сущности данного класса
     * @return string
     */
    static public function getTable()
    {
        return '<:tablename:>';
    }

    /**
     * функция возвращает название модели
     * @return string
     */
    static public function getTitle()
    {
        return '<:titlename:>';
    }
    
    /**
     * функция возвращает способ генерации значения первичного ключа
     *
     * @example
     *      UUID - случайное число между $uidMin и $uidMax
     *      AUTOINC - автоинкрементный счетчик (в MySQL)
     *      GUID - случайное значение
     * @return  string
     */
    static public function getIdDefault()
    {
        return '<:id_default:>';
    }

    /**
     * функция возвращает название первичного ключа
     * @return string
     */
    static public function getIdName()
    {
        return '<:id_name:>';
    }

    /**
     * функция возвращает список полей модели
     * @return array
     */
    static public function getClearFields()
    {
        return array(
        <:clear_fields:>
        );
    }

    /**
     * функция возвращает значение по умолчанию для полей модели
     * @return array
     */
    static public function getDefault()
    {
        return array(
        <:defaults:>
        );
    }// end method

    /**
     * список функций отвечающих за свзяи с другими моделями
     */
    <:relations_fields:>
}
