<?php
session_start();
if (!isset($_SESSION['manager_id'])) {
    header("Location: login2.html");
    exit;
}

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "ded2";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch missed‐bus reports by type
$arrivalMisses = $conn->query("
    SELECT user_id, route, time_slot, reported_at
      FROM missed_bus_reports
     WHERE type = 'arrival'
     ORDER BY reported_at DESC
")->fetch_all(MYSQLI_ASSOC);

$departureMisses = $conn->query("
    SELECT user_id, route, time_slot, reported_at
      FROM missed_bus_reports
     WHERE type = 'departure'
     ORDER BY reported_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Handle notices
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['notice_message'])) {
        $stmt = $conn->prepare("INSERT INTO notices (message, status) VALUES (?, 'active')");
        $stmt->bind_param("s", $_POST['notice_message']);
        $stmt->execute();
        $stmt->close();
    }
    if (isset($_POST['action'], $_POST['notice_id'])) {
        $id     = (int)$_POST['notice_id'];
        $action = $_POST['action'];
        if ($action === 'deactivate') {
            $stmt = $conn->prepare("UPDATE notices SET status='inactive' WHERE id=?");
        } elseif ($action === 'activate') {
            $stmt = $conn->prepare("UPDATE notices SET status='active' WHERE id=?");
        } else {
            $stmt = $conn->prepare("DELETE FROM notices WHERE id=?");
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        header("Location: bus_manager_interface.php");
        exit;
    }
}

// Fetch notices
$notices = $conn->query("
    SELECT id, message, status, created_at
      FROM notices
     ORDER BY created_at DESC
     LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Fetch arrival overview
$arrivalData = $conn->query("
    SELECT route, time_slot, COUNT(*) AS student_count
      FROM arrival_info
     WHERE status='confirmed'
     GROUP BY route, time_slot
")->fetch_all(MYSQLI_ASSOC);

// Fetch departure overview
$timeSlots     = ['1:40 PM', '3:15 PM', '4:15 PM'];
$routes        = ['Shewrapara', 'Shonirakhra', 'Mohakhali', 'Chashara'];
$departureData = [];

foreach ($routes as $r) {
    foreach ($timeSlots as $ts) {
        // 1) total buses on this route
        $totalRow = $conn->query("
            SELECT COUNT(*) AS c
              FROM bus_info
             WHERE designated_route = '$r'
        ")->fetch_assoc();
        $totalBuses = isset($totalRow['c']) ? (int)$totalRow['c'] : 0;

        // 2) how many already allocated for this route+slot
        $allocRow = $conn->query("
            SELECT buses_allocated
              FROM bus_allocations
             WHERE route = '$r' AND time_slot = '$ts'
        ")->fetch_assoc();
        $allocated = isset($allocRow['buses_allocated'])
                   ? (int)$allocRow['buses_allocated']
                   : 0;

        // 3) remaining available
        $available = $totalBuses - $allocated;
        if ($available < 0) {
            $available = 0;
        }

        // 4) confirmed student count
        $studRow = $conn->query("
            SELECT COUNT(*) AS c
              FROM departure_info
             WHERE route = '$r'
               AND time_slot = '$ts'
               AND status = 'confirmed'
        ")->fetch_assoc();
        $studentCount = isset($studRow['c']) ? (int)$studRow['c'] : 0;

        $departureData[] = [
            'route'           => $r,
            'time_slot'       => $ts,
            'student_count'   => $studentCount,
            'buses_available' => $available,
            'buses_allocated' => $allocated,
        ];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Bus Manager Interface</title>
  <link rel="stylesheet" href="bus_manager.css">
</head>
<body>

  <h1>Welcome, Manager <?= htmlspecialchars($_SESSION['manager_id']) ?></h1>
  <h2>Bus Management System</h2>

  <!-- Toolbar -->
  <div class="toolbar">
    <button id="btn-arrival-missed"    class="toolbar-button">Arrival Missed Reports</button>
    <button id="btn-departure-missed"  class="toolbar-button">Departure Missed Reports</button>
    <button id="btn-notices"           class="toolbar-button">Post/View Notices</button>
    <button id="btn-arrival-overview"  class="toolbar-button">Arrival Overview</button>
    <button id="btn-departure-overview"class="toolbar-button">Departure Overview</button>
  </div>

  <!-- Sections -->
  <div id="section-arrival-missed">
    <h3>🚨 Arrival Missed Reports (<?= count($arrivalMisses) ?>)</h3>
    <?php if ($arrivalMisses): ?>
      <table class="bm-table">
        <thead>
          <tr><th>User ID</th><th>Route</th><th>Time Slot</th><th>Reported At</th></tr>
        </thead>
        <tbody>
          <?php foreach ($arrivalMisses as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['user_id']) ?></td>
            <td><?= htmlspecialchars($r['route']) ?></td>
            <td><?= htmlspecialchars($r['time_slot']) ?></td>
            <td><?= htmlspecialchars($r['reported_at']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No arrival missed-bus reports.</p>
    <?php endif; ?>
  </div>

  <div id="section-departure-missed" style="display:none;">
    <h3>🚨 Departure Missed Reports (<?= count($departureMisses) ?>)</h3>
    <?php if ($departureMisses): ?>
      <table class="bm-table">
        <thead>
          <tr><th>User ID</th><th>Route</th><th>Time Slot</th><th>Reported At</th></tr>
        </thead>
        <tbody>
          <?php foreach ($departureMisses as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['user_id']) ?></td>
            <td><?= htmlspecialchars($r['route']) ?></td>
            <td><?= htmlspecialchars($r['time_slot']) ?></td>
            <td><?= htmlspecialchars($r['reported_at']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No departure missed-bus reports.</p>
    <?php endif; ?>
  </div>

  <div id="section-notices" style="display:none;">
    <h3>📢 Notices</h3>
    <form method="POST" class="bm-form">
      <textarea name="notice_message" rows="3" placeholder="Enter notice…" required></textarea>
      <button type="submit" class="toolbar-button">Add Notice</button>
    </form>
    <?php if ($notices): ?>
      <ul class="bm-notice-list">
        <?php foreach ($notices as $n): ?>
        <li>
          <?= htmlspecialchars($n['message']) ?>
          (<?= htmlspecialchars($n['status']) ?>, <?= htmlspecialchars($n['created_at']) ?>)
          <form method="POST" style="display:inline;">
            <input type="hidden" name="notice_id" value="<?= $n['id'] ?>">
            <input type="hidden" name="action"
                   value="<?= $n['status']==='active'?'deactivate':'activate' ?>">
            <button type="submit" class="toolbar-button">
              <?= $n['status']==='active'?'Deactivate':'Activate' ?>
            </button>
          </form>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="notice_id" value="<?= $n['id'] ?>">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="toolbar-button"
                    onclick="return confirm('Delete this notice?')">
              Delete
            </button>
          </form>
        </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No notices.</p>
    <?php endif; ?>
  </div>

  <div id="section-arrival-overview" style="display:none;">
    <h3>Total Students Arriving</h3>
    <table class="bm-table">
      <thead>
        <tr><th>Route</th><th>Time Slot</th><th>Count</th></tr>
      </thead>
      <tbody>
        <?php foreach ($arrivalData as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['route']) ?></td>
          <td><?= htmlspecialchars($r['time_slot']) ?></td>
          <td><?= htmlspecialchars($r['student_count']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div id="section-departure-overview" style="display:none;">
    <?php foreach ($departureData as $d): ?>
    <section class="bm-overview-block">
      <h3>Departing <?= htmlspecialchars($d['time_slot']) ?>
          via <?= htmlspecialchars($d['route']) ?></h3>
      <p><strong>Students:</strong> <?= $d['student_count'] ?></p>
      <p><strong>Available:</strong> <?= $d['buses_available'] ?></p>
      <p><strong>Allocated:</strong> <?= $d['buses_allocated'] ?></p>
      <form action="allocate_buses.php" method="POST" class="bm-form">
        <input type="hidden" name="route"     value="<?= htmlspecialchars($d['route']) ?>">
        <input type="hidden" name="time_slot" value="<?= htmlspecialchars($d['time_slot']) ?>">
        <label>Allocate Buses:</label>
        <input type="number" name="buses"
               min="0" max="<?= $d['buses_available'] ?>" required>
        <button type="submit" class="toolbar-button">Allocate</button>
      </form>
    </section>
    <?php endforeach; ?>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function(){
      const map = {
        'btn-arrival-missed':    'section-arrival-missed',
        'btn-departure-missed':  'section-departure-missed',
        'btn-notices':           'section-notices',
        'btn-arrival-overview':  'section-arrival-overview',
        'btn-departure-overview':'section-departure-overview'
      };
      function hideAll(){
        Object.values(map).forEach(id => {
          document.getElementById(id).style.display = 'none';
        });
      }
      Object.entries(map).forEach(([btn,sec])=>{
        document.getElementById(btn).addEventListener('click', e=>{
          e.preventDefault();
          hideAll();
          document.getElementById(sec).style.display = 'block';
        });
      });
      hideAll();
      document.getElementById('section-arrival-missed').style.display = 'block';
    });
  </script>

</body>
</html>
