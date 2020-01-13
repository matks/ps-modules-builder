<?php

namespace PrestaShop\ModuleBuilder;

use Symfony\Component\Filesystem\Filesystem;

class BadFilesDeletor
{
    /**
     * List of files to delete
     *
     * @var string[]
     */
    private $filesBlacklist;

    /**
     * List of directories to delete
     *
     * @var string[]
     */
    private $directoriesBlacklist;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        $this->filesBlacklist = [
            '.gitignore',
            '.php_cs.dist',
            '.travis.yml'
        ];

        $this->directoriesBlacklist = [
            '.github',
            'test',
            'tests',
        ];
    }

    public function cleanModuleFolder($moduleFolderPath)
    {
        foreach ($this->filesBlacklist as $fileToRemove) {
            $filepath = $moduleFolderPath . '/' . $fileToRemove;

            if ($this->filesystem->exists($filepath)) {
                $this->filesystem->remove($filepath);
            }
        }

        foreach ($this->directoriesBlacklist as $dirToRemove) {
            $dirPath = $moduleFolderPath . '/' . $dirToRemove;

            if ($this->filesystem->exists($dirPath)) {
                $this->filesystem->remove($dirPath);
            }
        }
    }
}
