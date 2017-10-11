<?php
/**
 * Bootstrapping script for unit tests.
 *
 * This script should be kept as simple as possible to avoid contamination of the test environment.
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
chdir(__DIR__ . '/../..');
require_once 'vendor/autoload.php';
