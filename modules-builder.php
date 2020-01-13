#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use PrestaShop\ModuleBuilder\BuildZIPArchiveCommand;

$application = new Application();

$application->add(new BuildZIPArchiveCommand());

$application->run();
