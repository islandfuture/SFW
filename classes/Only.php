<?php
namespace IslandFuture\Sfw;

/**
 * @version rev: $Id:$
 * @author Michael Akimov <michael@island-future.ru>
 * 
 * @description  класс "Одиночный/Singleton", необходим для создания только одного экземпляра
 * класса. Все синглтоны должны наследоваться от этого класса.
 *
 * @example
 *        IslandFuture\Sfw\Only::one() //вернет один экземпляр себя
 *
 * @example
 *         class Application extends One { ... }
 *         IslandFuture\Sfw\Application::one(); // вернет единственный экземпляр Application
 */

class Only {
    /**
     * @var Array массив для хранения уникальных экземпляров
     */
    private static $_arInstances=array();
    
    private function __construct($arParams = null){
        $classname = get_called_class();

        if( method_exists($classname, 'afterConstruct') ) {
            $this->afterConstruct($arParams);
        }
    } // блокируем доступ к функции
    private function __clone(){} // блокируем доступ к функции
    private function __wakeup(){} // блокируем доступ к функции
            
    public static function one() {
        $classname = get_called_class();
        strtolower($classname);
        
        if ( empty(self::$_arInstances[$classname]) ) {           
            self::$_arInstances[$classname] = new $classname();
        }

        return self::$_arInstances[$classname];
    }    


} /* end class Only */


    /**
     * @deprecated
     * 
     * Волшебная функция позволяющая вызывать любой класс только один раз
     * @return object
     * @example Only::one()->className($param)
     */
/* 
    public function __call($name, $params = null ) {
        $lname = strtolower($name);

        if( isset(self::$_arOBJ[$lname]) ) {
            return  self::$_arOBJ[$lname];
        } else {
            if( class_exists($name,true) ) {
                if( $params ){
                    self::$_arOBJ[$lname] = new $name ($params);
                } else {
                    self::$_arOBJ[$lname] = new $name();
                }
                
                if( method_exists(self::$_arOBJ[$lname],"afterConstruct") ){
                    self::$_arOBJ[$lname]->afterConstruct();
                }
                return self::$_arOBJ[$lname];
            } else {
                throw new Exception('Не определен класс: ['.$name.']. Попробуйте подключить файл в котором определен данный класс.');
            }
        }
    }
*/
