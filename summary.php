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

// --- 2. โหลดข้อมูลผู้ใช้จาก data.json ---
$registeredUsers = [];
if (file_exists($userFile)) {
    // โหลดข้อมูลผู้ใช้ทั้งหมด
    $registeredUsers = json_decode(file_get_contents($userFile), true);
    /*
    if (!is_array($registeredUsers)) {
        $registeredUsers = [];
    }
    */
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // รับค่าและทำความสะอาดข้อมูล
    $name = htmlspecialchars($_POST['name'] ?? '');
    $q1 = htmlspecialchars($_POST['q1'] ?? 0);
    $q2 = htmlspecialchars($_POST['q2'] ?? 0);
    $q3 = htmlspecialchars($_POST['q3'] ?? 0);
    $q4 = htmlspecialchars($_POST['q4'] ?? 0);
    $q5 = htmlspecialchars($_POST['q5'] ?? 0);
    $comments = htmlspecialchars($_POST['Comments'] ?? '-');

    $isUserFound = false;
    foreach ($registeredUsers as $user) {
        // ใช้ trim() เพื่อตัดช่องว่างหน้า/หลังชื่อที่กรอก
        // ตรวจสอบกับคีย์ 'username' ใน data.json
        if (isset($user['username']) && trim($user['username']) === trim($name)) {
            $isUserFound = true;
            break; 
        }
    }

    if (!$isUserFound) {
        // หากไม่พบชื่อผู้ใช้
        $_SESSION['flash_message'] = "⚠️ ไม่พบชื่อในระบบ กรุณาตรวจสอบว่าคุณได้ลงทะเบียนแล้วหรือไม่";
        header("Location: feedback.php"); 
        exit;
    }

    // *** ส่วนที่แก้ไข: ลบรายการ Feedback เก่าที่ซ้ำซ้อนออกไป ***
    foreach($currentData['reviews'] as $key => $review) {
        // ตรวจสอบว่าชื่อใน review ซ้ำกับชื่อที่ส่งมาหรือไม่
        if (isset($review['name']) && trim($review['name']) === trim($name)) {
            // ลบรายการ Feedback เก่าออกโดยใช้ Key
            unset($currentData['reviews'][$key]);
        }
    }

    // *** สำคัญ: จัดเรียง Index ของ Array ใหม่หลังจากลบ ***
    // (เพื่อให้ Array เริ่มต้นจาก 0 ใหม่ และป้องกันปัญหาในการนับจำนวน Record)
    $currentData['reviews'] = array_values($currentData['reviews']);

    // --- 4. บันทึก Feedback (ถ้าตรวจสอบผ่าน) ---
    $alert_message = 'คำตอบของคุณถูกส่งออกไปเรียบร้อย!';

    $currentData['reviews'][] = [
        'name' => $name, // เพิ่ม name เข้าไปในข้อมูล feedback
        'q1' => (int)$q1, // แปลงเป็น int เพื่อให้การคำนวณถูกต้อง
        'q2' => (int)$q2,
        'q3' => (int)$q3, 
        'q4' => (int)$q4, 
        'q5' => (int)$q5, 
        'comments' => $comments
    ];

    $totalRecords = count($currentData['reviews']);
    $sumQ1 = 0; $sumQ2 = 0; $sumQ3 = 0; $sumQ4 = 0; $sumQ5 = 0;

    foreach ($currentData['reviews'] as $row) {
        // คำนวณผลรวมใหม่ทั้งหมด
        $sumQ1 += $row['q1'] ?? 0;
        $sumQ2 += $row['q2'] ?? 0;
        $sumQ3 += $row['q3'] ?? 0;
        $sumQ4 += $row['q4'] ?? 0;
        $sumQ5 += $row['q5'] ?? 0;
    }

    if ($totalRecords > 0) {
        $avgQ1 = number_format($sumQ1 / $totalRecords, 3);
        $avgQ2 = number_format($sumQ2 / $totalRecords, 3);
        $avgQ3 = number_format($sumQ3 / $totalRecords, 3);
        $avgQ4 = number_format($sumQ4 / $totalRecords, 3);
        $avgQ5 = number_format($sumQ5 / $totalRecords, 3);
    } else {
        $avgQ1 = $avgQ2 = $avgQ3 = $avgQ4 = $avgQ5 = 0;
    }

    $finalOutput = [
        'summary' => [
            'total_responses' => $totalRecords,
            'last_updated' => date('Y-m-d'),
            'averages' => [
                'q1' => $avgQ1,
                'q2' => $avgQ2,
                'q3' => $avgQ3,
                'q4' => $avgQ4,
                'q5' => $avgQ5
            ]
        ],
        'reviews' => $currentData['reviews']
    ];
    
    $_SESSION['flash_message'] = $alert_message;
    // ใช้ JSON_UNESCAPED_UNICODE เพื่อให้ภาษาไทยอ่านได้
    file_put_contents($dataFile, json_encode($finalOutput, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // POST-Redirect-GET
    header("Location: feedback.php"); 
    exit; 
}

$flash_message = '';
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

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
    <div class="feedback-box container col-md-6 py-5 mb-3">
      
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Your custom script file -->
    <script src="script.js"></script>
  </body>
</html>