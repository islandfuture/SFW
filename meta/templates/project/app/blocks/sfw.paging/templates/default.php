<nav <?=$this->sClass > '' ? 'class="'.$this->sClass.'"': ''?>>
  <ul class="pagination">
    <? for($iPage=0; $iPage < $this->iTotalPage; $iPage++): ?>
        <? if($iPage == $this->iCurrent-1):?>
            <li class="active"><a href="<?=$this->sUrl.$this->sGlue.'p='.($iPage+1)?>"><?=($iPage+1)?> <span class="sr-only">(current)</span></a></li>
        <? else: ?>
            <li><a href="<?=$this->sUrl.$this->sGlue.'p='.($iPage+1)?>"><?=($iPage+1)?></a></li>
        <? endif; ?>
    <? endfor; ?>
  </ul>
</nav