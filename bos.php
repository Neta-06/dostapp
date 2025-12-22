<?php
session_start();
require_once "config/database.php";
require_once "config/functions.php";

$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'tr';
$translations = loadLanguage($lang);

if (!isset($_SESSION['kullanici_id'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

$kullanici_id = $_SESSION['kullanici_id'];

// Kullanıcı bilgilerini al
$user_query = "SELECT * FROM kullanicilar WHERE id = :kullanici_id";
$user_stmt = $db->prepare($user_query);
$user_stmt->bindParam(":kullanici_id", $kullanici_id);
$user_stmt->execute();
$user_profile = $user_stmt->fetch(PDO::FETCH_ASSOC);

$error = '';
$success = "";
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations['kayit_ol']; ?> - <?php echo $translations['site_adi']; ?></title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <script src="js/bootstrap.bundle.min.js"></script>

  <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/nav.css">
    <style>    

    </style>

<body class="bg-light">
  <!-- Top Navbar -->
  <nav class="top-navbar">
    <div class="nav-left">
       <a class="navbar-brand" href="giris.php">
      <img src="img/friends.png" alt="Logo"> <?php echo $translations['site_adi']; ?>
    </a>
    </div>
    
    <div class="nav-right">
        <button class="location-btn" onclick="getCurrentLocation()">
        <i class="fas fa-location-arrow"></i>
        <span><?php echo $translations['konum']; ?></span>
      </button>
      <img src="<?php echo !empty($user_profile['profil_resmi']) ? $user_profile['profil_resmi'] : 'https://cdn-icons-png.flaticon.com/512/847/847969.png'; ?>" 
           alt="Profil" 
           class="profile-img"
           onerror="this.src='https://cdn-icons-png.flaticon.com/512/847/847969.png'">
      <button class="menu-btn" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
        <i class="fas fa-bars"></i>
      </button>
    </div>
  </nav>

  <div class="app-container">
    <!-- Floating Menü Butonu -->
    <button class="menu-floating-btn" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
      <i class="fas fa-bars"></i>
    </button>

    <!-- OFFCANVAS MENU -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="sidebar">
    <div class="offcanvas-header">
      <div class="d-flex align-items-center">
        <img src="<?php echo !empty($user_profile['profil_resmi']) ? $user_profile['profil_resmi'] : 'https://cdn-icons-png.flaticon.com/512/847/847969.png'; ?>" 
             width="50" 
             height="50" 
             class="rounded-circle me-3"
             onerror="this.src='https://cdn-icons-png.flaticon.com/512/847/847969.png'">
        <div>
          <h6 class="mb-0"><?php echo $user_profile['ad'] . ' ' . $user_profile['soyad']; ?></h6>
          <small class="text-muted"><?php echo $user_profile['cep_telefonu']; ?></small>
        </div>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
    </div>

    <div class="offcanvas-body">
      <div class="list-group list-group-flush">
        <a href="#" class="list-group-item list-group-item-action active " onclick="getCurrentLocation()">
          <i class="fas fa-map-marker-alt"></i> <?php echo $translations['neredeyim']; ?>
        </a>
        <a href="dostlar.php" class="list-group-item list-group-item-action">
          <i class="fas fa-users"></i> <?php echo $translations['arkadaslar']; ?>
        </a>
        <a href="bos.php" class="list-group-item list-group-item-action">
          <i class="fas fa-compass"></i> <?php echo $translations['etrafimdakiler']; ?> 
        </a>
        <a href="#" class="list-group-item list-group-item-action">
          <i class="fas fa-user-plus"></i> <?php echo $translations['ardadas_edin']; ?> 
        </a>
        <a href="#" class="list-group-item list-group-item-action">
          <i class="fas fa-bell"></i> <?php echo $translations['bildirimler']; ?> &nbsp;&nbsp; 
          <span class="badge bg-danger float-end">3</span>
        </a>
        <a href="#" class="list-group-item list-group-item-action">
          <i class="fas fa-cog"></i><?php echo $translations['ayarlar']; ?>
        </a>
       <!-- <a href="#" class="list-group-item list-group-item-action">
          <i class="fas fa-moon"></i> Karanlık Mod
        </a>-->
        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
          <i class="fas fa-sign-out-alt"></i>  <?php echo $translations['cikis']; ?>
        </a>
      </div>
    </div>
  </div>
  </div>

<div class="container">


<article>
<p>dsad</p>
</article>

<!-- sonsuz scroll -->
<script src="js/load-more.js"></script>
<aside> 
  <p>sdfsdafsdaf fsdfsdfsdaf sdfsdafsdaf sadfsdafsdaf sdafsdaffsdafsdafsdaf sdafsda</p> 
</aside>
</div>
</body>

</head>
</html>
