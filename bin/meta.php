<?php
/**
 * $Id: $
 * $Log: $
 *
 * Комманда предназначена для генерации различных скриптов на основе метамодели
 * @example:
 *      на основе мета модели создаем набор классов для использования в проекте
 *      console.php meta task=generate::model model=blog
 *
 *      на основе мета-модели генерируем набор скриптов (блоков) для использования на страницах проекта
 *      console.php meta task=generate::blocks model=blog
 *
 *      на основе таблицы создаем файл метамодели
 *      console.php meta task=convert::fromdb model=Attachments
 */

$sPathSfw = \IslandFuture\Sfw\Application::one()->PATH_SFW;
$sPathApp = \IslandFuture\Sfw\Application::one()->PATH_APP;

if (empty($_GET['task'])) {
    echo "Error: You not select task\r\n";
    $sPath = $sPathSfw.'meta'.DIRECTORY_SEPARATOR.'tasks'.DIRECTORY_SEPARATOR;
    if (! file_exists($sPath)) {
        echo "Error: Task not found in directory meta/tasks/ \r\n";
        return false;
    }

    $oDir = dir($sPath);
    echo "Select task: ";
    while (false !== ($sFile = $oDir->read())) {
        if ('.php' == substr($sFile, -4, 4)) {
            echo "\t".substr($sFile, -4)."\n";
        }
    }
    $oDir->close();

    return false;
}

$sPathMeta = $sPathSfw.'meta'.DIRECTORY_SEPARATOR;

$sCommand = $_GET['task'];
$sUCommand = ucfirst($sCommand);

// если существует нужный генератор или существует файл, где он может лежать, то 
//     class_exists($ucommand.'Generator')

$isExist = file_exists($sPathSfw."classes".DIRECTORY_SEPARATOR.'Meta'.DIRECTORY_SEPARATOR.$sUCommand."Generator.php")
    || file_exists($sPathApp.'meta'.DIRECTORY_SEPARATOR."tasks".DIRECTORY_SEPARATOR.$sUCommand."Generator.php");

if ($isExist) {
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
    
} else {
    echo "Command $sCommand - not found\n";

}
