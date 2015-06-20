<?php
namespace IslandFuture\Sfw;

/**
 * класс - сборник полезных методов и функций
 */
class Tools
{

    /**
     * Возвращает возраст, по дате рождения (все что младше 10 и старше 100 лет считает несуществующим)
     *
     */
    public static function howOld($date, $is_null = '')
    {
        $date = strtotime($date);
        $arDate = explode(',', date('Y,m,d,H,i,s', $date));
        $arDateNow = explode(',', date('Y,m,d,H,i,s'));
        
        $minusYear = 0;
        if ($arDate[1] > $arDateNow[1]) { /* month */
            $minusYear = 1;
        } elseif ($arDate[2] > $arDateNow[2]) {
            $minusYear = 1;
        }
        
        $age = $arDateNow[0] - $arDate[0] - $minusYear;
        
        if ($age < 10 || $age > 100) {
            $age = $is_null;
        }

        return $age;
    }

    /**
     * Возвращает, сколько прошло времени между двух дат
     * @var timestamp раняя дата
     * @var timestamp поздняя дата
     * @var boolean флаг - показывать время меньше минуты
     *
     * @return string
     */
    public static function distanceOfTimeInWords($fromTime, $toTime = 0, $showLessThanAMinute = false)
    {
        if ($toTime == 0) {
            $toTime = time();
        }
        
        $distanceInSeconds = round(abs($toTime - $fromTime));
        $distanceInMinutes = round($distanceInSeconds / 60);
       
        if ($distanceInMinutes <= 1) {
            if (!$showLessThanAMinute) {
                return ($distanceInMinutes == 0) ? 'меньше минуты' : '1 минута';
            } else {
                if ($distanceInSeconds < 5) {
                    return 'меньше 5 секунд';
                }
                if ($distanceInSeconds < 10) {
                    return 'меньше 10 секунд';
                }
                if ($distanceInSeconds < 20) {
                    return 'меньше 20 секунд';
                }
                if ($distanceInSeconds < 40) {
                    return 'примерно полминуты';
                }
                if ($distanceInSeconds < 60) {
                    return 'меньше минуты';
                }
               
                return '1 минута';
            }
        }

        if ($distanceInMinutes < 45) {
            $sDigits = self::humanDigits($distanceInMinutes, array('минуту', 'минуты', 'минут'));
            
            return $distanceInMinutes . ' ' . $sDigits;
        }
        
        if ($distanceInMinutes < 90) {
            return 'около 1 часа';
        }
        
        if ($distanceInMinutes < 1440) {
            $time = round(floatval($distanceInMinutes) / 60.0);
            return 'около ' . $time . ' '.self::humanDigits($time, array('часа', 'часов','часов'));
        }

        if ($distanceInMinutes < 10080) { //меньше недели
            $week = array('Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота');
            return $week[ date('w', $fromTime) ].' в '.date('H:i', $fromTime);
        }
        
        if ($distanceInMinutes < 1051199) {
            return date('d', $fromTime) . ' ' . self::getMonthHuman(date('m', $fromTime), 'r') . ' в ' . date('H:i', $fromTime);
        }
        
        return date('d', $fromTime) . ' ' . self::getMonthHuman(date('m', $fromTime), 'r') . ' ' . date('Y', $fromTime) . ' в '. date('H:i');
    }//end function

    public static function getMonthHuman($m, $padeg = 'i')
    {
        $aMonth = array(
            'i' => array(
                1 => 'Январь',
                2 => 'Февраль',
                3 => 'Март',
                4 => 'Апрель',
                5 => 'Май',
                6 => 'Июнь',
                7 => 'Июль',
                8 => 'Август',
                9 => 'Сентябрь',
                10 => 'Октябрь',
                11 => 'Ноябрь',
                12 => 'Декабрь'
            ),
            'r' => array(
                1 => 'января',
                2 => 'февраля',
                3 => 'марта',
                4 => 'апреля',
                5 => 'мая',
                6 => 'июня',
                7 => 'июля',
                8 => 'августа',
                9 => 'сентября',
                10 => 'октября',
                11 => 'ноября',
                12 => 'декабря'
            ),
            
        );
        
        return ( isset($aMonth[$padeg][(int)$m]) ? $aMonth[$padeg][(int)$m] : '' );
    }

    /**
     * Возвращает правильную строку в соответствии с цифрой
     *
     * @var integer цифра
     * @var array 3 варианта строки из которых и будет происходить выбор (например: год, года, лет)
     *
     * @return string
     */
    public static function humanDigits($iDigit, $arNames = array('год', 'года', 'лет'))
    {
        if ($iDigit == 0 || $iDigit == 100) {
            $iModule = 10;
        } else {
            $iModule = $iDigit % 100;
        }
        $sResultat = '';

        if ($iModule > 20) {
            $iModule = $iModule % 10;
            
            $iModule = ($iModule == 0) ? $iModule = 10 : $iModule;
        }

        $sResultat = ( $iModule <= 1 ?
                        $arNames[0] :
                        ( $iModule < 5 ?
                            $arNames[1] :
                            ( $iModule < 21 ?
                                $arNames[2] :
                                '???'
                            )
                        )
                    );
        
        return $sResultat;
    }

    /**
     * Возвращает дату в человеко-читаемом виде
     * @var string|timestamp дата в строковом формате или таймстамп
     *
     * @return string
     */
    public static function getHumanDate($date)
    {
        $arMonth = array(
            1 => 'января',
            2 => 'февраля',
            3 => 'марта',
            4 => 'апреля',
            5 => 'мая',
            6 => 'июня',
            7 => 'июля',
            8 => 'августа',
            9 => 'сентября',
            10 => 'октября',
            11 => 'ноября',
            12 => 'декабря'
            );
        
        if (strpos($date, '-') !== false) {
            $date = strtotime($date);
        }

        $day = date('d', $date);
        $month = date('m', $date);
        $year = date('Y', $date);
        if ($year == date('Y')) {
            $year='';
        }
            
        return (int)$day.' '.$arMonth[ (int)$month ].' '.$year.' в '.date('H:i', $date);
    }

    public static function password_hash($sPass, $sAlgo='', $arOptions=array())
    {
        if( !function_exists('password_hash') ) {
            function password_hash($sPass, $sAlgo='', $arOptions=array())
            {
                $sAlgo = '2y';
                if( empty($arOptions['cost']) ) {
                    $arOptions['cost'] = '10';
                } else {
                    if(strlen($arOptions['cost']) == 1) {
                        $arOptions['cost'] = '0'.$arOptions['cost'];
                    }
                }
               
                $salt = substr(strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.'),0,22);
                return crypt($sPass, '$'.$sAlgo.'$'.$arOptions['cost'].'$'.$salt.'$');
            }
        }
        
        return password_hash($sPass, $sAlgo, $arOptions);
    }

    public static function password_verify($sPass, $sHash)
    {
        if(! file_exists('password_verify')) {
            function password_verify($sPass, $sHash)
            {
                return (crypt($sPass, $sHash)==$sHash);
            }
        }
        
        return password_verify($sPass, $sHash);
    }

    public static function getListFilesEx($sPath, $iDepth, $sNewPath,$arVars=array())
    {
        $arResult = array();
        if (! file_exists($sNewPath)) {
            mkdir($sNewPath);
        }
        
        $oDir = dir($sPath);
        while (false !== ($sFile = $oDir->read())) {
            if ('.' == $sFile || '..' == $sFile) {
                continue;
            }
            
            if (is_dir($sPath.$sFile)) {
                $sNewFile = $sFile;
                foreach ($arVars as $key => $val) {
                    $sNewFile = str_replace($key,$val,$sFile);
                }
                $arTmp = self::getListFilesEx($sPath.$sFile.DIRECTORY_SEPARATOR,$iDepth+1,$sNewPath.$sNewFile.DIRECTORY_SEPARATOR, $arVars);
                $arResult = array_merge($arResult,$arTmp);
            } else {
                $arResult[ $sPath.$sFile ] = $sNewPath.$sFile;
            }

        }
        $oDir->close();
        return $arResult;
    }
}