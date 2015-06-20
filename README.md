# SFW - Simple FrameWork on PHP

Простой фреймворк для быстрой разработки интернет-проектов.

## Установка

В директорию, где планируете размещать проект, размещаете файл `composer.json`:

```json
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/islandfuture/SFW.git"
        }
    ],
    "require": {
        "islandfuture/sfw": "dev-master"
    }
}
```
Запускаете установку:

```
php composer.phar install
```

Будут созданы директории

```
vendor/island-future
vendor/composer
```

Выполнить из коммандной строки:

```
php vendor/island-future/sfw/bin/install.php
```

## Настройка

Зайдите в директорию `config/config.php` и напишите свои данные для соединения с БД и другие параметры

Настройте nginx так, чтобы все запросы к скриптам шли к файлу `public/index.php`


## Как работает первая версия

### Структура каталогов проекта

```
/app/
/app/layout/main.php - скрипт шаблона страницы (общая для всех скриптов обертка)
/app/pages/ - каталог страниц
/app/blocks/ - каталог блоков/компонентов страницы
/app/meta/ - каталог мета-данных, для генерации кода
/config/config.php - файл конфига
/vendor/ - каталог внешних модулей
/vendor/islandfuture/sfw/ - ядро системы
/public/ - каталог публичной части (корень веб-сайта)
/public/js/ - каталог для javascript
```
