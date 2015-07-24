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

if($this->iSize == 0) {
    $this->iSize = 20;
}

if($this->iCurrent==0) {
    $this->iCurrent = 1; // текущая страница
}

if($this->iTotal==0) {
    $this->iTotal = $this->iSize; // общее количество записей
}

if($this->sUrl=='') {
    $this->sUrl = $_SERVER['REQUEST_URI'];
}

$sNewUrl = preg_replace('/&p=\d{0,5}/', '', $this->sUrl);
if($sNewUrl == null || $sNewUrl == $this->sUrl) {
    $sNewUrl = preg_replace('/\?p=\d{0,5}/', '', $this->sUrl);
}

if($sNewUrl !== null ) {
    $this->sUrl = $sNewUrl;
}

if(strpos($this->sUrl, '?') === false ) {
    $this->sGlue = '?';
} else {
    $this->sGlue = '&';
}

$this->iTotalPage = ceil($this->iTotal / $this->iSize);