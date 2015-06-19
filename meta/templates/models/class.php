<?php
include '<:classname:>Auto.php';

/**
 * здесь Вы можете добавлять свои функции и обработчики для модели <:classname:>
 */
 
class <:classname:> extends <:classname:>Auto
{
    
    /**
     * функция вызывается автоматически перед удалением и если возвращает true,
     * то удаление происходит, иначе - нет.
     * 
     * @return boolean
    protected function beforeDelete ($mParams=array())
    {
        return true;
    }
    */
    
    /**
     * функция вызывается после удалениея
     * 
    protected function afterDelete( $mParams=array() )
    {
        return true;
    }
    */

    /**
     * функция вызывается автоматически перед сохранением данных и если возвращает true,
     * то сохранение происходит, иначе - нет.
     * Рекомендуется использовать для проверки данных или формирования парамеров по умолчанию
     * 
     * @return boolean
    protected function beforeSave( $mParams=array() )
    {
        return true;
    }
    */

    /**
     * Функция вызывается после сохранения данных
     *
    protected function afterSave( $mParams=array() )
    {
        return true;
    }
    */
}
