#!/usr/bin/php
<?php

declare(strict_types=1);

namespace App;

require_once __DIR__ . '/autoload.php';

use Shopsys\FrameworkBundle\Component\Phing\PhingDownloader;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;

return static function () {
    if (file_exists(__DIR__ . '/../../../parameters_monorepo.yaml')) {
        $vendorDir = __DIR__ . '/../../../vendor';
    } else {
        $vendorDir = __DIR__ . '/../vendor';
    }

    $inputDefinition = new InputDefinition(
        [
            new InputOption('--' . PhingDownloader::OPTION_VERSION, null, InputOption::VALUE_REQUIRED, 'Version of Phing in SemVer format.'),
        ],
    );

    $input = new ArgvInput();
    $input->bind($inputDefinition);

    $output = new ConsoleOutput();

    $phingDownloader = new PhingDownloader($vendorDir);
    exit($phingDownloader->execute($input, $output));
};
