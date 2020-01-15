<?php

namespace PrestaShop\ModuleBuilder;

class ComposerInstaller
{
    public function installDependencies($targetDirectory)
    {
        $commandScript = 'php composer.phar install --no-dev --optimize-autoloader -d '.$targetDirectory.' &> /dev/null';

        $output = null;
        exec($commandScript, $output);
    }
}
