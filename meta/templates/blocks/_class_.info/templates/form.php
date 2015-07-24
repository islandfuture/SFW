<?php if ($this->oModel->id > 0) : ?>
<h1>Редактирование заметки</h1>
<?php else: ?>
<h1>Создание заметки</h1>
<?php endif; ?>
<div class="sfw_form">
<form action="<:websubdir:>edit/" method="post" class="form-horizontal">
    <input type="hidden" type="text" name="<:classname:>[<:id_name:>]" value="<?=$this->oModel-><:id_name:>?>" />

<:clear_fields:>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-7">
            <input type="submit" class="btn btn-primary" value="Сохранить" />
            &nbsp;
            <a href="<:websubdir:>" class=" text-danger">Отмена</a>
        </div>
    </div>
</form>
        <div class="col-sm-offset-2 col-sm-7 text-right">
            <form method="post" action="<:websubdir:>">
                <input type="hidden" name="<:classname:>[deleteIds][]" value="<?=$this->oModel-><:id_name:>?>" />
                <input type="submit" role="button" class="btn btn-xs btn-danger" value="Удалить" onclick="confirm('Вы действительно хотите удалить?');" />
            </form>
        </div>

</div>
