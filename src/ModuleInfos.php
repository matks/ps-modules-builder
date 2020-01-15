<?php

namespace PrestaShop\ModuleBuilder;

class ModuleInfos
{
    /** @var string */
    public $moduleName;
    /** @var string */
    public $versionNumber;

    /**
     * @param string $moduleName
     * @param string $versionNumber
     */
    public function __construct($moduleName, $versionNumber)
    {
        $this->moduleName = $moduleName;
        $this->versionNumber = $versionNumber;
    }
}
