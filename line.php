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

$sql = "SELECT p.id,
               p.media_url,
               u.ad, u.soyad, u.yas, u.sehir, u.profil_resmi,
               (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as begeni_sayisi,
               EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_id = :uid) as ben_begendim
        FROM posts p
        JOIN kullanicilar u ON u.id = p.user_id
        WHERE p.media_url <> ''
        ORDER BY p.id DESC";

$stmt = $db->prepare($sql);
$stmt->bindValue(':uid', $kullanici_id, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    <style>    
    body {
      background-color: #c5a7acff;
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
     
    }

    .container {
        display: flex;              /* yan yana gelsinler */
        gap: 20px;                  /* aralarındaki boşluk */
        padding: 0 15px;            /* istersen sağ-sol ufak boşluk */
    }

    aside {
        flex: 0 0 30%;              /* sabit %30 genişlik */
        max-width: 30%;
        background: #fff;
        border-radius: 25px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
    }

    article {
        flex: 0 0 70%; /* %70 - gap kadar */
        max-width: 70%;
        background: #fff;
        border-radius: 25px;
        padding: 5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
    }

    /* Navbar Stilleri */
    .top-navbar {
      background: linear-gradient(90deg, #637e4eff, #00bcd4);
      height: 60px;
      padding: 0 15px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      z-index: 1000;
      position: relative;
      margin-bottom: 30px; /* nav kısmınn alt boşluğunu ayarlar*/

    }

    .nav-left {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .nav-right {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .location-btn {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      padding: 8px 15px;
      border-radius: 20px;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .location-btn:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-1px);
    }

    .profile-img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: 2px solid white;
      cursor: pointer;
      object-fit: cover;
    }

    .menu-btn {
      background: none;
      border: none;
      color: white;
      font-size: 20px;
      cursor: pointer;
      padding: 8px;
      border-radius: 50%;
      transition: all 0.3s ease;
    }

    .menu-btn:hover {
      background: rgba(255, 255, 255, 0.2);
    }


    /* Card Styles */
    .card {
        border: none;
        border-radius: 25px;
        box-shadow: var(--shadow-light);

    }

    .card-header {
        background: var(--gradient-primary);
        color: white;
        padding: 1.5rem;
        border-bottom: none;
        border-top-left-radius: 25px;
        border-top-right-radius: 25px;
        
    }

    .card-body {
        padding: 2rem;
    }

     /* Offcanvas */
    .offcanvas {
      background-color: #ffffff;
      border-right: 1px solid #ddd;
      width: 400px;
    }

    .offcanvas-header {
      border-bottom: 1px solid #eee;
      background: #f7f7f7;
      padding: 15px;
    }

    .offcanvas-body {
      padding: 0;
    }

    .list-group-item {
      font-size: 16px;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      padding: 15px 20px;
      border: none;
      border-bottom: 1px solid #eee;
    }

    .list-group-item i {
      margin-right: 12px;
      font-size: 18px;
      width: 24px;
      text-align: center;
    }

    .list-group-item:hover,
    .list-group-item.active {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    
    .menu-floating-btn {
      position: fixed;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #637e4eff, #00bcd4);
      border: none;
      border-radius: 50%;
      color: white;
      font-size: 22px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      z-index: 1000;
      display: none;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .navbar-brand {
      position: absolute;
      left: 3%;
      transform: translateX(-6%);
      color: white !important;
      font-weight: 100;
      display: flex;
      align-items: center;
    }

    .navbar-brand img {
      height: 40px;
      border-radius: 8px;
      margin-right: 8px;
    }

     /* facebook yapısı buradan aşağı */

     .mob-feed{height:calc(100vh - 76px);overflow-y:auto;}
.mob-feed::-webkit-scrollbar{width:4px}
.mob-feed::-webkit-scrollbar-thumb{background:rgba(0,0,0,.25);border-radius:2px}

.mob-card{position:relative;margin-bottom:3px;background:#000;}
.mob-media{width:100%;aspect-ratio:1/1;object-fit:cover;display:block;}

/* ÜST BİLGİ */
.card-info{
  position:absolute;
  top:12px;
  left:12px;
  color:#fff;
  text-shadow:0 0 4px rgba(0,0,0,.7);
  line-height:1.3;
}
.info-name{font-weight:600;font-size:14px;}
.info-meta{font-size:12px;opacity:.9;}

/* BEĞENİ BADGE (alt orta) */
.begeni-badge{
  position:absolute;
  bottom:12px;left:50%;
  transform:translateX(-50%);
  background:rgba(0,0,0,.45);
  color:#fff;
  padding:4px 10px;
  border-radius:16px;
  font-size:13px;
  display:flex;align-items:center;gap:4px;
}

/* KALP ANİMASYONU */
.heart-overlay{
  position:absolute;
  top:50%;left:50%;
  transform:translate(-50%,-50%) scale(0);
  font-size:96px;
  color:#ff3040;
  opacity:0;
  pointer-events:none;
  text-shadow:0 0 16px rgba(0,0,0,.6);
}
.heart-overlay.show{
  animation: heartPop .55s ease-out;
}
@keyframes heartPop{
  0%{transform:translate(-50%,-50%) scale(0);opacity:1;}
  15%{transform:translate(-50%,-50%) scale(1.2);opacity:1;}
  100%{transform:translate(-50%,-50%) scale(1.5);opacity:0;}
}

     /* ---------- Mobil-feed ---------- */
.mob-feed{
  height: calc(100vh - 76px);
  overflow-y: auto;
  padding: 0 0 20px 0;
}
.mob-feed::-webkit-scrollbar{width:4px}
.mob-feed::-webkit-scrollbar-thumb{background:rgba(0,0,0,.25);border-radius:2px}

.mob-card{
  position: relative;
  margin-bottom: 3px;        /* resimler bitişik gibi */
  background: #000;
}
.mob-media{
  width: 100%;
  display: block;
  aspect-ratio: 1 / 1;      /* kare resim */
  object-fit: cover;
}

/* ---- Beğeni Kalp Animasyonu ---- */
.heart-overlay{
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%,-50%) scale(0);
  font-size: 96px;
  color: #fff;
  opacity: 0;
  pointer-events: none;
  text-shadow: 0 0 16px rgba(0,0,0,.6);
}
.heart-overlay.show{
  animation: heartPop .55s ease-out;
}
@keyframes heartPop{
  0%  {transform: translate(-50%,-50%) scale(0); opacity: 1;}
  15% {transform: translate(-50%,-50%) scale(1.2); opacity: 1;}
  100%{transform: translate(-50%,-50%) scale(1.5); opacity: 0;}
}
    .post-box{
     border:none;
     border-radius: 16px;
     overflow:hidden;
     box-shadow: 0 1px 3px rgba(0,0,0,.08);
    }

    .post-box .card-footer .btn{
    width:32%;                /* üç düğme eşit */
    border-radius: 8px;
    }
    .like-btn.liked{
    color:#1877f2;
    font-weight:600;
    }

        /*---------- Feed Container ----------*/
    .feed-scroll {
    height: calc(100vh - 76px);   /* 60px navbar + 16px üst boşluk */
    overflow-y: auto;
    padding-right: 4px;           /* kaydırma çubuğu sağa yapışmasın */
    }
    .feed-scroll::-webkit-scrollbar{
    width: 6px;
    }
    .feed-scroll::-webkit-scrollbar-thumb{
    background: rgba(0,0,0,.25);
    border-radius: 3px;
    }

    /*---------- Post Kart ----------*/
    .ig-card {
    background: #ffffff;
    border: 1px solid #dbdbdb;
    border-radius: 12px;
    margin-bottom: 24px;
    max-width: 480px;        /* Instagram'ın maksimum genişliği */
    margin-left: auto;
    margin-right: auto;
    }

    .ig-header {
    display: flex;
    align-items: center;
    padding: 14px 16px;
    }
    .ig-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    margin-right: 12px;
    object-fit: cover;
    }
    .ig-username {
    font-weight: 600;
    font-size: 14px;
    color: #262626;
    }

    .ig-media {
    width: 100%;
    display: block;
    }

    .ig-footer {
    padding: 0 16px 16px;
    }
    .ig-icons {
    display: flex;
    align-items: center;
    padding: 12px 0 8px;
    font-size: 24px;
    gap: 16px;
    }
    .ig-icons i {
    cursor: pointer;
    }
    .ig-icons .far.fa-heart:hover,
    .ig-icons .far.fa-comment:hover,
    .ig-icons .far.fa-share:hover {
    color: #737373;
    }
    .ig-likes {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 8px;
    }
    .ig-caption {
    font-size: 14px;
    line-height: 1.45;
    }
    .ig-time {
    font-size: 12px;
    color: #8e8e8e;
    text-transform: uppercase;
    margin-top: 8px;
    }


    /* Sadece mobilde (768px ve altı) görünür */
    @media (max-width: 768px) {
      .menu-floating-btn {
        display: flex;
      }
    
    .container {
        flex-direction: column;
    }
    aside,
    article {
        flex: 1 1 100%;
        max-width: 100%;
    }
      /* .top-navbar {/*nav üstü çubuğu kapatılıyor
        display: none !important;
      }*/
      
      .location-btn span {
        display: none;
      }
      
      .location-btn {
        padding: 8px;
      }

      .friend-list-name {
        max-width: 60px;
      }

      aside{
        display: none;
      }
      
    }


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
<article class="mob-feed">
<?php foreach ($posts as $p): ?>
  <div class="mob-card" data-pid="<?= $p['id'] ?>"
       data-begeni="<?= $p['begeni_sayisi'] ?>"
       data-benden="<?= $p['ben_begendim'] ? '1' : '0' ?>">

    <img src="<?= $p['media_url'] ?>" class="mob-media">

    <!-- ÜST BİLGİ (sol üst) -->
    <div class="card-info">
      <div class="info-name"><img src="<?php echo !empty($p['profil_resmi']) ? $p['profil_resmi'] : 'https://cdn-icons-png.flaticon.com/512/847/847969.png'; ?>" 
           alt="Profil" 
           class="profile-img"
           onerror="this.src='https://cdn-icons-png.flaticon.com/512/847/847969.png'"></div>
      <div class="info-name"><?= htmlspecialchars($p['ad'].' '.$p['soyad']) ?></div>
      <div class="info-meta"><?= $p['yas'] ?> · <?= htmlspecialchars($p['sehir']) ?></div>
    </div>

    <!-- BEĞENİ SAYISI (alt orta) -->
    <div class="begeni-badge">
      <i class="fas fa-heart"></i>
      <span class="begeni-sayisi"><?= $p['begeni_sayisi'] ?></span>
    </div>

    <!-- KALP ANİMASYONU -->
    <i class="fas fa-heart heart-overlay"></i>
  </div>
<?php endforeach; ?>
</article>
<!-- sonsuz scroll -->

<aside> 
  <p>sdfsdafsdaf fsdfsdfsdaf sdfsdafsdaf sadfsdafsdaf sdafsdaffsdafsdafsdaf sdafsda</p> 
</aside>
</div>
</body>


<script>
document.querySelectorAll('.mob-card').forEach(card=>{
  let tik=0;
  card.addEventListener('click',()=>{
    tik++;
    setTimeout(()=>{
      if(tik===2) handleLike(card);
      tik=0;
    },250);
  });
});
async function likePost(card){
    if(card.dataset.liked==='1') return; // zaten beğenmiş

    // animasyon
    const heart = card.querySelector('.heart');
    heart.classList.add('show');
    setTimeout(()=>heart.classList.remove('show'),600);

    // AJAX (aynı dosyaya POST)
    const rsp = await fetch('',{   // boş url = bu sayfa
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'postId='+card.dataset.pid
    });
    const res = await rsp.json();

    if(res.ok){
        // badge +1
        const badge = card.querySelector('.like-count span');
        badge.textContent = parseInt(badge.textContent)+1;
        // bir daha tıklanmasın
        card.dataset.liked='1';
    }
}
</script>
</head>
</html>
