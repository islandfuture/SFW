<?php
$this->sLayout = 'main';

$this->block('<:classlower:>.info', array(
        'modeedit' => true,
        'key' => '<:id_name:>'
    ),
    array(
        'template' => 'form'
    )
);
