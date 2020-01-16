<?php

namespace PrestaShop\ModuleBuilder;

use PrestaShop\ModuleBuilder\Exception\MissingRequirementException;

class RequirementsChecker
{
    /**
     * @throws MissingRequirementException
     */
    public function checkRequirements()
    {
        $return = null;
        $output = null;
        exec('php -v', $output, $return);

        if ($return !== 0) {
            throw new MissingRequirementException('Cannot run php');
        }

        $return = null;
        $output = null;
        exec('zip -v', $output, $return);

        if ($return !== 0) {
            throw new MissingRequirementException('Cannot run zip');
        }

        $return = null;
        $output = null;
        exec('php composer.phar -v', $output, $return);

        if ($return !== 0) {
            throw new MissingRequirementException('Cannot run composer from PHAR archive');
        }
    }
}
