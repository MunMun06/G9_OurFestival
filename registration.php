<?php
session_start(); // **เพิ่มบรรทัดนี้เพื่อเริ่มใช้งาน Session**

// Simple self-posting PHP page that renders the form and the result.
$dataFile = 'data.json';
$resultHtml = '';
$pass = 1;
$alertMessage = ''; // ตัวแปรสำหรับเก็บข้อความแจ้งเตือน

// Load existing data from JSON file
if (file_exists($dataFile)) {
  $records = json_decode(file_get_contents($dataFile), true) ?? [];
} else {
  $records = [];
}

$participants = count($records);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username  = trim($_POST['username']  ?? '');
  $phonenumber = trim($_POST['phonenumber'] ?? '');
  $email  = trim($_POST['email']  ?? '');

  foreach ($records as $user) {
    $existing_username = trim($user['username'] ?? '');
    $existing_phonenumber = trim($user['phonenumber'] ?? '');
    $existing_email = trim($user['email'] ?? '');

      // Check for duplicate
    if ($username !== '' && $username === $existing_username) {
      $alertMessage = "Error: Registration failed. Username duplicated.";
      $pass = 0;
      break; // ออกจาก loop เมื่อพบ Duplicate
    }

      if ($phonenumber !== '' && $phonenumber === $existing_phonenumber) {
      $alertMessage = "Error: Registration failed. Phone number duplicated.";
      $pass = 0;
      break;
    }

    if ($email !== '' && $email === $existing_email) {
      $alertMessage = "Error: Registration failed. Email duplicated.";
      $pass = 0;
      break;
    }
  }

  // ถ้าผ่านการตรวจสอบทั้งหมด
  if ($username && $phonenumber && $email && $pass === 1) {
    $records[] = ['username' => $username, 'phonenumber' => $phonenumber, 'email' => $email];
    file_put_contents($dataFile, json_encode($records, JSON_PRETTY_PRINT));
    $_SESSION['flash_message'] = 'success';
  } else {
      $_SESSION['flash_message'] = $alertMessage;
  }
  
  // POST-Redirect-GET: Redirect ไปที่หน้าเดิมเพื่อล้างข้อมูล POST
  header("Location: registration.php"); 
  exit; // **สำคัญมาก: ต้องใช้ exit; หลัง header()**
}

$flash_message = '';
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']); // ล้าง Session เพื่อให้แสดง Alert เพียงครั้งเดียว
}

?>
<!DOCTYPE html>
<html lang="th" translate="yes">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="icon" type="image/png" href="resources/browser_icon.png">
    <link rel="stylesheet" href="css/registeration_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  </head>
  <body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
      <div class="container">
        <a
          class="navbar-brand"
          href="index.html"
          id="navbrand"
        >HALLOW<span
            id="thai-brand"
          >วัด งาน</span>WEEN</a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navMenu">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div id="navMenu" class="collapse navbar-collapse">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a href="index.html" class="nav-link">Home</a></li>
            <li class="nav-item dropdown d-flex align-items-lg-center">
              <a class="nav-link pe-0" href="boothDirectory.html">Booth</a>
              <a class="nav-link dropdown-toggle dropdown-toggle-split ps-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="boothDropdown">
                <span class="visually-hidden">Toggle Dropdown</span>
              </a>
              <ul
                class="dropdown-menu dropdown-menu-dark"
                aria-labelledby="boothDropdown"
                id="dropdown-item"
              >
                <li><a class="dropdown-item" href="booth1.html">Food</a></li>
                <li><a class="dropdown-item" href="booth2.html">Desert</a></li>
                <li><a class="dropdown-item" href="booth3.html">Costume</a></li>
                <li><a class="dropdown-item" href="booth4.html">Amusement</a></li>
              </ul>
            </li>
            <li class="nav-item"><a href="registration.php" class="nav-link">Register</a></li>
            <li class="nav-item"><a href="feedback.php" class="nav-link">Feedback</a></li>
          </ul>
        </div>
      </div>
    </nav>
    <!--register-->
    <section class="register-wrap" style="background-image: url('resources/Background.jpg'); background-size: cover;
  background-position: center;">
      <div class="overlay" id="overlay"></div>
      <div class="register-box">
        <div class="register-content">
          <h2>Join our spooky night!</h2>
          <div class="form-box">
            <form method="POST">
              <input type="text" pattern="^[A-Za-z]+ [A-Za-z]+$" name="username" minlength="3" maxlength="40" placeholder="Fullname" required>
              <input type="tel" pattern="^0\d{9}" title="*error input" name="phonenumber" maxlength="10" placeholder="Phone number" required>
              <input type="email" name="email" placeholder="Email">
              <div class="submit">
                <button type="submit">Join</button>
              </div>
            </form>
          </div>
          <h4>Current participants : <?=$participants?></h4>
        </div>
      </div>
    </section>
    <div id="popup-thx" class="thx-wrap">
      <div class="thx-box">
        <h2>ENJOY YOUR SPOOKY NIGHT !!</h2>
        <div class="link">
          <a href="index.html">home</a>
          <a href="boothDirectory.html">booth</a>
        </div>
        <button onclick="toggle_thx(event)">x</button>
      </div>
    </div>

    <!-- Footer -->
    <footer class="footer-custom">
      <div class="container text-center">
        <div class="social-icons">
          <a href="#"><i class="bi bi-instagram"></i></a>
          <a href="#"><i class="bi bi-facebook"></i></a>
          <a href="#"><i class="bi bi-tiktok"></i></a>
        </div>
      </div>
    </footer>

    <?php if ($flash_message === 'success'): ?>
      <script>
        document.addEventListener('DOMContentLoaded', () => {
            // เรียกฟังก์ชันเพื่อแสดง Popup ความสำเร็จ
            const thxPopup = document.getElementById('popup-thx');
            const overlay = document.getElementById('overlay');
            if (thxPopup && overlay) {
                thxPopup.classList.add('show');
                overlay.classList.add('show');
            }
        });
      </script>
    <?php elseif ($flash_message !== ''): ?>
      <script>
        // แสดง Alert สำหรับข้อผิดพลาด (Duplicate)
        alert("<?php echo htmlspecialchars($flash_message, ENT_QUOTES, 'UTF-8'); ?>");
      </script>
    <?php endif; ?>

    <script>
      function toggle_thx(e){
            e.preventDefault();
            const form = document.getElementById('popup-thx');
            const overlay = document.getElementById('overlay');
            form.classList.toggle('show');
            overlay.classList.toggle('show')
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
