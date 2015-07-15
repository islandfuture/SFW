<?php
namespace IslandFuture\Sfw\Data;
/**
 * (еще в процессе) Класс предназначен для валидации данных, передотправкой в БД
 * 
 * @link    https://github.com/islandfuture/SFW
 * @author  Michael Akimov <michael@island-future.ru>
 * @version GIT: $Id$
 **/
class Validator
{
    
    /**
     * Проверка данных модели на соответствие правилам
     * @param \IslandFuture\Sfw\Data\Model $oModel модель которую нужно отвалидировать
     */
    static public function isValidateModel($oModel)
    {
        /**
         * @example
         *     $arRules = array(
         *        'название поля' => array(array(правило валидации),array(правила валидации)),
         *        'name' => array(
         *            'req' => array('error'=>'поле оьязательное'),
         *            'length' => array('min'=>10,'max'=>'30','code'=>'utf-8','errorMin' => 'Поле слишком короткое', 'errorMax' => 'Поле слишком длинное')
         *        ),
         *        'email' => array(
         *            'req' => array('error'=>'поле оьязательное'),
         *            'length' => array('min'=>4,'max'=>'250','error' => 'Email может быть от 4 до 250 символов')
         *            'email' => array('error' => 'Некорректный формат email')
         *        )
         *     )
         */
        $arRules = $oModel::getRules();
        $bResult = true;
        
        foreach( $arRules as $sField => $arRule )
        {
            $mVal = $oModel->$sField;
            foreach($arRule as $sRule => $arRuleParam )
            {
                if(method_exists('\IslandFuture\Sfw\Data\Validator', $sRule) ) {
                    if(!static::$sRule($mVal, $arRuleParam) ) {
                        $bResult = false;
                    }
                }
                elseif(class_exists($sRule, true) ) {
                    if(!$sRule::validate($mVal, $arRuleParam) ) {
                        $bResult = false;
                    }
                    
                }
            } /* end foreach $arRule */
            
        }/* end foreach $arRules */
        
        return $bResult;
    }
    
    static public function req( $sVal, $arParam=array()) 
    {
        return !empty($sVal);
    }

    static public function string( $sVal, $arParam=array()) 
    {
        return is_string($sVal);
    }

    static public function phone( $sVal, $arParam=array()) 
    {
        if($sVal > '' && (!preg_match("/^(.+)[(\s][0-9\s]+[)\s]([0-9-\s]+)$/ui", $sVal) ) ) {
            return true;
        } else {
            return false;
        }
    }

    static public function email( $sVal, $arParam=array()) 
    {
        if(preg_match('/^[+.\w-_]+@([\w-]+\.)+[a-zA-Z]{2,4}$/i', $sVal)) {
            return true;
        } else {
            return false;
        }
    }

    static public function url( $sVal, $arParam=array()) 
    {
        if(preg_match('/^(https?:\/\/)?([\w-]+\.)+[a-zA-Z]{2,4}\/?$/i', $sVal)) {
            return true;
        } else {
            return false;
        }
    }

    static public function length( $sVal, $arParam=array()) 
    {
        $bResult = true;
        if(empty($arParam['code']) ) {
            if(!empty($sVal) && isset($arParam['min']) && strlen($sVal) < $arParam['min'] )
                $bResult = false;
            if(!empty($sVal) && isset($arParam['max']) && strlen($sVal) > $arParam['max'] )
                $bResult = false;
        }
        else
        {
            if(!empty($sVal) && isset($arParam['min']) && mb_strlen($sVal, $arParam['code']) < $arParam['min'] )
                $bResult = false;
            if(!empty($sVal) && isset($arParam['max']) && mb_strlen($sVal, $arParam['code']) > $arParam['max'] )
                $bResult = false;
        }
        return $bResult;
    }

    public static function name( $sVal, $arParam=array())
    {
        if
        ($sVal > ''
            && (!preg_match("/^([A-Za-zА-Яа-яЁё\s]{1,}((\-)?[A-Za-zА-Яа-яЁё\s](\')?){0,})*$/ui", $sVal) )
        ) {
            return false;
        }

        return true;
    }

    public static function regexp( $sVal, $arParam=array())
    {
        if
        ($sVal > ''
            && ( !preg_match("/".$arParam['regExp']."/ui", $sVal) )
        ) {
            return false;
        }

        return true;
    }

    /**
     * Метод определяет корректен ли переданный ОГРН
     * @param string $sVal     - строка ОГРН
     * @param array  $arParams - параметры
     * @return boolean
     */
    public static function ogrn( $sVal, $arParams )
    {
        if(!$sVal ) {
            return true;
        }

        $sInn = (string) trim($sVal);

        if(!ctype_digit((string) $sInn) ) {
            return false;
        }

        if(strlen($sVal) == 13 ) {
            $sCheck = substr($sVal, 12);
            $sTmp = substr($sVal, 0, 12);
            $sCheckReal = $sTmp - floor($sTmp / 11) * 11;
        }
        elseif(strlen($sVal) == 15 ) {
            $sCheck = substr($sVal, 14);
            $sTmp = substr($sVal, 0, 14);
            $sCheckReal = $sTmp - floor($sTmp / 13) * 13;
        }
        else
        {
            return false;
        }

        if($sCheckReal == 10) {
            $sCheckReal = 0;
        }

        return ( $sCheckReal == $sCheck );

    }


    /**
     * Метод определяет корректен ли переданный ИНН
     * @param string $sVal     - строка ИНН
     * @param array  $arParams - параметры
     * @return boolean
     */
    public static function inn( $sVal, $arParams )
    {
        if(!$sVal ) {
            return true;
        }

        $sInn = ( string ) trim($sVal);

        if(!preg_match('/^\d*$/', $sInn) ) {
            return false;
        }

        if(strlen($sInn) == 10 ) {
            $iCheckDigits = (2 * $sInn{0} + 4 * $sInn{1} + 10 * $sInn{2} + 3 * $sInn{3} + 5 * $sInn{4}
                        + 9 * $sInn{5} + 4 * $sInn{6} + 6 * $sInn{7} + 8 * $sInn{8}) % 11;

            if($iCheckDigits == 10 ) {
                $iCheckDigits = 0;
            }

            if($iCheckDigits == $sInn{9} ) {
                return true;
            }
            else
            {
                return false;
            }
        }
        elseif(strlen($sInn) == 12 ) {
            $iCheckDigits = (7 * $sInn{0} + 2 * $sInn{1} + 4 * $sInn{2} + 10 * $sInn{3} + 3 * $sInn{4}
                        + 5 * $sInn{5} + 9 * $sInn{6} + 4 * $sInn{7} + 6 * $sInn{8} + 8 * $sInn{9}) % 11;

            if($iCheckDigits == 10 ) {
                $iCheckDigits = 0;
            }

            $iCheckDigits2 = (3 * $sInn{0} + 7 * $sInn{1} + 2 * $sInn{2} + 4 * $sInn{3} + 10 * $sInn{4}
                        + 3 * $sInn{5} + 5 * $sInn{6} + 9 * $sInn{7} + 4 * $sInn{8} + 6 * $sInn{9} + 8 * $sInn{10}) % 11;

            if($iCheckDigits2 == 10 ) {
                $iCheckDigits2     = 0;
            }

            if($iCheckDigits == $sInn{10} && $iCheckDigits2 == $sInn{11} ) {
                return true;
            }
            else
            {
                return false;
            }
        }

        return false;
    }



}

