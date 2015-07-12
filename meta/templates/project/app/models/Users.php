<?php
include_once "UsersAuto.php";
/**
 * здесь Вы можете добавлять свои функции и обработчики для модели Microblogs
 */
class Users extends UsersAuto
{    
    public function genRandomPassword($length = 8)
    {
        $salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $len = strlen($salt);
        $makepass = '';
    
        for ($i = 0; $i < $length; $i ++) {
            $makepass .= $salt[mt_rand(0, $len -1)];
        }
        return $makepass;
    }
    
    public function canEdit() {
        return ( $this->id == \IslandFuture\Sfw\ActiveUser::one()->id );
    }
    
    // функция возвращает timestamp даты создания записи
    public function convertDbToUser($ts, $sUserTimeZone="Etc/GMT-3")
    {
        $date = new DateTime($ts, new DateTimeZone(date('e')));
        $date->setTimezone(new DateTimeZone($sUserTimeZone));

        $date->format('Y-m-d H:i:sP');
        
        return $date->getTimestamp();
    }//end function
	
	public function getName()
	{
		if($this->sName > '') {
			return $this->sName;
		} else {
			$arTmp = explode('@', $this->sEmail);
			return $arTmp[0];
		}
	}

}