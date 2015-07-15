<?php

try {
    if (empty($_POST['login']) && empty($_POST['password']) ) {
        return;
    }

    if (empty($_POST['login']) || empty($_POST['password']) ) {
        throw new Exception('не указаны почта или пароль.');
    }

    $this->login = $_POST['login'];
    
    if (! \IslandFuture\Sfw\Data\Validator::email($_POST['login'])) {
        throw new Exception('Ошибка в электронной почте');
    }
    
    $oUser = \IslandFuture\Sfw\Data\Storages::getOne(
        array(
            'sModel' => 'Users',
            'arFilter' => array(
                'sEmail' => array('like' => $this->login)
            )
        )
    );

    if (! $oUser ) {
        if ($this->isAdminSection
            && \IslandFuture\Sfw\Data\Storages::getCountAll(array('sModel' => 'Users')) == 0
        ) {
            $oUser = new Users();
            $oUser->isNewRecord = true;
            $oUser->sEmail = $_POST['login'];
            $oUser->iStatusId = '1';
            $oUser->sPasswd = \IslandFuture\Sfw\Tools::password_hash($_POST['password']);
            $oUser->save();
        } else {
            //@todo нужно логировать такие обращения, возможно идет подборка пароля
            throw new Exception('email|ошибка в почтовом адресе');
        }
    }

    if (! \IslandFuture\Sfw\Tools::password_verify($_POST['password'], $oUser->sPasswd)) {
        throw new Exception('Доступ запрещен');
    }

        
    /*
    if( $oUser->iStatusId != '1' ) {
        //@todo нужно логировать такие обращения, возможно идет регистрация ботов
        throw new Exception('Ваша учетная запись не активирована. Для активации воспользуйтесь ссылкой в присланном Вам письме.');
    }
    */

    $oUser->tLastLogin = date('Y-m-d H:i:s');
    $oUser->tLastActivity = $oUser->tLastLogin;
    if(! empty($_SERVER['REMOTE_ADDR'])) {
        $oUser->sLastIp = $_SERVER['REMOTE_ADDR'];
    }
    $oUser->save();

    \IslandFuture\Sfw\ActiveUser::one()->oCurrentUser = $oUser;
    \IslandFuture\Sfw\ActiveUser::one()->login($oUser->__getFields());
    
    $this->item = $oUsers;

    if(!empty($_REQUEST['remeber']) ) {
        /* установим на 2 недели */
        setcookie("session_lifetime", $oUser->id.'_'.$oUser->sPasswd, time()+1209600);
    }

} catch (Exception $e) {
    echo $e->getMessage();
    return false;
}

return true;
