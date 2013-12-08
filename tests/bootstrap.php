<?php

/*
 * This file is part of the XPL DateTime component.
 *
 * © Oscar Cubo Medina <ocubom@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Increase error reporting
error_reporting(E_ALL);

// Convert all errors into exceptions
set_error_handler(
    function ($errno, $errstr, $errfile, $errline, $errcontext) {
        // Ignore if error reporting is disabled
        if (0 === error_reporting()) {
            return;
        }

        // Convert into ErrorException
        throw new \ErrorException($errstr, $errno, $errno, $errfile, $errline);
    }
);

// Setup auto-loader
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$loader->add('Xpl\\DateTime\\Test\\', __DIR__);
