<?php
/**
 * Блок удаление <:classname:>
 * в iDeleteCnt - записывается количество удаленных элементов или -1, если при удалении возникли ошибки
 */

$arParams = $this->getParams();
if (isset($arParams['arFilter'])) {
    $arParams['sModel'] = '<:classname:>';
    $arParams['iPageSize'] = 100;
} elseif (isset($_POST['<:classname:>']['deleteIds']) && is_array($_POST['<:classname:>']['deleteIds'])) {
    $arParams = array(
        'sModel' => '<:classname:>',
        'iPageSize' => 100,
        'arFilter' => array(
            '<:id_name:>' => array('in' => $_POST['<:classname:>']['deleteIds'])
        )
    );
}

if (! empty($arParams['arFilter']) && ! empty($arParams['sModel'])) {
    $this->arItems = \IslandFuture\Sfw\Data\Storages::getAll($arParams);
}

$iCnt = 0;
if (sizeof($this->arItems) > 0) {
    \IslandFuture\Sfw\Data\Storages::one()->begin();
    foreach ($this->arItems as $oItem) {
        if ($oItem->delete()) {
            $iCnt++;
        } else {
            $iCnt = 0;
            break;
        }
    }
    $this->arItems = null;
    if ($iCnt == 0) {
        $iCnt = -1;
        \IslandFuture\Sfw\Data\Storages::one()->rollback();
    } else {
        \IslandFuture\Sfw\Data\Storages::one()->commit();
    }
}
$this->arBuffered[$this->sBlockName.':result'] = $iCnt;