#!/usr/bin/env php
<?php

require __DIR__ . '/../../vendor/autoload.php';

use PrestaShop\ModuleBuilder\BuildZIPArchiveCommandHandler;
use PrestaShopTests\Integration\FolderComparator;

$buildZIPArchiveCommandHandler = new BuildZIPArchiveCommandHandler(__DIR__ . '/workspace/');
$folderComparator = new FolderComparator();

// ------------ blockreassurance ------------
$workspaceID = 100;
$moduleFolderpath = __DIR__ . '/module-samples/blockreassurance';
$expectedModuleFolderpath = __DIR__ . '/expected/blockreassurance';
$workspaceFolderpath = __DIR__ . '/workspace/' . $workspaceID;
$buildZIPArchiveCommandHandler->createWorkspace($workspaceID);
$buildZIPArchiveCommandHandler->copyModuleFolderIntoWorkspace($workspaceID, $moduleFolderpath);
$buildZIPArchiveCommandHandler->checkComposerDependencies($workspaceID);
$buildZIPArchiveCommandHandler->removeUnwantedFilesAndDirectories($workspaceID);
$buildZIPArchiveCommandHandler->installComposerDependencies($workspaceID);

// blockreassurance test validation
$check = $folderComparator->compareFolders($expectedModuleFolderpath, $workspaceFolderpath, '');
if (!empty($check)) {
    $message = sprintf(
        'Test failed, got differences between expected folder and workspace folder : %s',
        ' - ' . PHP_EOL . implode(PHP_EOL . ' - ', $check)
    );

    throw new \RuntimeException($message);
}

// ------------ gsitemap ------------
$workspaceID = 101;
$moduleFolderpath = __DIR__ . '/module-samples/gsitemap';
$expectedModuleFolderpath = __DIR__ . '/expected/gsitemap';
$workspaceFolderpath = __DIR__ . '/workspace/' . $workspaceID;
$buildZIPArchiveCommandHandler->createWorkspace($workspaceID);
$buildZIPArchiveCommandHandler->copyModuleFolderIntoWorkspace($workspaceID, $moduleFolderpath);
$buildZIPArchiveCommandHandler->checkComposerDependencies($workspaceID);
$buildZIPArchiveCommandHandler->removeUnwantedFilesAndDirectories($workspaceID);
$buildZIPArchiveCommandHandler->installComposerDependencies($workspaceID);

// blockreassurance test validation
$check = $folderComparator->compareFolders($expectedModuleFolderpath, $workspaceFolderpath, '');
if (!empty($check)) {
    $message = sprintf(
        'Test failed, got differences between expected folder and workspace folder : %s',
        ' - ' . PHP_EOL . implode(PHP_EOL . ' - ', $check)
    );

    throw new \RuntimeException($message);
}
