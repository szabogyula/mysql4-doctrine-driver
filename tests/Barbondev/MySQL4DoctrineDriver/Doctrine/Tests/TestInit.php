<?php
/*
 * This file bootstraps the test environment.
 */
namespace Barbondev\MySQL4DoctrineDriver\Doctrine\Tests;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__ . '/../../../../../vendor/autoload.php';

$loader->add('Doctrine\Tests', __DIR__ . '/../../../../../vendor/doctrine/dbal/tests');

$loader->add('Barbondev\MySQL4DoctrineDriver\Doctrine\Tests', __DIR__ . '/../../../..');
