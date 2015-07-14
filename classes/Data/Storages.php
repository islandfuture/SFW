<?php
namespace IslandFuture\Sfw\Data;
use \IslandFuture\Sfw\Application as Application;
use \PDO as PDO;

class Storages extends \IslandFuture\Sfw\Only {

    // кеш на время жизни скрипта (чтобы избегать повторного запуска)
    protected static $arCaches=array();
    protected static $arCacheTables = array();

    protected $bEnableCache = false;

    // @var Array of PDO statement
    private $arPools = array();
    private static $sCurKey = 'default';

    /**
     * ошибки, которые  произошли просто так или с каким-то объектом
     * массив состоит из моделей/полей или общей модели/блоков
     * Общая модель называется '_' (1-й уровень массива), у каждой модели есть счетчик ошибок ierr
     */
    protected static $arErrors = array();

    
    public static function initModel($sClassName)
    {
        if( !class_exists($sClassName,false) )
        {
            if( !file_exists(Application::one()->PATH_APP.'models'.DIRECTORY_SEPARATOR.$sClassName.'.php') )
            {
                throw new \Exception('Cannot find class ['.$sClassName.']');
            }
            include_once Application::one()->PATH_APP.'models'.DIRECTORY_SEPARATOR.$sClassName.'.php';
        }

        return true;
    }

    public static function model($sClassName)
    {
        if( static::initModel($sClassName) )
        {
            return new $sClassName;
        }
        else
        {
            throw new \Exception('Cannot create object ['.$sClassName.']');
        }

    }

    /**
     * Метод генерит блок WHERE для запроса
     * @param array $arParams
     * @return string
     */
    public static function generateWhereSQL( $arParams )
    {

        if( empty($arParams['sModel']) )
        {
            throw new \Exception('Class of model not defined');
        }
        
        $sClassName = $arParams['sModel'];
        $sTableName = $sClassName::getTable();
        $arFields    = $sClassName::getClearFields();

        $sWhere        = '1=1';
        $arRelations    = null;
        
        /*
         * Выставляем базу и таблицу для запроса
         */
        if( !empty( $arParams['sDatabase'] ) )
        {
            $table = '`' . $arParams['sDatabase'] . '`.`' . $sTableName . '`';
        }
        elseif( $sClassName::getDatabase() > '' )
        {
            $table = '`' . $sClassName::getDatabase() . '`.`' . $sTableName . '`';
        }
        else
        {
            $table = '`' . $sTableName . '`';
        }

        if( empty($arParams['arFilter']) )
        {
            $arParams['arFilter'] = array();
        }
        
        foreach( $arParams['arFilter'] as $key => $value )
        {
            if( key_exists( $key, $arFields ) )
            {
                if( is_array( $value ) )
                {

                    foreach( $value as $op => $val )
                    {
                        $op = strtolower( $op );

                        switch( $op )
                        {
                            case 'not like':
                            case 'like':
                            case '>=':
                            case '>':
                            case '<':
                            case '<=':
                            case '=':
                            case '!=':
                                $sWhere .= " AND $table.`" . $key . "` " . $op . " '" . addslashes( $val ) . "'";
                                break;
                            case '!in':
                                if( is_array( $val ) )
                                {
                                    foreach( $val AS &$value )
                                    {
                                        $value = addslashes( $value );
                                    }
                                    $val = "'" . implode( "','", $val ) . "'";
                                    $sWhere .= " AND $table.`" . $key . "` not in (" . $val . ")";
                                }
                                else
                                {
                                    $sWhere .= " AND $table.`" . $key . "` not in (" . addslashes( $val ) . ")";
                                }
                                break;
                            case 'not in':
                            case 'in':
                                /*
                                  if( !is_array($val) && strpos($val,',')>0 ){
                                  $val = explode(',',$val);
                                  }
                                 */
                                if( is_array( $val ) )
                                {
                                    foreach( $val AS &$value )
                                    {
                                        $value = addslashes( $value );
                                    }
                                    $val = "'" . implode( "','", $val ) . "'";
                                    $sWhere .= " AND $table.`" . $key . "` " . $op . " (" . $val . ")";
                                }
                                else
                                {
                                    $sWhere .= " AND $table.`" . $key . "` " . $op . " (" . addslashes( $val ) . ")";
                                }
                                break;
                            default:
                                if( $op == 0 )
                                {
                                    foreach( $value AS &$val )
                                    {
                                        $val = addslashes( $val );
                                    }
                                    $sWhere .= " AND $table.`" . $key . "` IN ('" . implode( "','", $value ) . "')";
                                    break 2;
                                }
                                else
                                {
                                    foreach( $val AS &$value )
                                    {
                                        $value = addslashes( $value );
                                    }
                                    $sWhere .= " AND $table.`" . $key . "` " . $op . " ('" . implode( "','", $val ) . "')";
                                }
                        }
                    }
                }
                else
                {
                    if( $value == '[:null:]' )
                    {
                        $sWhere .= " AND $table.`" . $key . "` is null";
                    }
                    elseif( $value == '[:!null:]' )
                    {
                        $sWhere .= " AND $table.`" . $key . "` is not null";
                    }
                    elseif( $value == '[:ignore:]' )
                    {
                        /* по данному полю сортировать нельзя */
                    }
                    else
                    {
                        $sWhere .= " AND $table.`" . $key . "`='" . addslashes( $value ) . "'";
                    }
                }
            }
            else
            {
                if( !$arRelations )
                {
                    $arRelations = $sClassName::getRelations();
                }

                if( isset( $arRelations[$key] ) )
                {
                    $rel         = $arRelations[$key];
                    $classname     = $rel[3];

                    if( $rel[0] == '::table::' )
                    {
                        static::initModel( $rel[3] );
                        
                        $sWhere .= ' AND '. $rel[2] .' IN ('.static::generateSelectSQL(array(
                                'sModel'=>$classname,
                                'sDatabase' => $classname::getDatabase(),
                                'fields' => $rel[4],
                                'arFilter'=>$value
                            )
                        ).')';
             
                    }
                    
                }
                elseif( $key == ':sql:' )
                {
                    $sWhere .= ' AND ' . $value;
                }
            }
        }
        return $sWhere;
    }

    public static function generateCountSQL( $arParams )
    {
        $from_add = '';
        if( empty( $arParams['arFilter'] ) )
        {
            $arParams['arFilter'] = array();
        }

        $sClassName = $arParams['sModel'];

        /*
         * Выставляем базу и таблицу для запроса
         */
        if( !empty( $arParams['sDatabase'] ) )
        {
            $table = '`' . $arParams['sDatabase'] . '`.`' . $sClassName::getTable() . '`';
        }
        elseif( $sClassName::getDatabase() > '' )
        {
            $table = '`' . $sClassName::getDatabase() . '`.`' . $sClassName::getTable() . '`';
        }
        else
        {
            $table = '`' . $sClassName::getTable() . '`';
        }

        $sWhere = static::generateWhereSQL( $arParams );

        if( !empty( $arParams['joins'] ) )
        {
            if( is_string( $arParams['joins'] ) )
            {
                $arParams['joins'] = array( $arParams['joins'] );
            }

            foreach( $arParams['joins'] as $i => $joinClass )
            {

                if( is_array( $joinClass ) )
                {
                    if( isset( $joinClass['typeJoin'] ) )
                    {
                        $from_add .= ' ' . $joinClass['typeJoin'] . ' ';
                    }
                    else
                    {
                        $from_add .= ' INNER JOIN ';
                    }

                    static::initModel( $joinClass[0] );
                    $joinClassname     = $joinClass[0];
                    $t                 = $joinClassname::getTable();

                    if( isset( $joinClass[1] ) )
                    {
                        $from_add .= '(SELECT * FROM `' . $t . '` WHERE ' . $joinClass[1] . ') as t' . $i . ' ';
                    }
                    else
                    {
                        $from_add .= ' ' . $t . ' as t' . $i . ' ';
                    }

                    $from_add .= ' ON ' . $joinClass['on'];
                }
                else
                {
                    static::initModel( $joinClass );
                    $t = $joinClass::getTable();

                    $from_add .= ', `' . $t . '` as t' . $i . ' ';
                }
            }
        }
        return 'SELECT COUNT(*) as cnt FROM ' . $table . ' ' . $from_add . ' WHERE ' . $sWhere;
    }

    /**
     * Метод генерит тело SELECT запроса
     * @param array $arParams
     * @return string
     */
    public static function generateSelectSQL( $arParams )
    {
        if( empty($arParams['sModel']) )
        {
            throw new \Exception('Class of model not defined');
        }
        
        $sClassName = $arParams['sModel'];
        $sTableName = $sClassName::getTable();
        $arFields     = $sClassName::getClearFields();

        /*
         * Выставляем базу и таблицу для запроса
         */
        if( !empty( $arParams['sDatabase'] ) )
        {
            $table = '`' . $arParams['sDatabase'] . '`.`' . $sTableName . '`';
        }
        elseif( $sClassName::getDatabase() > '' )
        {
            $table = '`' . $sClassName::getDatabase() . '`.`' . $sTableName . '`';
        }
        else
        {
            $table = '`' . $sTableName . '`';
        }

        $from_add = '';

        $limit = '';
        if( !empty( $arParams['nPageSize'] ) )
        {

            if( empty( $arParams['nPage'] ) )
            {
                $offset = 0;
            }
            else
            {
                $offset = ($arParams['nPage'] - 1) * $arParams['nPageSize'];
            }

            $limit = " LIMIT " . $offset . "," . $arParams['nPageSize'];
        }/* emd if */


        if( !isset( $arParams['arFilter'] ) )
        {
            $arParams['arFilter']     = array();
        }

        if( empty( $arParams['fields'] ) )
        {
            $arKeys     = array_keys( $arFields );
            $select     = $table . '.`' . implode( '`,' . $table . '.`', $arKeys ) . '`';
        }
        else
        {
            if( is_array( $arParams['fields'] ) )
            {
                $select = implode( ',', $arParams['fields'] );
            }
            else
            {
                $select = $arParams['fields'];
            }

            /* @todo добавить анализ полей и если есть групповые функции SUM, AVG, COUNT то остальные поля добавить в GROUP BY */
        }/* end if else */


        $sWhere = static::generateWhereSQL( $arParams );

        if( !empty( $arParams['joins'] ) )
        {
            if( is_string( $arParams['joins'] ) )
            {
                $arParams['joins'] = array( $arParams['joins'] );
            }

            foreach( $arParams['joins'] as $i => $joinClass )
            {

                if( is_array( $joinClass ) )
                {
                    if( isset( $joinClass['typeJoin'] ) )
                    {
                        $from_add .= ' ' . $joinClass['typeJoin'] . ' ';
                    }
                    else
                    {
                        $from_add .= ' INNER JOIN ';
                    }

                    static::initModel( $joinClass[0] );
                    $joinClassname     = $joinClass[0];
                    $t                 = $joinClassname::getTable();

                    if( isset( $joinClass[1] ) )
                    {
                        $from_add .= '(SELECT * FROM `' . $t . '` WHERE ' . $joinClass[1] . ') as t' . $i . ' ';
                    }
                    else
                    {
                        $from_add .= ' ' . $t . ' as t' . $i . ' ';
                    }

                    $from_add .= ' ON ' . $joinClass['on'];
                }
                else
                {
                    static::initModel( $joinClass );
                    $t = $joinClass::getTable();

                    $from_add .= ', `' . $t . '` as t' . $i;
                }
            }
        }
        $orders = '';
        if( !empty( $arParams['arSort'] ) )
        {
            $orders = array();
            foreach( $arParams['arSort'] as $by => $order )
            {
                $orders[] = $by . ' ' . $order;
            }
            if( sizeof( $order ) > 0 )
            {
                $orders = 'ORDER BY ' . implode( ',', $orders );
            }
            else
            {
                $orders = '';
            }
        }

        return 'SELECT ' . $select . ' FROM ' . $table . $from_add . ' WHERE ' . $sWhere . ' ' . $orders . $limit;
    }

    
    /**
     * Функция возвращает массив объектов определнного класса
     * Если присутсвует параметр $arSysOptions[index] - отдаем индексированный массив
     *
     * @param Array $arParametrs массив с данными класса и параметров фильтрации для выбора нужных объектов
     * @param Array $arSysOptions массив с системными опциями (такие как отключить кеширование: nocache=>true)
     * @return Array
     *
     * @example возвращает первые 20 записей сделанные в блоге после 1 января 2015 года по убыванию
     * Storages::one()->getAll(array
     *      'sModel'=>'Blogs',
     *      'arFilter' => array(
     *          'tCreated' => array('>' => '2015-01-01')
     *      ),
     *      'arSort' => array(
     *          'tCreated' => 'desc'
     *      )
     *      'nPageSize' => 30,
     *      'nPage' => 1
     *      
     * ));
     */
    public static function getAll($arParametrs = array(), $arSysOptions = array())
    {
        $sKeyCache = md5( serialize( $arParametrs ) );

        if( empty($arParametrs['sModel']) ) {
            throw new \Exception('Cannot define class for Model');
        }

        static::initModel( $arParametrs['sModel'] );
        if (! empty($arSysOptions['index'])) {
            $sKeyCache .= 'idx';
        }
        
        /* узнаем название класса модели */
        $sClassName = $arParametrs['sModel'];
        $arResult = array();
        
        if(
            empty( static::$arCacheTables[$sClassName][$sKeyCache] )
            || (isset( $arSysOptions['nocache'] ) && $arSysOptions['nocache'])
        ) {
            if (empty($arParametrs["nPageSize"])) {
                $arParametrs['nPageSize'] = 100;
            }

            if (empty( $arParametrs['nPage'])) {
                $arParametrs['nPage'] = 1;
            }

            if (empty($arParametrs['arFilter'])) {
                $arParametrs['arFilter'] = Array();
            }

            $arSelect = array();


            /* Собираем SQL */
            $sSql = static::generateSelectSQL($arParametrs);

            /* Отправляем запрос к базе */
            if(! empty( $arSysOptions['debug'])) {
                echo '[[[' . $sSql . ']]]';
            }
            $st = Storages::one()->getStorage()->query($sSql, PDO::FETCH_CLASS, $sClassName, array(Storages::one()));

            if( $st )
            {
                $arResult = $st->fetchAll();

                if( !empty( $arSysOptions['index'] ) )
                {
                    $idname     = $sClassName::getIdName();
                    $arTmp = array();
                    foreach( $arResult as $obTmp )
                    {
                        $arTmp[ $obTmp->{$idName} ] = $obTmp;
                    }
                    $arResult = array();
                    $arResult = & $arTmp; 
                }
            }

            if( empty(static::$arCacheTables[$sClassName]) )
            {
                static::$arCacheTables[$sClassName] = array();
            }

            static::$arCacheTables[$sClassName][$sKeyCache] = & $arResult;
        }//end if self::$_cache

        return static::$arCacheTables[$sClassName][$sKeyCache];
    }

    public static function getCountAll( $arParametrs = array(), $arSysOptions = array() )
    {
        $arParametrs['fileds'] = array('count(*) as cnt');

        if( empty($arParametrs['sModel']) ) {
            throw new \Exception('Cannot define class for sModel');
        }
        static::initModel( $arParametrs['sModel'] );

        if( isset($arParametrs['nPage']) )
        {
            unset($arParametrs['nPage']);
        }
        if( isset($arParametrs['nPageSize']) )
        {
            unset($arParametrs['nPageSize']);
        }
        if( isset($arParametrs['arSort']) )
        {
            unset($arParametrs['arSort']);
        }
        if( empty( $arParametrs['arFilter'] ) )
        {
            $arParametrs['arFilter'] = Array();
        }

        $sKeyCache = md5( serialize( $arParametrs ) );
        
        /* узнаем название класса модели */
        $sClassName = $arParametrs['sModel'];
        $iResult = 0;
        
        if(
            empty( static::$arCacheTables[$sClassName][$sKeyCache] )
            || (isset( $arSysOptions['nocache'] ) && $arSysOptions['nocache'])
        )
        {

            $arSelect = array();

            /* Собираем SQL */
            $sSql = static::generateCountSQL( $arParametrs );

            /* Отправляем запрос к базе */
            if( !empty( $arSysOptions['debug'] ) )
            {
                echo '[[[' . $sSql . ']]]';
            }
            $st = Storages::one()->getStorage()->query($sSql);

            if( $st )
            {
                $arTmp = $st->fetch(PDO::FETCH_ASSOC);
                if( isset($arTmp['cnt']) )
                {
                    $iResult=$arTmp['cnt'];
                }
        
            }

            if( empty(static::$arCacheTables[$sClassName]) )
            {
                static::$arCacheTables[$sClassName] = array();
            }

            static::$arCacheTables[$sClassName][$sKeyCache] = $iResult;
        }//end if self::$_cache

        return static::$arCacheTables[$sClassName][$sKeyCache];
    }
    
    
    
    /**
     * @param array $arParametrs данные для запроса
     * @param array $arSysOptions Дополнительные условия по отбору объекта
     * @return Model
     **/
    public static function getOne( $arParametrs = array(), $arSysOptions = array() )
    {
        $arParametrs['nPage'] = 1;
        $arParametrs['nPageSize'] = 1;
        
        $rows = static::getAll($arParametrs, $arSysOptions);

        $obj = null;
        foreach($rows as $obj) {
            break;
        }

        return $obj;
    }
    
    public static function deleteAll( $arParametrs=array() )
    {
        if(empty($arParametrs['sModel'])) {
            throw new \Exception('Cannot define class for Model');
        }
        static::initModel( $arParametrs['sModel'] );
        
        /* узнаем название класса модели */
        $sClassName = $arParametrs['sModel'];

        /*
         * Выставляем базу и таблицу для запроса
         */
        if (! empty($arParametrs['sDatabase'] )) {
            $sTable = '`'.$arParametrs['sDatabase'].'`.`'.$sClassName::getTable().'`';
        } elseif($sClassName::getDatabase() > '') {
            $sTable = '`'.$sClassName::getDatabase().'`.`'.$sClassName::getTable().'`';
        } else {
            $sTable = '`'.$sClassName::getTable().'`';
        }

        $sWhere     = static::generateWhereSQL( $arParametrs );
        $sql     = "DELETE FROM " . $sTable . " WHERE " . $sWhere;
        $result     = static::one()->execute( $sql );

        if (false !== $result) {
            if (0 === $result) {
                $err = static::one()->errorInfo();
                if ('00000' != $err[0]) {
                    echo "<div class='error'>" . $sql;
                    echo ($err);
                    echo '</div>';
                }//end if
            }//end if
        }//end if

        static::clearInnerCache($sClassName);
        
        return $result;
    }

    
    /**
     * Добавляем ошибку
     * @param string $sError текст ошибки
     * @param string $sClassNamel название класса модели в котором произошла ошибка ("_" - означает общая ошибка)
     * @param string $sField название поля в котором обнаружена ошибка
     */
    public static function addError($sError,$sClassName='_',$sField = '_'){
        if ( empty(static::$arErrors[$sClassName]) ){
            static::$arErrors[$sClassName] = array();
        }//end if

        if( empty(static::$arErrors[$sClassName][$field]) ){
            static::$arErrors[$sClassName][$sField] = array();
        }
        static::$arErrors[$sClassName][$sField][] = $sError;

    }//end function
    
    // возвращает TRUE если в модели и поля есть ошибки
    public static function isError($sClassName='_',$sField = '_') {
        if(
            isset(static::$arErrors)
            && isset(static::$arErrors[$sClassName])
            && isset(static::$arErrors[$sClassName][$sField])
            && sizeof(static::$arErrors[$sClassName][$sField]) > 0
        ){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Функция проверяет есть ли ошибки в модели с названием $sClassName, или вообще, есть ли ошибки ($sClassName == '')
     * @param string $sClassName название класса модели в которой проверяем наличие ошибок
     * 
     * @return boolean
     */
    public static function isErrors($sClassName='') {
        if( $sClassName == ''){
            return ( sizeof(static::$arErrors) > 0 );
        } else {
            if(
                isset(static::$arErrors[$sClassName])
                && sizeof(static::$arErrors[$sClassName]) > 0
            ){
                return true;
            } else {
                return false;
            }
        }
    }//end function
    
    /**
     * Функция возвращает массив из текстов ошибок для поля указанного класса
     *
     * @return array 
     */
    public function getError($sClassName='_',$sField='_', $isClear=true) {
        if(
            isset(static::$arErrors[$model])
            && isset(static::$arErrors[$model][$sField])
        ){
            $e = static::$arErrors[$sClassName][$sField];
            
            if( $isClear ){
                static::$arErrors[$sClassName][$sField] = null;
                unset(static::$arErrors[$sClassName][$sField]);

                if( sizeof(static::$arErrors[$sClassName]) == 0 )
                {
                    unset(static::$arErrors[$sClassName]);
                }
                
                if( sizeof(static::$arErrors) == 0 )
                {
                    static::$arErrors = array(); 
                }
            }
            
            return $e;
        } else {
            return false;
        }
    }//end function

    // @todo доделать 
    public function getErrors($model='_', $isClear=false) {
        if( $model == ''){
            
        } else {
            if(
                isset($this->errors)
                && isset($this->errors[$model])
                && $this->errors[$model]['ierr'] > 0
            ){
                $e = $this->errors[$model];
                if( $isClear ){
                    unset($this->errors[$model]);
                    $this->errors[$model] = null;
                    $this->errors['ierr'] -= sizeof($e);
                }
                return $e;
            } else {
                return false;
            }
        }
    }//end function

    // стираем ошибку 
    public function clearError($model='_',$field='_') {
        if(
            isset($this->errors)
            && isset($this->errors[$model])
            && isset($this->errors[$model][$field])
            && sizeof($this->errors[$model][$field]) > 0
        ) {
            $this->errors[$model]['ierr'] -= sizeof($this->errors[$model][$field]);
            $this->errors['ierr'] -= sizeof($this->errors[$model][$field]);
            unset($this->errors[$model][$field]);
            $this->errors[$model][$field] = null;
        }
    }//end function

    // стираем ошибки, если указана модель, то стираем ошибки, только указанной модели
    public function clearErrors($model='') {
        if( $model == ''){
            $this->errors = array('commons'=>array('ierr'=>0), 'ierr'=>0);
        } elseif( isset($this->errors[$model]) ) {
            $this->errors['ierr'] -= $this->errors[$model]['ierr'];
            unset( $this->errors[$model] );
            $this->errors[$model] = array();
        }
    }//end function

    public function isConnected()
    {
        return is_object( Storages::one()->getStorage() );
    }
    
    /**
     * Функция начинает транзакцию
     * @return bool возвращет TRUE если успех и FALSE если не успех
     */
    public function begin()
    {
        return Storages::one()->getStorage()->beginTransaction();
    }

    /**
     * Функция коммитит все изменения в рамках ранее начатой транзакции
     * @return bool возвращет TRUE если успех и FALSE если не успех
     */
    public function commit()
    {
        return Storages::one()->getStorage()->commit();
    }

    /**
     * Функция отменяет все изменения в рамках ранее начатой транзакции
     * @return bool возвращет TRUE если успех и FALSE если не успех
     */
    public function rollback()
    {
        return Storages::one()->getStorage()->rollBack();
    }
    
    public static function clearInnerCache($sClassName = '')
    {
        if( $sClassName == '' )
        {
            static::$arCaches = array();
        }
        elseif( isset(static::$arCacheTables[$sClassName]) )
        {
            static::$arCacheTables[$sClassName] = array();
        }
        elseif( $sClassName == ':all:')
        {
            static::$arCacheTables = array();
            static::$arCaches = array();
        }
    }


    /**
     * @throws WrongArgumentException
     * @return Storages
    **/
    public function addStorage($key, $arDBConfig)
    {
        if (isset($this->arPools[$key])) {
            throw new \Exception("already have '{$key}' link db");
        }
        
        $arDBPoolConfig = Application::one()->dbpool;
        if( $arDBPoolConfig == null ){
            $arDBPoolConfig = array();
        }
        $arDBPoolConfig[ $key ] = $arDBConfig;
        Application::one()->dbpool = $arDBPoolConfig;

        static::$sCurKey = $key;
        return $this;
    }
    
    public function disconnect($sKey='') {
        if( $sKey > '' ) { 
            static::$sCurKey = $sKey;
        }

        if ( empty($this->arPools[static::$sCurKey]) ) {
            throw new \Exception("already disconnected '{static::$sCurKey}' link db");
        }
        
        $this->arPools[static::$sCurKey] = null;
    }
    
    /**
     * Функция возвращает класс для работы с хранилищем
     * @return class 
     */
    private function getStorage($sKey = '') {
        if( $sKey > '' ) { 
            static::$sCurKey = $sKey;
        }
        
        if ( empty($this->arPools[static::$sCurKey]) ) {
            $arDBPoolConfig = Application::one()->dbpool;
            if( empty($arDBPoolConfig[static::$sCurKey]) ) {
                throw new \Exception('Cannot read config for initialize DB {$sKey}');
            }

            $arPdoOptions = array(
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            );

            $this->arPools[static::$sCurKey] =  new PDO(
                $arDBPoolConfig[static::$sCurKey]['dsn'],
                $arDBPoolConfig[static::$sCurKey]['user'],
                $arDBPoolConfig[static::$sCurKey]['password'],
                $arPdoOptions
            );
            
            $this->arPools[static::$sCurKey]->exec("SET NAMES 'utf8'");
        }
        
        return $this->arPools[static::$sCurKey];
    }
    
    public function setKey($sKey='') {
        if( $sKey > '' ) { 
            static::$sCurKey = $sKey;
        } else {
            static::$sCurKey = 'default';
        }
        return $this;
    }

    /**
     * создется объект, для работы с БД. Данные для соединения берутся из класса SFW_Config->dbpool
     */
    public function __construct() {
        $arCacheConfig = Application::one()->cache;
        if( $arCacheConfig && isset($arCacheConfig['enable']) && $arCacheConfig['enable'] == 'on') {
            $this->bEnableCache = true;
            /* @todo add code for init cache classes */
        }
        
        static::$arErrors = array(
            'commons'=>array()
        );
    }//end function
    

    /**
	 * @param string $sSql
	 * @param array $arSysOptions
     * @return class pdo_statement
     */
    public function query($sSql, $arSysOptions=array())
	{
        $sCacheKey = hash('md5',$sSql);

        if( !empty($arSysOptions['nocache']) || empty( $this->arCaches[$sCacheKey] ) ){
            if( isset($arSysOptions['type']) && $arSysOptions['type'] == 'class' && isset($arSysOptions['className']) ) {
                $rows = $this->getStorage()->query($sSql, PDO::FETCH_CLASS, $arSysOptions['className'], array($this) );
            } else {
                $rows = $this->getStorage()->query($sSql);
            }
            
            if( empty($arSysOptions['nocache']) ) {
                $this->arCaches[$sCacheKey] = $rows;
            }
        } else {
            $rows = $this->arCaches[$sCacheKey];

        }//end if else
        
        return $rows;
    }

	/**
	 * @param string $sSql
	 * @param array $arSysOptions
	 * 
	 * @return array of classes
	 */
    public function queryAll($sSql, $arSysOptions=array() )
    {
        $sCacheKey = hash('md5',$sSql);
        $rows = null;
        
        if (! empty($arSysOptions['nocache']) || empty( $this->arCaches[$sCacheKey] ) ){
            if (isset($arSysOptions['type']) && $arSysOptions['type'] == 'class' && isset($arSysOptions['className']) ) {
                $st = $this->getStorage()->query($sSql, PDO::FETCH_CLASS, $arSysOptions['className'], array($this) );
            } else {
                $st = $this->getStorage()->query($sSql, PDO::FETCH_ASSOC);
            }
            
            $rows = $st ? $st->fetchAll() : array() ;

            if (empty($arSysOptions['nocache'])) {
                static::$arCaches[$sCacheKey] = $rows;
            }
        } else {
            $rows = static::$arCaches[$sCacheKey];

        }//end if else
        
        return $rows;
    }

	public function quote($s)
	{
		return $this->getStorage()->quote( $s ) ;
	}

    public function execute($sql)
    {
        $rows = $this->getStorage()->exec($sql);
        return $rows;
    }

    public function getLastID($name=NULL)
    {
        $val = $this->getStorage()->lastInsertId($name);
        return $val;
    }

    public function errorInfo()
    {
        return $this->getStorage()->errorInfo();
    }
    
}
