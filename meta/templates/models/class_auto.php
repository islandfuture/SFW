<?php
/**
 * Автогенерация класса для работы с данными <:classname:>
 * Если хотите добавить или изменить свойства, то делать это нужно в
 * дочернем классе <:classname:>
 */
class <:classname:>Auto extends \IslandFuture\Sfw\Data\Model
{
    
    /**
     * функция возвращает название таблицы в которой хранятся сущности данного класса
     * @return string
     */
    public function getTable()
    {
        return '<:tablename:>';
    }

    /**
     * функция возвращает название модели
     * @return string
     */
    public function getTitle()
    {
        return '<:titlename:>';
    }
    
    public function getIdDefault()
    {
        return '<:id_default:>';
    }

    public function getClearFields()
    {
        return array(
<:clear_fields:>
        );
    }

    public function getDefault()
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
