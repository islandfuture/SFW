<?php

if (\IslandFuture\Sfw\ActiveUser::one()->id == '') {
    $oUsersLoginBlock = $this->block('users.login', array(
            'isAdminSection' => true
        ),
        array(
            'template' => 'system',
            'buffered' => true
        )
    );
}

if (\IslandFuture\Sfw\ActiveUser::one()->id == '') {
    $this->setTitle('Форма авторизации');
?>
<div class="row">
    <div class="col-md-offset-3 col-md-6">
    <?=$oUsersLoginBlock->arBuffered['users.login:html']?>
    </div>
</div>

<?php
    return;
} else {
    $this->setTitle('Панель администратора');

}
