<?php
/**
 * Authentication and authorisation helpers shared by every page:
 * current_user(), require_role(), login_user(), logout_user(), redirect().
 * Reads and writes $_SESSION only; touches no database tables directly.
 * Requires config.php to already be loaded (needs BASE_URL and the session).
 */

/**
 * Returns the logged in user's session data, or null if nobody is logged in.
 * Shape: ['role' => 'resident'|'collector'|'administrator', 'id' => int,
 *         'name' => string, 'email' => string, plus role-specific extras
 *         such as 'zone_id'].
 */
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

/**
 * Sends a Location header for a path relative to the site root (e.g.
 * '/login.php') and stops execution. Always root-relative via BASE_URL so
 * links work the same whether the page redirecting is at the site root or
 * one folder down (resident/, collector/, admin/).
 */
function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

/**
 * Returns the site-root-relative path to a role's dashboard. Needed because
 * the administrator role string doesn't match its folder name (the folder
 * is admin/, not administrator/); resident and collector folders do match.
 */
function role_home_path(string $role): string
{
    $folders = [
        'resident'      => 'resident',
        'collector'     => 'collector',
        'administrator' => 'admin',
    ];
    $folder = $folders[$role] ?? $role;

    return '/' . $folder . '/dashboard.php';
}

/**
 * Must be called at the very top of every page under resident/, collector/,
 * and admin/, before any HTML is echoed. Sends the visitor to login.php if
 * they are not logged in as exactly this role. Returns the session user
 * array on success so the calling page can use it right away.
 */
function require_role(string $role): array
{
    $user = current_user();

    if (!$user || $user['role'] !== $role) {
        redirect('/login.php');
    }

    return $user;
}

/**
 * Stores the logged in user's data in the session and regenerates the
 * session ID (a basic protection against session fixation on login).
 */
function login_user(array $userRow, string $role): void
{
    session_regenerate_id(true);

    $sessionUser = [
        'role'  => $role,
        'email' => $userRow['email'],
        'name'  => $userRow['full_name'],
    ];

    if ($role === 'resident') {
        $sessionUser['id']      = (int) $userRow['resident_id'];
        $sessionUser['zone_id'] = (int) $userRow['zone_id'];
    } elseif ($role === 'collector') {
        $sessionUser['id']      = (int) $userRow['collector_id'];
        $sessionUser['zone_id'] = (int) $userRow['zone_id'];
    } elseif ($role === 'administrator') {
        $sessionUser['id'] = (int) $userRow['admin_id'];
    }

    $_SESSION['user'] = $sessionUser;
}

/** Clears the whole session and destroys it. */
function logout_user(): void
{
    $_SESSION = [];
    session_destroy();
}
