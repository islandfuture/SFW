<?php if ($this->oModel->id > 0): ?>
<h1>Редактирование заметки</h1>
<?php else: ?>
<h1>Создание заметки</h1>
<?php endif; ?>
<div class="sfw_form">
<form action="/admin/<:classlower:>/edit/" method="post" class="form-horizontal">
    <input type="hidden" type="text" name="<:classname:>[<:id_default:>]" value="<?=$this->oModel-><:id_default:>?>" />

<:clear_fields:>

    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-7">
            <input type="submit" class="btn btn-primary" value="Сохранить" />
            &nbsp;
            <a href="../" class=" text-danger">Отмена</a>
            &nbsp;&nbsp;&nbsp;
            <a href="/admin/<:classlower:>/del/?id=<?=$this->oModel-><:id_default:>?>" class="text-danger" onclick="confirm('Вы действительно хотите удалить?');">Удалить запись <?=$this->oModel-><:id_default:>?></a>

        </div>
    </div>
</form>
</div>
