<?php
/**
 * Project and Group 6 team info page. Anyone can view this page.
 * Touches no database tables.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';

$page_title = 'About';
require __DIR__ . '/includes/header.php';

$members = [
    ['name' => 'Phillip Ssempereza', 'reg_no' => 'VU-BCS-2407-0707-EVE'],
    ['name' => 'Mwondha Andrew',     'reg_no' => 'VU-BIT-2411-0560-EVE'],
    ['name' => 'Sserunjogi Muhammad', 'reg_no' => 'VU-BCS-2407-0417-EVE'],
    ['name' => 'Kimoga Sudais',      'reg_no' => 'VU-BIT-2311-0902-EVE'],
];
?>

<h1 class="mb-3">About This Project</h1>
<p class="lead">
  This prototype was built for the Software Engineering class project
  "A Web Based Waste Collection Management System for Urban Residential
  Communities". It lets residents request waste pickups, view their zone's
  collection schedule, pay for completed pickups, and raise complaints, while
  collectors manage the requests assigned to them and administrators oversee
  zones, schedules, vehicles, collector accounts, and reporting. The design
  follows the architecture, use case diagram, data flow diagrams, entity
  relationship diagram, and class diagram presented in the accompanying
  project report.
</p>

<div class="card mt-4">
  <div class="card-header bg-brand">Group 6</div>
  <div class="card-body">
    <table class="table mb-0">
      <thead>
        <tr>
          <th>Name</th>
          <th>Registration Number</th>
        </tr>
      </thead>
      <tbody>
<?php foreach ($members as $member): ?>
        <tr>
          <td><?php echo htmlspecialchars($member['name']); ?></td>
          <td><?php echo htmlspecialchars($member['reg_no']); ?></td>
        </tr>
<?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
