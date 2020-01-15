#!/usr/bin/env php
<?php

require __DIR__ . '/../../vendor/autoload.php';

use PrestaShop\ModuleBuilder\BuildZIPArchiveCommandHandler;
use PrestaShopTests\Integration\FolderComparator;

$buildZIPArchiveCommandHandler = new BuildZIPArchiveCommandHandler(__DIR__ . '/workspace/');
$folderComparator = new FolderComparator();

$modulesToTest = [
    'blockreassurance',
    'gsitemap',
    // 'autoupgrade', security issues found because of outdated twig version
    'dashactivity',
    'gamification',
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

foreach ($modulesToTest as $module) {
    $workspaceID++;
    $moduleFolderpath = __DIR__ . '/module-samples/' . $module;
    $expectedModuleFolderpath = __DIR__ . '/expected/' . $module;
    $workspaceFolderpath = __DIR__ . '/workspace/' . $workspaceID;

    $buildZIPArchiveCommandHandler->createWorkspace($workspaceID);
    $buildZIPArchiveCommandHandler->copyModuleFolderIntoWorkspace($workspaceID, $moduleFolderpath);
    $buildZIPArchiveCommandHandler->checkComposerDependencies($workspaceID);
    $buildZIPArchiveCommandHandler->removeUnwantedFilesAndDirectories($workspaceID);
    $buildZIPArchiveCommandHandler->installComposerDependencies($workspaceID);

    $check = $folderComparator->compareFolders($expectedModuleFolderpath, $workspaceFolderpath, '');
    $check2 = $folderComparator->compareFolders($workspaceFolderpath, $expectedModuleFolderpath, '');
    if (!empty($check)) {
        printErrorsList($module, $check);
        return 1;
    }
    if (!empty($check2)) {
        printErrorsList($module, $check2);
        return 1;
    }

    echo ' - module ' . $module . ' built successfully' . PHP_EOL;
}

echo "Integration tests run successfully" . PHP_EOL;
return 0;
