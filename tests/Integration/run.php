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

/**
 * @param string $message
 */
function printErrorMessage($message)
{
    echo "\033[31m";
    echo $message;
    echo "\033[37m";
}

/**
 * @param string $message
 */
function printSuccessMessage($message)
{
    echo "\033[32m";
    echo $message;
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
            return 1;
        }
    }

    printSuccessMessage(' - module ' . $moduleName . ' built successfully' . PHP_EOL);
}

printSuccessMessage("Integration tests run successfully" . PHP_EOL);

return 0;
