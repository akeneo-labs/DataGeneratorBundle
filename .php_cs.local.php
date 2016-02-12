<?php

$finder = \Symfony\CS\Finder\DefaultFinder::create()->files();
$fixers = require __DIR__ . '/.php_cs-fixers.php';

$finder->name('*.php')
    ->in(__DIR__ . '/Command')
    ->in(__DIR__ . '/Configuration')
    ->in(__DIR__ . '/DependencyInjection')
    ->in(__DIR__ . '/Generator');

return \Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers($fixers)
    ->finder($finder);
