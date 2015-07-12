<?php
/***
 * блок отображения страниц (используется когда записей больше чем на одну страницу)
 *
 */

if( empty($paging_size) ) {
    $paging_size = 20;
}

if( empty($paging_current) ) {
    $paging_current = 1; // текущая страница
}

if( empty($paging_total_rec) ) {
    $paging_total_rec = $paging_size; // общее количество записей
}

if( empty($paging_url) ) {
    $paging_url = $_SERVER['REQUEST_URI'];
}

if( strpos($paging_url, '?') === false ) {
    $glue = '?';
} else {
    $glue = '&';
}

$newurl = preg_replace('/&p=\d{0,5}/','', $paging_url);
if( $newurl == NULL ){
    $newurl = preg_replace('/?p=\d{0,5}/','', $paging_url);
}

if( $newurl !== NULL ){
    $paging_url = $newurl;
}


$paging_total_page = ceil($paging_total_rec / $paging_size);

echo '<ul class="paging">';
for($page = 0; $page < $paging_total_page; $page++ ) {
    if( $page == $paging_current-1 ) {
        echo '<li class="current"><a href="'.$paging_url.$glue.'p='.($page+1).'">'.($page+1).'</a></li>';
    } else {
        echo '<li><a href="'.$paging_url.$glue.'p='.($page+1).'">'.($page+1).'</a></li>';
    }
}//end for
echo '</ul> &nbsp;';

?>