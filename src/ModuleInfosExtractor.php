<?php

namespace PrestaShop\ModuleBuilder;

use Symfony\Component\Filesystem\Filesystem;

class ModuleInfosExtractor
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

    public function extractModuleInformations($moduleFolderPath)
    {
        if (!$this->filesystem->exists($moduleFolderPath . '/config.xml')) {
            throw new \RuntimeException('Cannot extract module information, no config.xml file');
        }

        $xmlContent = file_get_contents($moduleFolderPath . '/config.xml');
        $xml = simplexml_load_string($xmlContent, null, LIBXML_NOCDATA);
        $json = json_encode($xml);
        $data = json_decode($json);

        if (!array_key_exists('name', $data)) {
            throw new \RuntimeException('Cannot extract module name from config.xml file');
        }
        if (!array_key_exists('version', $data)) {
            throw new \RuntimeException('Cannot extract module version number from config.xml file');
        }

        return new ModuleInfos(
            $data->name,
            $data->version
        );
    }
}
