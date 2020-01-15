<?php

namespace PrestaShop\ModuleBuilder;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildZIPArchiveCommand extends Command
{
    protected static $defaultName = 'build-zip';

    /**
     * @var BuildZIPArchiveCommandHandler
     */
    private $commandHandler;

    /**
     * @var string
     */
    private $workspaceDirectory;

    public function __construct($name = null)
    {
        $this->workspaceDirectory = __DIR__ . '/../var/';
        $this->commandHandler = new BuildZIPArchiveCommandHandler($this->workspaceDirectory);

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Build ZIP archive for given module')
            ->addArgument('module-folder', InputArgument::REQUIRED, 'module folder location')
            ->addOption(
                'ignore-dependency-check',
                null,
                InputOption::VALUE_REQUIRED,
                'Allows to ignore dependency check step result',
                false
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $moduleFolderpath = $input->getArgument('module-folder');
        $ignoreDependencyCheck = $input->getOption('ignore-dependency-check');

        $workspaceID = md5(rand(0, 9999));

        // 1. create temporary workspace
        $this->commandHandler->createWorkspace($workspaceID);
        // 2. copy the module folder into workspace
        $this->commandHandler->copyModuleFolderIntoWorkspace($workspaceID, $moduleFolderpath);
        // 3. check composer dependencies
        if ($ignoreDependencyCheck === false) {
            $this->commandHandler->checkComposerDependencies($workspaceID);
        }
        // 4. remove unwanted files and folders
        $this->commandHandler->removeUnwantedFilesAndDirectories($workspaceID);
        // 5. install composer dependencies
        $this->commandHandler->installComposerDependencies($workspaceID);
        // 6. check prestashop security practices are valid
        // @todo
        // 7. build zip archive
        $moduleInformations = $this->commandHandler->extractModuleInformationsFromWorkspace($workspaceID);
        $zipFileName = sprintf(
            '%s-%s.zip',
            $moduleInformations->moduleName,
            $moduleInformations->versionNumber
        );
        $this->commandHandler->buildZIPArchiveFile(
            $workspaceID,
            $this->workspaceDirectory . DIRECTORY_SEPARATOR . $zipFileName
        );
        // 8. delete workspace
        $this->commandHandler->deleteWorkspace($workspaceID);

        $output->writeln(sprintf('<info>%s ZIP archive built with success</info>', $zipFileName));

        return 0;
    }
}
