<?php

namespace WeBee\gCMSTests\Kahlan\Config;

$commandLine = $this->commandLine();
$commandLine->option('coverage', 'default', 4);
$commandLine->option('clover', 'default', 'docs/code_coverage_clover.xml');
$commandLine->option('istanbul', 'default', 'docs/code_coverage_istanbul.json');

if (!file_exists('docs')) {
    mkdir('docs', 0777, true);
}
