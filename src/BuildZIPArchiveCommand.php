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
            )->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Checks if archive can be built but does not build it'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $requirementsChecker = new RequirementsChecker();
        $requirementsChecker->checkRequirements();

        $moduleFolderpath = $input->getArgument('module-folder');
        $ignoreDependencyCheck = $input->getOption('ignore-dependency-check');
        $isDryRun = $input->getOption('dry-run');

        $workspaceID = md5(rand(0, 9999));

        // 1. create temporary workspace
        $this->commandHandler->createWorkspace($workspaceID);
        // 2. copy the module folder into workspace
        try {

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
            if ($isDryRun === false) {
                $this->commandHandler->buildZIPArchiveFile(
                    $workspaceID,
                    $this->workspaceDirectory . $zipFileName
                );
            }
        } catch (\Exception $e) {
            $this->commandHandler->deleteWorkspace($workspaceID);
            throw $e;
        }

        // 8. delete workspace
        $this->commandHandler->deleteWorkspace($workspaceID);

        if ($isDryRun) {
            $output->writeln(sprintf('<info>%s ZIP archive can be built successfully</info>', $zipFileName));
        } else {
            $output->writeln(sprintf('<info>%s ZIP archive built with success</info>', $zipFileName));
        }

        return 0;
    }
}
