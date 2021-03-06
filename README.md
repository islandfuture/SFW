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

Если будуте рассылать почту и юзаете bootstrap, то лучше используйте такой конфиг:
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
        "phpmailer/phpmailer": "5.2.*"
        "twbs/bootstrap": "3.3.*"
    }
}
```
Запускаете установку:

```
php composer.phar install
```

Будут созданы директории

```
vendor/islandfuture
vendor/composer
```

Выполнить из коммандной строки:

```
php vendor/islandfuture/sfw/bin/install.php
```

## Настройка

### БД (на пример MySQL)

Скорее всего, данные будете хранить в БД. Создать базу данных можно из консольной программы или через визульную программу.
Запустить в консольном режиме клиента mysql и выполнить команду:

```
CREATE DATABASE `имя БД` CHARACTER SET utf8 COLLATE utf8_general_ci;
```

Если у Вас стоит HeidiSQL или настроен phpMyAdmin - то выберите пункт "Создать БД" и заполните предлагаемы поля, а потом сохраните сделанный выбор. Не забудьте указать кодировку UTF8.

### Веб-сервер

Настройте nginx так, чтобы все запросы к скриптам шли к файлу `public/index.php`, а также document_root указывал на каталог <полный путь до проекта>/public

Для настройки апача, достаточно указать DocumentRoot в <полный путь до проекта>/public и разрешить обработку .htaccess

### Настройки самого проекта

Зайдите в директорию `config/config.php` и напишите свои данные для соединения с БД и другие параметры



## Как работает первая версия

### Структура каталогов проекта

```
/app/
/app/layout/main.php - скрипт шаблона страницы (общая для всех скриптов обертка)
/app/pages/ - каталог страниц
/app/blocks/ - каталог блоков/компонентов страницы
/app/meta/ - каталог мета-данных, для генерации кода
/config/config.php - файл конфига
/config/route.php - файл для ЧПУ
/vendor/ - каталог внешних модулей
/vendor/islandfuture/sfw/ - ядро системы
/public/ - каталог публичной части (корень веб-сайта)
/public/js/ - каталог для javascript
```
