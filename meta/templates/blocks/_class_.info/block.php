<?php
/**
 * Блок отвечает за вывод формы сущности Posts
 * @example echo $this->block('posts.info',array('id'=>?, 'key'=>?,...), array('template'=>'form','buffered'=>true|false) );
 */
if ($this->id == '' && $this->key > '') {
    if (! empty($_REQUEST[$this->key])) {
        $this->id = $_REQUEST[$this->key];
    } elseif (! empty($_REQUEST['<:classname:>'][$this->key])) {
        $this->id = $_REQUEST['<:classname:>'][$this->key];
    }
}

if($this->id > 0) {
    $this->oModel = \IslandFuture\Sfw\Data\Storages::getOne(array(
            'sModel' => '<:classname:>',
            'arFilter' => array(
                '<:id_name:>' => array('=' => $this->id )
            )
        )
    );
} else {
    $this->oModel = \IslandFuture\Sfw\Data\Storages::model('<:classname:>');
}

if ($this->modeedit == true) {
    if ($this->oModel-><:id_name:> > '') {
        \IslandFuture\Sfw\Application::one()->setTitle('Форма редактирования');
    } else {
        \IslandFuture\Sfw\Application::one()->setTitle('Форма создания');
    }

}
if($this->modeedit == true && isset($_REQUEST['<:classname:>'])) {
    $this->oModel->attributes($_REQUEST['<:classname:>']);
    
    if (! $this->oModel->save()) {
        $this->oModel->arErrors = 'Ошибка!';
    }
}
