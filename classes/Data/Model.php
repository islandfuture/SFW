<?php
namespace IslandFuture\Sfw\Data;

/**
 * Класс для работы с объектами в базе. Нужен для описания типов объектов и облегечения
 * работы с рутинными операциями:
 */
class Model
{

    public $isNewRecord     = false;
    protected $arFields     = array();

    public function __destruct()
    {
        $this->arFields = null;
        return true;
    }

    public function __construct($arFields = array())
    {

        if (sizeof($this->arFields) == 0) {
            $this->arFields = static::getClearFields();
        }

        if (is_array($arFields) && sizeof($arFields) > 0) {
            foreach ($arFields as $key => $value) {
                if (key_exists($key, $this->arFields)) {
                    $this->arFields[$key] = $value;
                }
            }//end foreach*/
        }
    }

    /**
     * Форматирует строку
     * @return string
     */
    public function __getFormatString()
    {
        $str = "array(\n";
        foreach ($this->arFields as $key => $value) {
            $str .= "\t'$key' => " . \IslandFuture\Sfw\Data\Storages::one()->quote($value) . ",\n";
        }//end foreach
        $str .= ")\n";

        return $str;
    }//end function

    /**
     * Возвращает массив созначениями полей
     * @return array
     */
    public function __getFields()
    {
        return $this->arFields;
    }

    static public function getTable()
    {
        throw new \Exception('not found table name in model ' . get_class($this));
    }

    /**
     * Возвращает массив с правилами валидации вида:
     *     array(
     *        'название поля' => array(array(правило валидации),array(правила валидации)),
     *        'name' => array(
     *            'isreq' => array('error'=>'поле оьязательное'),
     *            'islength' => array('min'=>10,'max'=>'30','errorMin' => 'Поле слишком короткое', 'errorMax' => 'Поле слишком длинное')
     *        ),
     *        'email' => array(
     *            'isreq' => array('error'=>'поле оьязательное'),
     *            'islength' => array('min'=>4,'max'=>'250','error' => 'Email может быть от 4 до 250 символов')
     *            'isemail' => array('error' => 'Некорректный формат email')
     *        )
     *     )
     */
    static public function getRules()
    {
        return array();
    }

    static public function getDatabase()
    {
        return '';
    }

    static public function getRelations()
    {
        return array();
    }

    static public function getTypes()
    {
        return array();
    }

    static public function getIdName()
    {
        return 'id';
    }

    static public function getIdDefault()
    {
        return 'AUTOINC'; // UUID, VALUE
    }

    public function is($name)
    {
        $result = false;
        if (key_exists($name, $this->arFields)) {
            $result = true;
        } else {
            $relations = static::getRelations();
            if (isset($relations[$name])) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Возвращает значение поля или объект или массив
     * @param string $name название поля или связи
     * @return mixed
     */
    public function __get($name)
    {        
        if (! $name) {
            return null;
        }

        if (key_exists($name, $this->arFields)) {
            return $this->arFields[$name];
        } else {
            $arRelations = static::getRelations();
            if (isset($arRelations[$name])) {
                $result = null;
                $arRelation  = $arRelations[$name];

                if ($arRelation[0] == '::virtual::') {
                    /**
                     * array('::virtual::','ONE','local_field',array('fieldvalue'=>array(val0,val1,...)))
                     */
                    $local_id       = $arRelation[2];
                    $local_id_value = $this->$local_id;
    
                    if ($arRelation[1] == 'ONE' && is_array( $arRelation[3])) {
                        $arValues = $arRelation[3];
                        if (isset($arValues[$local_id_value])) {
                            if (is_array($arValues[$local_id_value] ) && sizeof($arValues[$local_id_value]) == 1) {
                                $result = $arValues[$local_id_value][0];
                            } else {
                                $result = $arValues[$local_id_value];
                            }
                        }
                    }
                } elseif ($arRelation[0] == '::table::') {
                    
                    if (! empty($arRelation[3])) {
                        $classname = $arRelation[3];
                    } else {
                        throw new \Exception('Unknown model in relation ['.$name.']');
                    }

                    $local_id       = $arRelation[2];
                    $local_id_value = $this->$local_id;

                    if ($arRelation[1] == 'ONE') {
                        $values = $arRelation[4];

                        $arFilter = array($values => array('=' => $local_id_value));
                        if (! empty($arRelation['sDatabase'])) {
                            $result = \IslandFuture\Sfw\Data\Storages::getOne(
                                array(
                                    'sModel'    => $classname,
                                    'arFilter'  => $arFilter,
                                    'sDatabase' => $arRelation['sDatabase']
                                )
                            );
                        } else {
                            $result = \IslandFuture\Sfw\Data\Storages::getOne(
                                array(
                                    'sModel' => $classname,
                                    'arFilter' => $arFilter
                                )
                            );
                        }
                        //end ONE
                    } elseif ($arRelation[1] == 'MORE') {
                        $values = $arRelation[4];

                        $arParams = array(
                            'sModel' => $classname
                        );

                        if ( is_array( $values ) && sizeof( $values ) == 3 ) {
                            //если нужный нам объект связан с текущим как многие ко многим (то есть через доп.таблицу)
                            // тогда в массиве описывается [0] - возвращаемый код в нужной таблице, [1] - промежуточная класс, [2] - поле по которому идет отбор
                            $refParams = array(
                                'sModel' => $values[1],
                                'fields' => $values[0],
                                'arFilter' => array(
                                    $values[2] => array('=' => $local_id_value)
                                )
                            );
                            $arParams['arFilter'] = array(
                                'id' => array(
                                    'in' => \IslandFuture\Sfw\Data\Storages::generateSelectSQL($refParams)
                                )
                            );
                        } else {
                            //если есть доп.условие для выбора списка значений
                            $arParams['arFilter'] = array(
                                $values => array('=' => $local_id_value)
                            );

                            if( !empty( $arRelation[5] ) && is_array( $arRelation[5] ) )
                            {
                                $arParams = array_merge_recursive( $arParams, $arRelation[5] );
                            }
    
                            if( !empty( $arRelation['sDatabase'] ) )
                            {
                                $arParams['sDatabase'] = $arRelation['sDatabase'];
                            }
                        }
                        $result = $classname::getAll( $arParams );
                        if( !$result )
                        {
                            $result = array();
                        }
                        //end MORE
                    }
                }
                return $result;
            } 
        }
        $arTrace = debug_backtrace();
        $e = new ErrorException('Unknown fields ' . get_class($this) . '::$' . $name, E_USER_ERROR, 1, $arTrace[0]['file'], $arTrace[0]['line']);
        throw $e;
    }

    public function __set($name, $val)
    {
        if (sizeof($this->arFields) == 0) {
            $this->arFields = $this->getClearFields();
        }

        if (key_exists($name, $this->arFields)) {
            $this->arFields[$name] = $val;
        } else {
            $arTrace = debug_backtrace();
            $e = new ErrorException('Cannot assign '.$val.' to unknown fields ' . get_class($this) . '::$' . $name, E_USER_ERROR, 1, $arTrace[0]['file'], $arTrace[0]['line']);
            throw $e;
        }
    }

    public function getOptionsList($sRelname, $selected = '', $where = null, $sViewField = 'sName', $glue = ' / ')
    {
        //$arRelations = $this->$sRelname(false); // $this->getRelations();
        $arRelations = static::getRelations();
        $arResult = array();
        if (isset($arRelations[$sRelname])) {
            $arRelation = $arRelations[$sRelname];
            if( $arRelation[0] == '::virtual::' )
            {
                foreach ($arRelation[3] as $idx => $mRelation) {
                    if (is_array($mRelation)) {
                        $arResult[] = '<option value="' . $idx . '" ' . ($idx == $selected ? 'selected="selected"' : '') . '>' . $mRelation[0] . '</option>';
                    } else {
                        $sKey = $mRelation::getIdName();
                        $arResult[] = '<option value="' . $mRelation->{$sKey} . '" ' . ($mRelation->{$sKey} == $selected ? 'selected="selected"' : '') . '>' . $mRelation->{$sViewField} . '</option>';
                    }
                }/* end foreach */
            }
            elseif( $arRelation[0] == '::table::' )
            {
                if( $arRelation[1] == 'ONE' )
                {
                    $local_id = $arRelation[2];
                    $class    = $arRelation[3];
                    $value    = $view_field;
                    if( $selected == '' )
                    {
                        $select = $this->__get( $local_id );
                    }
                    else
                    {
                        $select = $selected;
                    }
                    $key = $arRelation[4];
    
                    if( is_array( $view_field ) )
                    {
                        foreach( $view_field as $key => $val )
                        {
                            if( empty( $val ) )
                            {
                                $val = 'asc';
                            }
                            $sort[$key] = $val;
                        }
                    }
                    else
                    {
                        $sort[$view_field] = 'asc';
                    }
    
                    $arParams = array(
                        'sModel' => $class,
                        'arSort' => $sort,
                        'arFilter' => is_array( $where ) ? $where : array()
                    );
                    
                    foreach (\IslandFuture\Sfw\Data\Storages::getAll($arParams) as $res) {
                        if( is_array( $view_field ) )
                        {
                            $str = array();
                            foreach( $view_field as $val )
                            {
                                $str[] = $res->{$val};
                            }
                            $str		 = implode( ' / ', $str );
                            $arResult[]	 = '<option value="' . $res->{$key} . '" ' . ($res->{$key} == $select ? 'selected="selected"' : '') . '>' . $str . '</option>';
                        }
                        else
                        {
                            $arResult[] = '<option value="' . $res->{$key} . '" ' . ($res->{$key} == $select ? 'selected="selected"' : '') . '>' . $res->{$value} . '</option>';
                        }
                    }//end foreach
                }
            }
        }


        return implode("\n", $arResult);
    }

    //end function

    public function attributes($params, $isClearEmpty = true, $isSetNull = false )
    {
        $fields = $this->getClearFields();

        foreach ($fields as $key => $val) {
            if (isset($params[$key])
                && (            $params[$key] != ''
                || $isClearEmpty            )
            ) {
                if ($isSetNull
                    && $params[$key] == ''
                ) {
                    $this->arFields[$key] = null;
                } else {
                    $this->arFields[$key] = $params[$key];
                }
            }
        }//end foreach
        return $this;
    }

    public function delete($arParams = array()) 
    {
        $bTransaction = false;

        /*
         * Проверяем параметры для передачи в методы before_delete и after_delete
         */
        if (! isset($arParams['before'])) {
            $arParams['before'] = false;
        }
        
        if (! isset($arParams['after'])) {
            $arParams['after'] = false;
        }

        /*
         * Выставляем базу и таблицу для запроса
         */
        if (! empty($arParams['sDatabase'])) {
            $sTable = '`' . $arParams['sDatabase'] . '`.`' . static::getTable() . '`';
        } elseif (static::getDatabase() > '') {
            $sTable = '`' . static::getDatabase() . '`.`' . static::getTable() . '`';
        } else {
            $sTable = '`'.static::getTable().'`';
        }

        /*
         * Проверяем необходимость использования транзакции
         */
        if (isset($arParams['transaction'] ) && $arParams['transaction'] == true) {
            $bTransaction = true;
            Storages::one()->begin();
        }


        if (! $this->beforeDelete($arParams['before'])) {
            if ($bTransaction) {
                Storages::one()->rollback();
            }
            return false;
        }

        $idKey = static::getIdName();
        $sql     = "DELETE FROM " . $sTable . " WHERE ".$idKey." = '" . $this->arFields[$idKey] . "'";
        $result     = Storages::one()->execute($sql);
    
        $sClassName = get_called_class();
        Storages::clearInnerCache($sClassName);

        if (! $result) {
                $err = Storages::one()->errorInfo();
            if ($err[0] != '00000') {
                echo "<div class='error'>" . $sql;
                echo ($err);
                echo '</div>';
            }//end if
        }


        if ($result > 0) {
            if ($this->afterDelete($arParams['after']) === false) {
                if ($bTransaction) {
                    Storages::one()->rollback();
                }
                $result = false;
            } else {
                if ($bTransaction) {
                    Storages::one()->commit();
                }
            }
        } elseif ($result === false && $bTransaction) {
            Storages::one()->rollback();
        }

        return $result;
    } //end function

    protected function beforeDelete($mParams=array())
    {
        return true;
    }

    protected function afterDelete($mParams=array() )
    {
        return true;
    }

    protected function beforeSave($mParams=array() )
    {
        return true;
    }

    protected function afterSave($mParams=array() )
    {
        return true;
    }

    /**
     * Сохраняем объект в БД. Если объект новый и поле не автоинкрементное, то перед вызовом этого метода
     * нужно установить флаг новой записи в TRUE: $obj->isNewRecord = true;
     * @return mixed больше 0, если сохраненно успешно, false если ошибка (ошибка сохраняется в Storages::$lastError)
     */
    public function save($arParams = array() )
    {

        $values             = array();
        $names             = array();
        $types             = static::getTypes(); /* for future */
        $values_upd         = array();
        $def             = static::getDefault();
        $idname             = static::getIdName();
        $bTransaction     = false;
        $sClassName = get_called_class();
        
        if ($idname && !$this->__get($idname)) {
            $this->isNewRecord = true;
        }

        /*
         * Выставляем базу и таблицу для запроса
         */
        if (!empty($arParams['sDatabase'])) {
            $sTable = '`'.$arParams['sDatabase'].'`.`'.static::getTable().'`';
        }
        elseif (static::getDatabase() > '') {
            $sTable = '`'.static::getDatabase().'`.`'.static::getTable().'`';
        }
        else
        {
            $sTable = '`'.static::getTable().'`';
        }


        /*
         * Проверяем параметры для передачи в методы before_save и after_save
         */
        if (!isset($arParams['before'] )) {
            $arParams['before'] = false;
        }
        if (!isset($arParams['after'] )) {
            $arParams['after'] = false;
        }

        /*
         * Проверяем необходимость использования транзакции
         */
        if (isset($arParams['transaction'] ) && $arParams['transaction'] == true) {
            $bTransaction = true;
            Storages::one()->begin();
        }


        if (! $this->beforeSave($arParams['before'])) {
            if ($bTransaction) {
                Storages::one()->rollback();
            }
            return false;
        }

        foreach ($this->arFields as $key => $value) {
            $names[$key] = $key;

            if ($value === null && $key != $idname) {
                if (isset($def[$key] )) {
                    if (in_array($def[$key], array( 'CURRENT_TIMESTAMP', 'now()', 'NOW()', 'NULL' ))) {
                        $values[$key]         = $def[$key];
                        $values_upd[$key]     = "`" . $key . "`=" . $def[$key];
                    } else {
                        $values[$key]         = \IslandFuture\Sfw\Data\Storages::one()->quote($def[$key]); //$value;
                        $values_upd[$key]     = "`" . $key . "`=" . \IslandFuture\Sfw\Data\Storages::one()->quote($def[$key]);
                    }
                    //echo $key.'-';
                } else {
                    //$values[$key] = 'NULL';
                    unset($names[$key]);
                }
            } else {
                $values[$key] = \IslandFuture\Sfw\Data\Storages::one()->quote($value);

                if ($key != $idname || ($def != 'UUID' && $def != 'AUTOINC' )) {
                    $values_upd[$key] = "`" . $key . "`=" . \IslandFuture\Sfw\Data\Storages::one()->quote($value);
                }// end if
            }
        }//end foreach
        
        if ($this->isNewRecord) {

            if (empty($values[$idname] ) || $values[$idname] == "''") {
                switch ($this->getIdDefault()) {
                case 'UUID':
                    do
                    {
                        $uid = mt_rand($this->uidMin, $this->uidMax);
                        $isExists = Storages::getCountAll(
                            array(
                                'sDatabase' => $this->getDatabase(),
                                'sModel' => get_called_class(),
                                'arFilter' => array(
                                    $idname => array('=' => $uid)
                                )
                            )
                        );
                    }
                    while($isExists);

                    $values[$idname] = "'" . $uid . "'";
                    $this->$idname = $uid; 
                    break;
                case 'GUID':

                    do {
                        $uid = str_replace('.', '', uniqid('', true));
                        $isExists = Storages::getCountAll(
                            array(
                                'sDatabase' => $this->getDatabase(),
                                'sModel' => get_called_class(),
                                'arFilter' => array(
                                    $idname => array('=' => $uid)
                                )
                            )
                        );
                    } while($isExists);

                    $values[$idname] = "'" . $uid . "'";
                    $this->$idname = $uid; 
                    break;
                case 'AUTOINC':
                    $values[$idname] = 'NULL';
                    break;
                default:
                    break;
                }//end switch

                if ($idname > '') {
                    $names[$idname] = $idname;
                }
            }
            $sql = "INSERT INTO " . $sTable . ' (`' . implode('`,`', $names) . "`) VALUES(" . implode(',', $values) . ")";
        }
        else
        {
            $sql = "UPDATE " . $sTable . " SET " .
                implode(',', $values_upd) .
                " WHERE $idname = '" . $this->$idname . "'";
        }//end if else

        $result = Storages::one()->execute($sql);
        Storages::clearInnerCache($sClassName);

        if($result !== false) {

            if ($result === 0) {
                $err = Storages::one()->errorInfo();
                if ($err[0] != '00000') {
                    echo "<div class='error'>" . $sql;
                    var_dump($err);
                    echo '</div>';
                }
            }

            if (static::getIdDefault() == 'AUTOINC' && $this->isNewRecord) {
                $this->__set($idname, Storages::one()->getLastID());
            }

            $result = true;
        } else {
            $result = false;
        }

        if (! $result) {
            //@todo вставить добавление ошибок возникших при сохранении
        }

        if ($result) {
            if (false === $this->afterSave($arParams['after'])) {
                if ($bTransaction) {
                    Storages::one()->rollback();
                }
                return false;
            } else {
                if ($bTransaction) {
                    Storages::one()->commit();
                }
            }
            $this->isNewRecord = false;
        } else {
            if ($bTransaction) {
                Storages::one()->rollback();
            }
        }
        return $result;
    }

    static public function getClearFields()
    {
        die( 'bred');
    }

    static public function getDefault()
    {
        return array();
    }

    /*     * ************ Utility ******************* */

    public function formatDate($name, $format = 'd.m.Y')
    {
        $str = $this->__get($name);
        $str = strtotime($str);

        if ($str > 0) {
            return date($format, $str);
        } else {
            return '';
        }
    }

    /**
     * @return string возвращает строку где переврод строки заменен на <br />
     */
    public function getTextToHtml($name)
    {
        if (isset($this->arFields[$name])) {
            return str_replace("\n", '<br />', $this->arFields[$name]);
        }
        return '';
    }

    public function validate()
    {
        return Validator::isValidateModel($this);
    }
    
    /** error functions **/

    /**
     * Добавляем ошибку
     * @param string $sError      текст ошибки
     * @param string $sField      название поля в котором обнаружена ошибка
     * @return \IslandFuture\Sfw\Data\Model
     */
    public function addError($sError, $sField='_')
    {
        \IslandFuture\Sfw\Data\Storages::addError($sError, get_class($this), $sField);
        return $this;
    }
    
    /**
     * Проверяем, есть ли ошибка в каком-то поле или в целом в моделе
     * @param string $sField      название поля которое проверяется (нужено указать "_" - для проверки общих ошибок)
     * @return boolean
     */
    public function isError($sField='_')
    {
        return \IslandFuture\Sfw\Data\Storages::isError(get_class($this), $sField);
    }

    /**
     * Проверяем, есть ли ошибка в каком-то поле или в целом в моделе
     * @param string $sField      название поля которое проверяется (нужено указать "_" - для проверки общих ошибок)
     * @return boolean
     */
    public function isErrors()
    {
        return \IslandFuture\Sfw\Data\Storages::isErrors(get_class($this));
    }

    /**
     * Функция возвращает массив из текстов ошибок для указанного поля
     *
     * @return array 
     */
    public function getError($sField = '_', $isClear = true)
    {
        return \IslandFuture\Sfw\Data\Storages::getError(get_class($this), $sField, $isClear);
    }

    /**
     * Функция возвращает массив из текстов ошибок для указанного поля
     *
     * @return array 
     */
    public function getErrors($isClear = false)
    {
        return \IslandFuture\Sfw\Data\Storages::getErrors(get_class($this), $isClear);
    }
}
//end class
