<?php
$this->sLayout = 'main';
$this->setTitle('Список объектов <:titlename:>');

if (isset($_POST['<:classname:>']['deleteIds']) && is_array($_POST['<:classname:>']['deleteIds'])) {
    $oBlock = $this->block('<:classlower:>.del', array(), array());
    if ($oBlock->arBuffered['<:classlower:>.del:result'] == -1) {
?>
        <p class="bg-danger">
            При удалении возникла ошибка.
        </p>
<?php
    } else {
?>
        <p class="bg-info">
            Элементов удалено - <?=$oBlock->arBuffered['<:classlower:>.del:result']?>.
        </p>
<?php
    }
    
}

$this->block(
    '<:classlower:>.list', array(),
    array(
        'template' => 'admin'
    )
);

