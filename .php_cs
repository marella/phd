<?php

use Symfony\CS\Config\Config;
use Symfony\CS\FixerInterface;
use Symfony\CS\Finder\DefaultFinder;

$fixers = [
    '-psr0',
    'no_useless_return',
    'ordered_use',
    'phpdoc_order',
    'short_array_syntax',
];

return Config::create()
    ->level(FixerInterface::SYMFONY_LEVEL)
    ->fixers($fixers)
    ->finder(DefaultFinder::create()->in(__DIR__))
    ->setUsingCache(true);
