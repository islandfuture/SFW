<?php if ($this->oModel-><:id_name:> > '') : ?>
<div class="panel panel-primary">
    <div class="panel-heading">Просмотр записи [<:classname:>: <?=$this->oModel-><:id_name:>?>]</div>
    <div class="panel-body">
        <:text_fields:>
        <p>
            <a href="<:websubdir:>" class="text-info">Вернуться к списку</a>
        </p>
    </div>
</div>
<?php else: ?>
<p class="bg-warning">Запись не найдена</p>
<?php endif; ?>