<?php

/**
 * CLI error view template: error 404.
 */

use CodeIgniter\CLI\CLI;

CLI::error('ERROR: ' . $code);
CLI::write($message);
CLI::newLine();
