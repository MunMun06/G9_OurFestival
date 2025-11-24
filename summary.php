<?php
session_start();

$dataFile = 'feedbackdata.json';
$userFile = 'data.json'; // ไฟล์ข้อมูลผู้ใช้
$alert_message = '';

$currentData = [
    'summary' => [],
    'reviews' => []
];

// --- 1. โหลดข้อมูล Feedback เดิม ---
if (file_exists($dataFile)) {
    $loaded = json_decode(file_get_contents($dataFile), true);
    
    if (is_array($loaded)) {
        if (isset($loaded[0])) {
            $currentData['reviews'] = $loaded;
        } 
        elseif (isset($loaded['reviews'])) {
            $currentData = $loaded;
        }
    }
}

// แยกข้อมูล Summary และ Reviews เพื่อใช้งานง่ายขึ้น
$summary = $currentData['summary'] ?? [];
$reviews = $currentData['reviews'] ?? [];

// --- 2. โหลดข้อมูลผู้ใช้จาก data.json ---
$registeredUsers = [];
if (file_exists($userFile)) {
    // โหลดข้อมูลผู้ใช้ทั้งหมด
    $registeredUsers = json_decode(file_get_contents($userFile), true);
    if (!is_array($registeredUsers)) {
        $registeredUsers = [];
    }

}
/*
$flash_message = '';
// ดึงข้อความแจ้งเตือนที่ถูกส่งมาจากการ Redirect จาก feedback.php
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}
*/

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Form</title>
    <link rel="icon" type="image/png" href="resources/browser_icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Link to the external CSS file -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Athiti:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Athiti:wght@200;300;400;500;600;700&family=Creepster&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/feedback_style.css">
    <link rel="stylesheet" href="css/homePage_style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  </head>
  <body style="background-image: url('resources/Background.jpg')">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg mb-3 navbar-dark bg-dark fixed-top">
    <div class="container">
      <a class="navbar-brand" href="index.html" id="navbrand">HALLOW<span id="thai-brand">วัด งาน</span>WEEN</a>
      <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navMenu"><span
          class="navbar-toggler-icon"></span></button>
      <div id="navMenu" class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a href="index.html" class="nav-link">Home</a></li>
          <li class="nav-item dropdown d-flex align-items-lg-center">
            <a class="nav-link pe-0" href="boothDirectory.html">Booth</a>
            <a class="nav-link dropdown-toggle dropdown-toggle-split ps-2" href="#" role="button"
              data-bs-toggle="dropdown" aria-expanded="false" id="boothDropdown"><span class="visually-hidden">Toggle
                Dropdown</span></a>
            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="boothDropdown" id="dropdown-item">
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
    
    <!-- Feedback -->
    <div class="feedback-box container col-md-8 py-5 mb-5">
      <h1 class="text-center mb-5 text-warning" style="font-family: 'Creepster', cursive;">FEEDBACK SUMMARY</h1>

        <div class="card bg-dark text-light p-4 mb-5 mx-auto" style="max-width: 800px; border: 3px solid #CE642A;">
            <h3 class="card-title text-center text-warning mb-4" style="font-family: 'Creepster', cursive;">Overall Averages</h3>
            <p class="mb-2"><strong>Total Responses:</strong> <span class="badge bg-warning text-dark fs-6"><?= $summary['total_responses'] ?? 0 ?></span></p>
            <p class="mb-4 small text-end">Last Updated: <?= $summary['last_updated'] ?? 'N/A' ?></p>
            
            <div class="table-responsive">
                <table class="table table-dark table-striped table-bordered text-center">
                    <thead>
                        <tr>
                            <th scope="col" class="text-start text-warning">Question</th>
                            <th scope="col" class="text-warning">Average Score (1-5)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start">1. ความสนุกโดยรวมของกิจกรรม</td>
                            <td><span class="badge bg-light text-dark fs-6"><?= $summary['averages']['q1'] ?? '0.000' ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-start">2. ความเหมาะสมของดนตรี / เสียง / แสง</td>
                            <td><span class="badge bg-light text-dark fs-6"><?= $summary['averages']['q2'] ?? '0.000' ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-start">3. ความสะดวกในการเข้าร่วมงาน</td>
                            <td><span class="badge bg-light text-dark fs-6"><?= $summary['averages']['q3'] ?? '0.000' ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-start">4. ความเป็นมิตรของทีมงาน / สตาฟ</td>
                            <td><span class="badge bg-light text-dark fs-6"><?= $summary['averages']['q4'] ?? '0.000' ?></span></td>
                        </tr>
                        <tr>
                            <td class="text-start">5. ความคุ้มค่าของเวลาและความคาดหวัง</td>
                            <td><span class="badge bg-light text-dark fs-6"><?= $summary['averages']['q5'] ?? '0.000' ?></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <h2 class="text-center mb-4 text-light" style="font-family: 'Creepster', cursive;">Individual Reviews (<?= count($reviews) ?>)</h2>
        
        <?php if (empty($reviews)): ?> 
            <p class="text-center text-light">No feedback submitted yet.</p>
        <?php else: ?>
            <?php foreach ($reviews as $review): 
                // กำหนดสีของ Score Badge ตามคะแนน (4-5 = Success, 3 = Warning, <3 = Danger)
                $q1_color = ($review['q1'] >= 4) ? 'success' : (($review['q1'] >= 3) ? 'warning' : 'danger');
                $q2_color = ($review['q2'] >= 4) ? 'success' : (($review['q2'] >= 3) ? 'warning' : 'danger');
                $q3_color = ($review['q3'] >= 4) ? 'success' : (($review['q3'] >= 3) ? 'warning' : 'danger');
                $q4_color = ($review['q4'] >= 4) ? 'success' : (($review['q4'] >= 3) ? 'warning' : 'danger');
                $q5_color = ($review['q5'] >= 4) ? 'success' : (($review['q5'] >= 3) ? 'warning' : 'danger');
            ?>
                <div class="card bg-dark text-light p-3 mb-4 mx-auto" style="max-width: 800px; border: 1px solid #CE642A;">
                    <div class="card-body p-2">
                        <h5 class="card-title text-warning mb-3" style="font-family: 'Creepster', cursive; border-bottom: 1px dashed #CE642A; padding-bottom: 5px;">
                            Name: <?= htmlspecialchars($review['name'] ?? 'Anonymous')//ถ้าไม่มีชื่อ แสดง Anonymous ?>
                        </h5>
                        <p class="card-text small text-white mb-2">
                            <span class="badge bg-primary">1. Fun:</span> <span class="badge bg-<?= $q1_color ?>"><?= $review['q1'] ?? 0 ?>/5</span> | 
                            <span class="badge bg-primary">2. Sound/Light:</span> <span class="badge bg-<?= $q2_color ?>"><?= $review['q2'] ?? 0 ?>/5</span> | 
                            <span class="badge bg-primary">3. Convenience:</span> <span class="badge bg-<?= $q3_color ?>"><?= $review['q3'] ?? 0 ?>/5</span> | 
                            <span class="badge bg-primary">4. Staff:</span> <span class="badge bg-<?= $q4_color ?>"><?= $review['q4'] ?? 0 ?>/5</span> | 
                            <span class="badge bg-primary">5. Value:</span> <span class="badge bg-<?= $q5_color ?>"><?= $review['q5'] ?? 0 ?>/5</span>
                        </p>
                        <h6 class="mt-3 text-warning" style="font-family: 'Creepster', cursive;">Additional Comments:</h6>
                        <p class="border p-2 bg-secondary bg-opacity-10 rounded text-light"><?= nl2br(htmlspecialchars($review['comments'] ?? '-'))//คงการขึ้นบรรทัดใหม่ของ comment ไว้?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

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

    <?php if ($flash_message !== ''): ?>
      <script>
        // แสดง Alert ข้อความที่มาจาก feedback.php (เช่น "คำตอบของคุณถูกส่งออกไปเรียบร้อย!")
        alert("<?php echo htmlspecialchars($flash_message, ENT_QUOTES, 'UTF-8'); ?>");
      </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="script.js"></script>
  </body>
</html>