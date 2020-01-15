#!/usr/bin/env php
<?php

require __DIR__ . '/../../vendor/autoload.php';

use PrestaShop\ModuleBuilder\BuildZIPArchiveCommandHandler;
use PrestaShopTests\Integration\FolderComparator;

$buildZIPArchiveCommandHandler = new BuildZIPArchiveCommandHandler(__DIR__ . '/workspace/');
$folderComparator = new FolderComparator();

$modulesToTest = [
    'blockreassurance' => [],
    'gsitemap' => [],
    'autoupgrade' => ['ignore-dependency-check' => true],
    'dashactivity' => [],
    'gamification' => [],
    'statsnewsletter' => [],
    'ps_mainmenu' => [],
    'productcomments' => [],
];
$workspaceID = 100;

function printErrorsList($moduleName, $list)
{
    echo "\033[31m";

    $message = sprintf(
        'Test failed for module %s, got differences between expected folder and workspace folder :',
        $moduleName
    );

    echo $message . PHP_EOL;

    foreach ($list as $item) {
        echo ' - ' . $item . PHP_EOL;
    }

    echo "\033[37m";
}

foreach ($modulesToTest as $moduleName => $config) {
    $workspaceID++;
    $moduleFolderpath = __DIR__ . '/module-samples/' . $moduleName;
    $expectedModuleFolderpath = __DIR__ . '/expected/' . $moduleName;
    $workspaceFolderpath = __DIR__ . '/workspace/' . $workspaceID;

    $buildZIPArchiveCommandHandler->createWorkspace($workspaceID);
    $buildZIPArchiveCommandHandler->copyModuleFolderIntoWorkspace($workspaceID, $moduleFolderpath);

    $skipDependyCheck = false;
    if (array_key_exists('ignore-dependency-check', $config) && $config['ignore-dependency-check'] === true) {
        $skipDependyCheck = true;
    }
    if ($skipDependyCheck === false) {
        $buildZIPArchiveCommandHandler->checkComposerDependencies($workspaceID);
    }
    $buildZIPArchiveCommandHandler->removeUnwantedFilesAndDirectories($workspaceID);
    $buildZIPArchiveCommandHandler->installComposerDependencies($workspaceID);

    $check = $folderComparator->compareFolders($expectedModuleFolderpath, $workspaceFolderpath, '');
    $check2 = $folderComparator->compareFolders($workspaceFolderpath, $expectedModuleFolderpath, '');
    if (!empty($check)) {
        printErrorsList($moduleName, $check);
        return 1;
    }
    if (!empty($check2)) {
        printErrorsList($moduleName, $check2);
        return 1;
    }

    echo ' - module ' . $moduleName . ' built successfully' . PHP_EOL;
}

echo "Integration tests run successfully" . PHP_EOL;
return 0;
