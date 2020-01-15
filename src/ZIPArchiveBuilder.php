<?php

namespace PrestaShop\ModuleBuilder;

class ZIPArchiveBuilder
{
    public function buildZIPArchiveFile($targetDirectory, $zipFilename)
    {
        $commandScript = sprintf(
            'zip %s -r %s',
            $zipFilename,
            $targetDirectory
        );

        $output = null;
        exec($commandScript, $output);
    }
}
