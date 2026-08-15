<?php
/**
 * Sample configuration file.
 *
 * Copy this file to config.php and fill in the real database credentials for
 * your environment (local XAMPP/WAMP install or Hostinger hPanel database).
 * config.php is the file every page actually includes; config.sample.php only
 * exists so real credentials are never committed to source control.
 */

// --- Database credentials -------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'waste_collection');
define('DB_USER', 'root');
define('DB_PASS', '');

// --- Site settings ---------------------------------------------------------
define('SITE_NAME', 'Community Waste Collection Management System');

// --- Fixed waste type list and pickup fees (UGX) ----------------------------
// Used by resident/request_pickup.php (the dropdown) and resident/pay.php
// (the simulated flat fee per waste type). Kept in one place so the group can
// change prices or waste types without hunting through multiple files.
define('WASTE_TYPES', ['Household', 'Plastic', 'Organic', 'Other']);
define('WASTE_FEES', [
    'Household' => 5000,
    'Plastic'   => 3000,
    'Organic'   => 3000,
    'Other'     => 4000,
]);

// --- Error handling ---------------------------------------------------------
// Never show raw PHP errors to visitors, but keep logging them so problems
// can still be diagnosed from the server's error log.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// --- Sessions ----------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Base URL ---------------------------------------------------------------
// Works out the web path to the folder this file lives in, so links keep
// working whether the site is installed at the domain root (public_html) or
// in a subfolder (public_html/wastecollect). Every internal link in the
// project is built as BASE_URL . '/something.php'.
$docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$appRoot = rtrim(str_replace('\\', '/', __DIR__), '/');
$basePath = '';
if ($docRoot !== '' && strpos($appRoot, $docRoot) === 0) {
    $basePath = substr($appRoot, strlen($docRoot));
}
define('BASE_URL', $basePath);

// --- Database connection ----------------------------------------------------
require_once __DIR__ . '/includes/db.php';
$pdo = get_db();
