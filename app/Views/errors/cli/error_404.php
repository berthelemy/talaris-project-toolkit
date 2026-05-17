<?php

/**
 * File documentation for app/Views/errors/cli/error_404.php.
 */

use CodeIgniter\CLI\CLI;

CLI::error('ERROR: ' . $code);
CLI::write($message);
CLI::newLine();
