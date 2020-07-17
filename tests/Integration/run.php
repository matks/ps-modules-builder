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
    'dashactivity' => ['test-zip' => true, 'zip-name' => 'dashactivity-2.0.2.zip'],
    'gamification' => [],
    'statsnewsletter' => [],
    'ps_mainmenu' => [],
    'productcomments' => ['test-zip' => true, 'zip-name' => 'productcomments-4.0.1.zip'],
];
$workspaceID = 100;

/**
 * @param string $moduleName
 * @param string[] $list
 */
function printErrorsList($moduleName, $list)
{
    $message = sprintf(
        'Test failed for module %s, got differences between expected folder and workspace folder :',
        $moduleName
    );

    echo $message . PHP_EOL;

    foreach ($list as $item) {
        echo ' - ' . $item . PHP_EOL;
    }
}

/**
 * @param string $message
 */
function printErrorMessage($message)
{
    echo $message;
}

/**
 * @param string $message
 */
function printSuccessMessage($message)
{
    echo $message;
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

    $check = $folderComparator->compareFolders($expectedModuleFolderpath, $workspaceFolderpath, __DIR__);
    $check2 = $folderComparator->compareFolders($workspaceFolderpath, $expectedModuleFolderpath, __DIR__);
    if (!empty($check)) {
        printErrorsList($moduleName, $check);
        exit(1);
    }
    if (!empty($check2)) {
        printErrorsList($moduleName, $check2);
        exit(1);
    }

    if (array_key_exists('test-zip', $config) && $config['test-zip'] === true) {
        $expectedZipFilename = $config['zip-name'];
        $expectedZipFilepath = __DIR__ . '/expected-zip/' . $expectedZipFilename;

        $buildZIPArchiveCommandHandler->buildZIPArchiveFile(
            $workspaceID,
            __DIR__ . '/workspace/' . $expectedZipFilename
        );

        // how to test ZIP files are identical ?
        if (!file_exists(__DIR__ . '/workspace/' . $expectedZipFilename)) {
            printErrorMessage(sprintf('Error: %s ZIP file was not built', $expectedZipFilename));
            exit(1);
        }
    }

    printSuccessMessage(' - module ' . $moduleName . ' built successfully' . PHP_EOL);
}

printSuccessMessage("Integration tests run successfully" . PHP_EOL);

return 0;
