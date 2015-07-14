<?php
$this->sLayout = 'main';
$this->setTitle('Список объектов <:titlename:>');

if (isset($_POST['<:classname:>']['deleteIds']) && is_array($_POST['<:classname:>']['deleteIds'])) {
    $oBlock = $this->block('<:classlower:>.del', array(), array());
    if ($oBlock->iDeleteCnt == -1) {
?>
        <p class="bg-danger">
            При удалении возникла ошибка.
        </p>
<?php
    } else {
?>
        <p class="bg-info">
            Элементов удалено - <?=$oBlock->iDeleteCnt?>.
        </p>
<?php
    }
    
}

$this->block('<:classlower:>.list', array(),
    array(
        'template' => 'list'
    )
);

