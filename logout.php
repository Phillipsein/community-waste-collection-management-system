<?php
/**
 * Destroys the current session and returns the visitor to the landing page.
 * Touches no database tables.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();
redirect('/index.php');
