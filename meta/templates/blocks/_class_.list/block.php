<?php
/**
 * Шаблон оформления списка сущностей Posts
 */

$arParams = $this->getParams();
$session = \IslandFuture\Sfw\ActiveUser::one();

/**
 * блок обработки фильтра (внешнего)
 */
if(
    ! empty($_REQUEST['<:classname:>']['clear'])
    && $_REQUEST['<:classname:>']['clear'] == '1'
) {
    $arFilter = array();
} elseif (
    isset($_GET['<:classname:>'])
    && isset($_GET['<:classname:>']['filter'])
    && isset($_GET['<:classname:>']['filter']['fields'])
) {
    $arFilters = $_GET['<:classname:>']['filter'];
    
    $arFilter = array();

    foreach( $arFilters['fields'] as $i => $field ) {
        if( isset($arFilters['value1'][$i]) && $arFilters['value1'][$i]!='') {
            if( $arFilters['op'][$i] == '=' ){
                $arFilter[ $field ] = $arFilters['value1'][$i];
            } elseif(  $arFilters['op'][$i] == '<' ){
                $arFilter[ $field ] = array('<' => $arFilters['value1'][$i] );
            } elseif(  $arFilters['op'][$i] == 'in' ){
                $arFilter[ $field ] = explode(',', $arFilters['value1'][$i]);
            } elseif(  $arFilters['op'][$i] == 'between' ){
                $arFilter[ $field ] = array('from' => $arFilters['value1'][$i], 'to' => $arFilters['value2'][$i] );
            } elseif(  $arFilters['op'][$i] == 'like' ){
                $arFilter[ $field ] = array('like' => $arFilters['value1'][$i] );
            }
        }//end if
    }//end foreach
} elseif ( isset($_GET['<:classname:>'])  ) {
    $arFilter = $_GET['<:classname:>'];
} else {
    $tmp = $session->ar<:classname:>Filters;
    if( is_array($tmp) ){
        $arFilter = $tmp;
    } else {
        $arFilter = array();
    }
}

if (empty($arFilter['p'])) {
    if (isset($_GET['p'])) {
        $this->iPage = (int)$_GET['p'];
    } else {
        $this->iPage = 0;
    }
} else {
    $this->iPage = (int)$arFilter['p'];
    unset($arFilter['p']);
}

if (empty($arFilter['size'])) {
    if (isset($_GET['size'])) {
        $this->iPageSize = (int)$_GET['size'];
    } else {
        $this->iPageSize = 0;
    }
} else {
    $this->iPageSize = (int)$arFilter['size'];
    unset($arFilter['size']);
}


if (! $this->arFilter || ! sizeof($this->arFilter) == 0) {
    $this->arFilter = array();
}

$this->arFilter = array_merge($this->arFilter, $arFilter);
$this->sModel = '<:classname:>';

$this->sFilterUrl = '/'.$this->sCurPages;
if( substr( $this->sFilterUrl, -5) == 'index' ) {
    $this->sFilterUrl = substr( $this->sFilterUrl, 0, -6);
}
$this->sFilterUrl .= '/';
$this->iCntModels = \IslandFuture\Sfw\Data\Storages::getCountAll($this->getParams());

// если размер известен, то запоминаем его, если нет, то ставим значение по умолчанию
if( isset($this->sort) ){
    $sort = $this->sort;
} elseif( isset($_REQUEST['sort']) ) {
    $sort = $_REQUEST['sort'];
    if( isset($_REQUEST['dir']) ){
        $sort = array($sort => $_REQUEST['dir']);
    } else {
        $sort = array($sort => 'asc');
    }
} else {
    $tmp = $session-><:classname:>_sort;
    if( is_array($tmp) ){
        $sort = $tmp;
    } else {
        $sort = array('id'=>'desc');
    }
}

$session-><:classname:>_sort = $sort;

$orderby = '';
foreach( $sort as $name => $dir ){
    $orderby .= $name.' '.$dir.', ';
}//end foreach
$orderby .= ' id asc';

$this->sort = $sort;

$this->arItems = \IslandFuture\Sfw\Data\Storages::getAll($this->getParams());

// настраиваем параметр для передачи в шаблон - отображать пейджинг или нет
if($this->iPageSize == 0 ){
    $this->paging = false;
} else {
    $this->paging = ($this->paging===false ? false : true );
}

