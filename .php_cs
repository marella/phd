<?php

use Symfony\CS\Config\Config;
use Symfony\CS\FixerInterface;
use Symfony\CS\Finder\DefaultFinder;

$fixers = [
    '-pre_increment',
    'short_array_syntax',
    'phpdoc_order',
];

return Config::create()
    ->level(FixerInterface::SYMFONY_LEVEL)
    ->fixers($fixers)
    ->finder(DefaultFinder::create()->in(__DIR__))
    ->setUsingCache(true);
