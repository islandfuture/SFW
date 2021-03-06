<?php
require __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'Tools.php';

$sProjectPath = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR;
$sTemplatePath = realpath(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'meta'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'project'.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

$arCopyFiles = \IslandFuture\Sfw\Tools::getListFilesEx($sTemplatePath, 0, $sProjectPath);
echo "Created files:\n";
foreach ($arCopyFiles as $sFrom => $sTo) {
    echo $sTo."\n";
    copy($sFrom, $sTo);
}

echo "Installed - OK\n";
