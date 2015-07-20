<?php
/**
 * Комманда предназначена для генерации набора скриптов для быстрого запуска проекта
 *
 * @link    https://github.com/islandfuture/SFW
 * @author  Michael Akimov <michael@island-future.ru>
 * @version GIT: $Id$
 *
 * @example:
 *      на основе мета модели создаем набор классов для использования в проекте, блоков и страниц
 *      console.php crud model=Posts
 */

$sPathSfw = \IslandFuture\Sfw\Application::one()->PATH_SFW;
$sPathApp = \IslandFuture\Sfw\Application::one()->PATH_APP;
$sPathMeta = $sPathSfw.'meta'.DIRECTORY_SEPARATOR;

if (empty($_GET['model'])) {
    echo "Usage: php consple.php crud model=MODEL\n";
    echo "Where MODEL is name meta models\n";
    return false;
}

$arTasks = array('Table','Model','Blocks','Pages');
foreach($arTasks as $sCommand) {
    $sUCommand = ucfirst($sCommand);
    
    try {
        $is_exists = class_exists($sUCommand.'Generator', false);
    } catch (Exception $e) {
        $is_exists = false;
    }//end try catch
    
    // если класса не существует, то открываем файл с классом
    if (! $is_exists) {
        if (file_exists($sPathSfw."classes".DIRECTORY_SEPARATOR.'Meta'.DIRECTORY_SEPARATOR.$sUCommand."Generator.php")) {
            include_once ($sPathSfw."classes".DIRECTORY_SEPARATOR.'Meta'.DIRECTORY_SEPARATOR.$sUCommand."Generator.php");
            $sUCommand = "\\IslandFuture\\Sfw\\Meta\\".$sUCommand.'Generator';
        } else {
            include_once $sPathApp.'meta'.DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$sUCommand."Generator.php";
            $sUCommand = $sUCommand.'Generator';
        }
    }//end if
    
    $oGenerator = new $sUCommand();
    echo "Run generator: $sCommand\n";
    $oGenerator->run();
}

if (! file_exists($sPathApp.'meta'.DIRECTORY_SEPARATOR.'gen')) {
    mkdir($sPathApp.'meta'.DIRECTORY_SEPARATOR.'gen');
}

if (! file_exists($sPathApp.'meta'.DIRECTORY_SEPARATOR.'gen'.DIRECTORY_SEPARATOR.$_GET['model'])) {
    mkdir($sPathApp.'meta'.DIRECTORY_SEPARATOR.'gen'.DIRECTORY_SEPARATOR.$_GET['model']);
}

$sPathGen = $sPathApp.'meta'.DIRECTORY_SEPARATOR.'gen'.DIRECTORY_SEPARATOR.$_GET['model'].DIRECTORY_SEPARATOR;

$arFiles =  \IslandFuture\Sfw\Tools::getListFilesEx($sPathGen.'models'.DIRECTORY_SEPARATOR, 0, $sPathApp.'models'.DIRECTORY_SEPARATOR, array());
foreach ($arFiles as $sFrom => $sTo) {
    if(strpos($sTo,$_GET['model'].'.php') > 0 && file_exists($sTo)) {
        
        echo "File [".$_GET['model'].".php] already exists, so save generic version to [$sFrom]\n";
        continue;
    }
    echo "Create file [$sTo]\n";
    copy($sFrom, $sTo);
}

$arFiles =  \IslandFuture\Sfw\Tools::getListFilesEx($sPathGen.'blocks'.DIRECTORY_SEPARATOR, 0, $sPathApp.'blocks'.DIRECTORY_SEPARATOR, array());
foreach ($arFiles as $sFrom => $sTo) {
    echo "Create file [$sTo]\n";
    copy($sFrom, $sTo);
}

$arFiles =  \IslandFuture\Sfw\Tools::getListFilesEx($sPathGen.'pages'.DIRECTORY_SEPARATOR, 0, $sPathApp.'pages'.DIRECTORY_SEPARATOR.'panel'.DIRECTORY_SEPARATOR, array());
foreach ($arFiles as $sFrom => $sTo) {
    echo "Create file [$sTo]\n";
    copy($sFrom, $sTo);
}

echo "Create table for model ".$_GET['model']."\n";
$sSQL = file_get_contents($sPathGen.'sql'.DIRECTORY_SEPARATOR.'create.sql');
\IslandFuture\Sfw\Data\Storages::one()->execute($sSQL);
echo "\n";
