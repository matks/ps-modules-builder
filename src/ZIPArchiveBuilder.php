<?php

namespace PrestaShop\ModuleBuilder;

class ZIPArchiveBuilder
{
    public function buildZIPArchiveFile($targetDirectory, $zipFilename)
    {
        $commandScript = sprintf(
            'pushd %s
            zip %s -r .
            popd',
            $targetDirectory,
            $zipFilename
        );

        $output = null;
        exec($commandScript, $output);
    }
}
