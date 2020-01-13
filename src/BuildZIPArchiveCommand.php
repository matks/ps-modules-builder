<?php

namespace PrestaShop\ModuleBuilder;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildZIPArchiveCommand extends Command
{
    protected static $defaultName = 'build-zip';

    /**
     * @var BuildZIPArchiveCommandHandler
     */
    private $commandHandler;

    public function __construct($name = null)
    {
        $this->commandHandler = new BuildZIPArchiveCommandHandler(__DIR__ . '/../var/');

        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $workspaceID = md5(rand(0, 9999));
        // for debug only
        $workspaceID = 1;
        $moduleFolderpath = __DIR__ . '/../tests/Integration/module-samples/blockreassurance';

        // 1. create temporary workspace
        $this->commandHandler->createWorkspace($workspaceID);
        // 2. copy the module folder into workspace
        $this->commandHandler->copyModuleFolderIntoWorkspace($workspaceID, $moduleFolderpath);
        // 3. check composer dependencies
        $this->commandHandler->checkComposerDependencies($workspaceID);
        // 4. remove unwanted files and folders
        $this->commandHandler->removeUnwantedFilesAndDirectories($workspaceID);
        // 5. install composer dependencies
        $this->commandHandler->installComposerDependencies($workspaceID);
        // 6. check prestashop security practices are valid

        // 7. build zip archive

        // 8. delete workspace

        return 0;
    }
}
