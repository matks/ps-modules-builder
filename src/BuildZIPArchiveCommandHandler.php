<?php

namespace PrestaShop\ModuleBuilder;

use Symfony\Component\Filesystem\Filesystem;

class BuildZIPArchiveCommandHandler
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var BadFilesDeletor
     */
    private $badFilesDeletor;

    /**
     * @var ComposerInstaller
     */
    private $composerInstaller;

    /**
     * @var ComposerDependencyChecker
     */
    private $composerDependencyChecker;

    /**
     * @var ModuleInfosExtractor
     */
    private $moduleInfosExtractor;

    /**
     * @var ZIPArchiveBuilder
     */
    private $zipArchiveBuilder;

    /**
     * @var string
     */
    private $workspaceDirectory;

    public function __construct($workspaceDirectory)
    {
        $this->filesystem = new Filesystem();
        $this->badFilesDeletor = new BadFilesDeletor($this->filesystem);
        $this->composerInstaller = new ComposerInstaller();
        $this->composerDependencyChecker = new ComposerDependencyChecker($this->filesystem);
        $this->moduleInfosExtractor = new ModuleInfosExtractor($this->filesystem);
        $this->zipArchiveBuilder = new ZIPArchiveBuilder();
        $this->workspaceDirectory = $workspaceDirectory;
    }

    public function createWorkspace($workspaceID, $removeIfAlreadyExists = true)
    {
        $workspaceFolder = $this->getWorkspaceFolder($workspaceID);

        if ($this->filesystem->exists($workspaceFolder)) {
            if ($removeIfAlreadyExists) {
                $this->filesystem->remove($workspaceFolder);
            } else {
                throw new \RuntimeException(sprintf('Cannot use workspace %s, it already exists', $workspaceID));
            }
        }

        $this->filesystem->mkdir($workspaceFolder);
    }

    public function deleteWorkspace($workspaceID)
    {
        $workspaceFolder = $this->getWorkspaceFolder($workspaceID);

        $this->filesystem->remove($workspaceFolder);
    }

    public function copyModuleFolderIntoWorkspace($workspaceID, $moduleFolderpath)
    {
        $workspaceFolder = $this->getWorkspaceFolder($workspaceID);

        $this->filesystem->mirror($moduleFolderpath, $workspaceFolder);
    }

    /**
     * @param int $workspaceID
     *
     * @return string
     */
    public function getWorkspaceFolder($workspaceID)
    {
        $workspaceFolder = $this->workspaceDirectory . $workspaceID;

        return $workspaceFolder;
    }

    public function removeUnwantedFilesAndDirectories($workspaceID)
    {
        $this->badFilesDeletor->cleanModuleFolder($this->getWorkspaceFolder($workspaceID));
    }

    public function checkComposerDependencies($workspaceID)
    {
        $this->composerDependencyChecker->checkDependencies($this->getWorkspaceFolder($workspaceID));
    }

    public function installComposerDependencies($workspaceID)
    {
        $this->composerInstaller->installDependencies($this->getWorkspaceFolder($workspaceID));
    }

    public function extractModuleInformationsFromWorkspace($workspaceID)
    {
        return $this->moduleInfosExtractor->extractModuleInformations($this->getWorkspaceFolder($workspaceID));
    }

    public function buildZIPArchiveFile($workspaceId, $zipFilename)
    {
        $this->zipArchiveBuilder->buildZIPArchiveFile($this->getWorkspaceFolder($workspaceId), $zipFilename);
    }
}
