<?php
include '../classes/Tools.php';

$sProjectPath = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
$sTemplatePath = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'meta'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'project'.DIRECTORY_SEPARATOR);

$arCopyFiles = \IslandFuture\Sfw\Tools::getListFilesEx($sTemplatePath,0,$sProjectPath);
echo "Created files:\n";
foreach ($arCopyFiles as $sFile) {
    echo $sFile."\n";
}

echo "Installed - OK\n";