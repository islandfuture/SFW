<?php
/***
 * блок отображения пейджинга страниц (используется когда записей больше чем на одну страницу)
 * 
 * @param int iSize количество записей на страницу (по умолчанию 20)
 * @param int iTotal сколько всего записей
 * @param int iCurrent текущая страница
 * @param string sUrl урл куда будет добавляться номер страницы
 * @param string sClass название специального CSS класса для оформления блока с пагинацией
 */

if(empty($this->iSize) ) {
    $this->iSize = 20;
}

if(empty($this->iCurrent) ) {
    $this->iCurrent = 1; // текущая страница
}

if(empty($this->iTotal) ) {
    $this->iTotal = $this->iSize; // общее количество записей
}

if(empty($this->sUrl) ) {
    $this->sUrl = $_SERVER['REQUEST_URI'];
}

if(strpos($this->sUrl, '?') === false ) {
    $this->sGlue = '?';
} else {
    $this->sGlue = '&';
}

$sNewUrl = preg_replace('/&p=\d{0,5}/', '', $this->sUrl);
if($sNewUrl == null ) {
    $sNewUrl = preg_replace('/?p=\d{0,5}/', '', $this->sUrl);
}

if($sNewUrl !== null ) {
    $this->sUrl = $sNewUrl;
}


$$this->iTotalPage = ceil($this->iTotal / $this->iSize);

