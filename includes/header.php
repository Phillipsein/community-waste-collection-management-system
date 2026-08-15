<?php
/**
 * Opening <html>/<head> and the top navbar. Included by every page after
 * config.php (and includes/auth.php, on protected pages) have already run.
 * Reads no tables directly, just current_user() from the session.
 *
 * Pages may set $page_title before including this file to customise the
 * <title> tag; it falls back to SITE_NAME if not set.
 */

$user = current_user();
$title = isset($page_title) && $page_title !== '' ? $page_title . ' - ' . SITE_NAME : SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($title); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-md navbar-dark app-navbar sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?php echo BASE_URL; ?>/index.php">Waste Collection MS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-md-0">
<?php if (!$user): ?>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/about.php">About</a></li>
<?php elseif ($user['role'] === 'resident'): ?>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/resident/dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/resident/request_pickup.php">Request Pickup</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/resident/my_requests.php">My Requests</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/resident/schedule.php">Schedule</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/resident/complaints.php">Complaints</a></li>
<?php elseif ($user['role'] === 'collector'): ?>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/collector/dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/collector/my_requests.php">My Requests</a></li>
<?php elseif ($user['role'] === 'administrator'): ?>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/admin/dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/admin/zones.php">Zones</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/admin/schedules.php">Schedules</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/admin/vehicles.php">Vehicles</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/admin/collectors.php">Collectors</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/admin/requests.php">Requests</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/admin/complaints.php">Complaints</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/admin/reports.php">Reports</a></li>
<?php endif; ?>
      </ul>
      <ul class="navbar-nav">
<?php if (!$user): ?>
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/register.php">Register</a></li>
        <li class="nav-item"><a class="nav-link btn btn-accent btn-sm ms-md-2" href="<?php echo BASE_URL; ?>/login.php">Login</a></li>
<?php else: ?>
        <li class="nav-item navbar-text text-white-50 me-2">
          Signed in as <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars(ucfirst($user['role'])); ?>)
        </li>
        <li class="nav-item"><a class="nav-link btn btn-accent btn-sm" href="<?php echo BASE_URL; ?>/logout.php">Logout</a></li>
<?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-4">
<?php if (isset($_GET['flash'])): ?>
  <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($_GET['flash']); ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
  <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($_GET['error']); ?></div>
<?php endif; ?>
