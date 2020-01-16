<?php

namespace PrestaShop\ModuleBuilder;

use PrestaShop\ModuleBuilder\Exception\SecurityIssuesInComposerDependenciesException;
use SensioLabs\Security\SecurityChecker;
use Symfony\Component\Filesystem\Filesystem;

class ComposerDependencyChecker
{
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
    }

    public function checkDependencies($targetPath)
    {
        $composerLockFile = $targetPath . '/composer.lock';

        if (false === $this->filesystem->exists($composerLockFile)) {
            return;
        }

        $checker = new SecurityChecker();
        $result = $checker->check($composerLockFile, 'json');

        $alerts = json_decode((string)$result, true);

        if (false === empty($alerts)) {
            throw new SecurityIssuesInComposerDependenciesException(
                'Found security issues in composer dependencies, please check sensiolabs/security-checker report !'
            );
        }
    }
}
