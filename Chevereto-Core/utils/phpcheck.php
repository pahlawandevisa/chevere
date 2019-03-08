<?php
/*
 * Checks if this server meets minimum PHP requirement
 * I do this in a separated file so I don't have to mix new+old syntax on my code
 */
namespace Chevereto\Core;

const MIN_PHP_VERSION = '7.2.0'; // Can't touch this!
if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
    http_response_code(500);
    trigger_error('This server is running PHP v' . PHP_VERSION . ' and ' . __NAMESPACE__ . ' requires at least PHP v' . MIN_PHP_VERSION, E_USER_ERROR);
}
