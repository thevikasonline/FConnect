<?php

/**
 * index.php
 * @author John Doe <john.doe@example.com>
 * @copyright (c) 2013, John Doe
 */
chdir(dirname(__DIR__));
require_once 'vendor/autoload.php';

use Vikas\Exception as VikasException;
use Vikas\Registry;
use Vikas\Vikas;
use Vikas\Request;

/**
 * Set Config
 */
if (is_file('config/config.php')) {
    $config = require_once 'config/config.php';
    Registry::set("config", $config);
} else {
    throw new VikasException("Config File doesn't exist.");
}
/**
 * Prepare route
 */
require_once Request::_require();
?>
