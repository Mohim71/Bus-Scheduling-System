<?php
session_start();

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "ded2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Fetch up to 3 manager phone numbers
$managerPhones = [];
if ($res = $conn->query("SELECT phone_number FROM bus_manager_details ORDER BY id LIMIT 3")) {
    while ($row = $res->fetch_assoc()) {
        $managerPhones[] = $row['phone_number'];
    }
    $res->free();
}

// Define available slots
$arrivalSlots   = ["7:00 AM", "8:00 AM", "9:00 AM"];
$departureSlots = ["1:40 PM", "3:15 PM", "4:15 PM"];

// Handle “I Missed This Bus”
if (isset($_GET['missed_type'])) {
    $type  = $_GET['missed_type'];
    $table = ($type === 'arrival') ? 'arrival_info' : 'departure_info';

    // fetch last booking
    $q = $conn->prepare("
        SELECT route, time_slot
          FROM {$table}
         WHERE user_id = ?
         ORDER BY id DESC
         LIMIT 1
    ");
    $q->bind_param("s", $_SESSION['user_id']);
    $q->execute();
    $info = $q->get_result()->fetch_assoc() ?: ['route'=>'','time_slot'=>''];
    $q->close();

    // cancel it
    $stmt = $conn->prepare("
        UPDATE {$table}
           SET status = 'cancelled'
         WHERE user_id = ?
         ORDER BY id DESC
         LIMIT 1
    ");
    $stmt->bind_param("s", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();

    // record the miss
    $stmt = $conn->prepare("
        INSERT INTO missed_bus_reports
            (user_id, type, route, time_slot, reported_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("isss",
        $_SESSION['user_id'],
        $type,
        $info['route'],
        $info['time_slot']
    );
    $stmt->execute();
    $stmt->close();

    $_SESSION['flash_message'] = "Missed {$type} bus reported.";
    header("Location: rebook.php");
    exit;
}

// Handle Cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_type'])) {
    $type  = $_POST['cancel_type'];
    $table = ($type === 'arrival') ? 'arrival_info' : 'departure_info';

    $stmt = $conn->prepare("
        UPDATE {$table}
           SET status = 'cancelled'
         WHERE user_id = ?
         ORDER BY id DESC
         LIMIT 1
    ");
    $stmt->bind_param("s", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();

    $_SESSION['flash_message'] = ucfirst($type) . " booking cancelled.";
    header("Location: rebook.php");
    exit;
}

// Handle Rebook submission
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['rebook_type'], $_POST['new_time'])
) {
    $type    = $_POST['rebook_type'];
    $newTime = $_POST['new_time'];
    $table   = ($type === 'arrival') ? 'arrival_info' : 'departure_info';

    // fetch last route
    $q = $conn->prepare("
        SELECT route
          FROM {$table}
         WHERE user_id = ?
         ORDER BY id DESC
         LIMIT 1
    ");
    $q->bind_param("s", $_SESSION['user_id']);
    $q->execute();
    $route = $q->get_result()->fetch_assoc()['route'] ?? '';
    $q->close();

    // insert new booking
    $stmt = $conn->prepare("
        INSERT INTO {$table}
            (user_id, route, time_slot, created_at, status)
        VALUES (?, ?, ?, NOW(), 'confirmed')
    ");
    $stmt->bind_param("iss", $_SESSION['user_id'], $route, $newTime);
    $stmt->execute();
    $stmt->close();

    $_SESSION['flash_message'] = "Rebooked {$type} bus for {$newTime}.";
    header("Location: rebook.php");
    exit;
}

// Fetch latest bookings
$user_id  = $_SESSION['user_id'];
$bookings = ['arrival'=>null, 'departure'=>null];
foreach (['arrival','departure'] as $type) {
    $tbl = "{$type}_info";
    $stmt = $conn->prepare("
        SELECT route, time_slot, status
          FROM {$tbl}
         WHERE user_id = ?
         ORDER BY id DESC
         LIMIT 1
    ");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $bookings[$type] = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
}

// Show slot‐select form if rebooking
if (isset($_GET['rebook_type'])) {
    $type     = $_GET['rebook_type'];
    $current  = $bookings[$type]['time_slot'] ?? null;
    $slotsArr = ($type === 'arrival') ? $arrivalSlots : $departureSlots;
    $nextSlots = [];

    if ($current) {
        $now = DateTime::createFromFormat('g:i A', $current);
        foreach ($slotsArr as $s) {
            $dt = DateTime::createFromFormat('g:i A', $s);
            if ($dt && ($dt >= $now)) {
                $nextSlots[] = $s;
            }
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Select New <?= ucfirst($type) ?> Slot</title>
      <link rel="stylesheet" href="bush.css">
    </head>
    <body>

    <?php if (isset($_SESSION['flash_message'])): ?>
      <script>alert("<?= addslashes($_SESSION['flash_message']) ?>");</script>
      <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <header>
      <div class="logo">
        <img src="gub.jpg" style="border-radius:5px" alt="Company Logo">
        <span class="brand-name">Green University of Bangladesh</span>
      </div>
      <nav>
        <ul>
          <li><a href="https://green.edu.bd/">GUB official site</a></li>
          <li><a href="#" class="cta-button">Schedule Your Bus</a></li>
          <li><a href="#buses">FAQ</a></li>
          <li><a href="#pages">Complaint</a></li>
          <li><a href="https://green.edu.bd/transportation">Transportation</a></li>
        </ul>
      </nav>
      <div class="contact-info">
        <span>Working Hours: Mon-Fri 6.30am-5pm</span>
        <span>Call Center: 01771257194</span>
      </div>
    </header>

    <main>
      <section class="hero">
        <h1 style="font-size:50px;">Rebook <?= ucfirst($type) ?> Slot</h1>
        <p style="font-size:30px;">Select a new time (including your cancelled slot).</p>
      </section>

      <section class="booking-summary-wrapper">
        <div class="booking-summary-box">
          <?php if ($nextSlots): ?>
            <form method="POST" action="rebook.php">
              <input type="hidden" name="rebook_type" value="<?= $type ?>">
              <label for="new_time">Available Slots:</label>
              <select name="new_time" id="new_time">
                <?php foreach ($nextSlots as $s): ?>
                  <option><?= htmlspecialchars($s) ?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="rebook-button">Rebook</button>
            </form>
          <?php else: ?>
            <p>No slots available at or after <?= htmlspecialchars($current) ?>.</p>
          <?php endif; ?>
          <p><a href="rebook.php">Back to My Bookings</a></p>
        </div>
      </section>

      <div class="image-stack">
        <img src="busgoodview.jpg" alt="Bus Image 1" class="image active">
        <img src="transport2.jpg" alt="Bus Image 2" class="image">
      </div>
    </main>

    <footer>
      <div class="footer-container">
        <div class="find-us">
          <h3>Find Us</h3>
          <p>Purbachal American City, Kanchan, Rupganj, Narayanganj-1461, Dhaka, Bangladesh</p>
          <p>+880 9614482482</p>
          <p>01324713503, 01324713502, 01324713504, 01324713505, 01324713506, 01324713507, 01324713508</p>
          <p><a href="mailto:admission@green.edu.bd">admission@green.edu.bd</a></p>
        </div>
        <div class="departmental-sites">
          <h3>Departmental Sites</h3>
          <ul>
            <li>Computer Science And Engineering</li>
            <li>Software Engineering</li>
            <li>Electrical And Electronic Engineering</li>
            <li>English</li>
            <li>Journalism And Media Communication</li>
          </ul>
        </div>
        <div class="useful-links">
          <h3>Useful Links</h3>
          <ul><li>Forms</li></ul>
        </div>
        <div class="get-in-touch">
          <h3>Get in touch</h3>
          <ul>
            <li>Contact Us</li>
            <li>Campus Map</li>
            <li>Photo Gallery</li>
          </ul>
        </div>
        <div class="social-links">
          <h3>Follow Us</h3>
          <ul>
            <li><a href="https://www.facebook.com/greenuniversitybd/">Facebook</a></li>
            <li><a href="https://x.com/i/flow/login?redirect_after_login=%2Fgreenvarsity">Twitter</a></li>
            <li><a href="https://www.linkedin.com/school/greenuniversity">LinkedIn</a></li>
          </ul>
        </div>
      </div>
      <p>&copy; 2003-2024 Green University of Bangladesh. All Rights Reserved.</p>
      <p>&copy; Developed by TM group &amp; corporation</p>
    </footer>

    <script src="bush.js"></script>
    </body>
    </html>
    <?php
    exit;
}

// Render main My-Bookings page
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Booked Slot | Book Our Bus</title>
  <link rel="stylesheet" href="bush.css">
</head>
<body>

<?php if (isset($_SESSION['flash_message'])): ?>
  <script>alert("<?= addslashes($_SESSION['flash_message']) ?>");</script>
  <?php unset($_SESSION['flash_message']); ?>
<?php endif; ?>

<header>
  <div class="logo">
    <img src="gub.jpg" style="border-radius:5px" alt="Company Logo">
    <span class="brand-name">Green University of Bangladesh</span>
  </div>
  <nav>
    <ul>
      <li><a href="https://green.edu.bd/">GUB official site</a></li>
      <li><a href="#" class="cta-button">Schedule Your Bus</a></li>
      <li><a href="#buses">FAQ</a></li>
      <li><a href="#pages">Complaint</a></li>
      <li><a href="https://green.edu.bd/transportation">Transportation</a></li>
    </ul>
  </nav>
  <div class="contact-info">
    <span>Working Hours: Mon-Fri 6.30am-5pm</span>
    <span>Call Center: 01771257194</span>
  </div>
</header>

<main>
  <section class="hero">
    <h1 style="font-size:50px;">Your Booked Slot</h1>
    <p style="font-size:30px;">
      Your current bookings. Cancel or report a miss to enable rebooking.
    </p>
  </section>

  <section class="booking-summary-wrapper">
    <div class="booking-summary-box">
      <h2>Your Current Bookings:</h2>

      <!-- Arrival -->
      <?php if ($bookings['arrival']): ?>
        <p>
          🟢 <strong>Arrival Via:</strong>
          <?= htmlspecialchars($bookings['arrival']['route']) ?> at
          <?= htmlspecialchars($bookings['arrival']['time_slot']) ?>
          <?php if ($bookings['arrival']['status'] === 'cancelled'): ?>
            <em>(cancelled)</em>
          <?php endif; ?>
        </p>
        <?php if ($bookings['arrival']['status'] !== 'cancelled'): ?>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="cancel_type" value="arrival">
            <button type="submit" class="cancel-button">Cancel</button>
          </form>
          <button onclick="location='?missed_type=arrival';" class="missed-button">
            I missed this bus
          </button>
        <?php else: ?>
          <button onclick="location='rebook.php?rebook_type=arrival';" class="rebook-button">
            Rebook
          </button>
          <select class="contact-button" onchange="if(this.value) location='tel:'+this.value">
            <option value="">Contact Manager</option>
            <?php foreach ($managerPhones as $ph): ?>
              <option value="<?= htmlspecialchars($ph) ?>"><?= htmlspecialchars($ph) ?></option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
      <?php else: ?>
        <p>⚠️ No arrival booking found.</p>
      <?php endif; ?>

      <!-- Departure -->
      <?php if ($bookings['departure']): ?>
        <p>
          🔵 <strong>Will Depart For:</strong>
          <?= htmlspecialchars($bookings['departure']['route']) ?> at
          <?= htmlspecialchars($bookings['departure']['time_slot']) ?>
          <?php if ($bookings['departure']['status'] === 'cancelled'): ?>
            <em>(cancelled)</em>
          <?php endif; ?>
        </p>
        <?php if ($bookings['departure']['status'] !== 'cancelled'): ?>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="cancel_type" value="departure">
            <button type="submit" class="cancel-button">Cancel</button>
          </form>
          <button onclick="location='?missed_type=departure';" class="missed-button">
            I missed this bus
          </button>
        <?php else: ?>
          <button onclick="location='rebook.php?rebook_type=departure';" class="rebook-button">
            Rebook
          </button>
          <select class="contact-button" onchange="if(this.value) location='tel:'+this.value">
            <option value="">Contact Manager</option>
            <?php foreach ($managerPhones as $ph): ?>
              <option value="<?= htmlspecialchars($ph) ?>"><?= htmlspecialchars($ph) ?></option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
      <?php else: ?>
        <p>⚠️ No departure booking found.</p>
      <?php endif; ?>

    </div>
  </section>

  <div class="image-stack">
    <img src="busgoodview.jpg" alt="Bus Image 1" class="image active">
    <img src="transport2.jpg" alt="Bus Image 2" class="image">
  </div>
</main>

<footer>
  <div class="footer-container">
    <div class="find-us">
      <h3>Find Us</h3>
      <p>Purbachal American City, Kanchan, Rupganj, Narayanganj-1461, Dhaka, Bangladesh</p>
      <p>+880 9614482482</p>
      <p>01324713503, 01324713502, 01324713504, 01324713505, 01324713506, 01324713507, 01324713508</p>
      <p><a href="mailto:admission@green.edu.bd">admission@green.edu.bd</a></p>
    </div>
    <div class="departmental-sites">
      <h3>Departmental Sites</h3>
      <ul>
        <li>Computer Science And Engineering</li>
        <li>Software Engineering</li>
        <li>Electrical And Electronic Engineering</li>
        <li>English</li>
        <li>Journalism And Media Communication</li>
      </ul>
    </div>
    <div class="useful-links">
      <h3>Useful Links</h3>
      <ul><li>Forms</li></ul>
    </div>
    <div class="get-in-touch">
      <h3>Get in touch</h3>
      <ul>
        <li>Contact Us</li>
        <li>Campus Map</li>
        <li>Photo Gallery</li>
      </ul>
    </div>
    <div class="social-links">
      <h3>Follow Us</h3>
      <ul>
        <li><a href="https://www.facebook.com/greenuniversitybd/">Facebook</a></li>
        <li><a href="https://x.com/i/flow/login?redirect_after_login=%2Fgreenvarsity">Twitter</a></li>
        <li><a href="https://www.linkedin.com/school/greenuniversity">LinkedIn</a></li>
      </ul>
    </div>
  </div>
  <p>&copy; 2003-2024 Green University of Bangladesh. All Rights Reserved.</p>
  <p>&copy; Developed by TM group &amp; corporation</p>
</footer>

<script src="bush.js"></script>
</body>
</html>
