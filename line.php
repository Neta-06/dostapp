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

// Beğeni işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['post_id'])) {
        $post_id = intval($_POST['post_id']);
        $action = $_POST['action'];

        if ($action === 'like') {
            // Beğeni ekle
            $check_query = "SELECT id FROM likes WHERE post_id = :post_id AND user_id = :user_id";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(":post_id", $post_id);
            $check_stmt->bindParam(":user_id", $kullanici_id);
            $check_stmt->execute();

            if ($check_stmt->rowCount() == 0) {
                $insert_query = "INSERT INTO likes (post_id, user_id) VALUES (:post_id, :user_id)";
                $insert_stmt = $db->prepare($insert_query);
                $insert_stmt->bindParam(":post_id", $post_id);
                $insert_stmt->bindParam(":user_id", $kullanici_id);
                $insert_stmt->execute();
                echo json_encode(['success' => true, 'action' => 'liked']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Zaten beğendiniz']);
            }
        } elseif ($action === 'unlike') {
            // Beğeniyi kaldır
            $delete_query = "DELETE FROM likes WHERE post_id = :post_id AND user_id = :user_id";
            $delete_stmt = $db->prepare($delete_query);
            $delete_stmt->bindParam(":post_id", $post_id);
            $delete_stmt->bindParam(":user_id", $kullanici_id);
            $delete_stmt->execute();
            echo json_encode(['success' => true, 'action' => 'unliked']);
        }
        exit;
    }
}

// Postları al
$sql = "SELECT p.id,
               p.body,
               p.media_url,
               p.created_at,
               u.id as user_id,
               u.ad, u.soyad, u.yas, u.sehir, u.profil_resmi,
               (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as begeni_sayisi,
               EXISTS(SELECT 1 FROM likes WHERE post_id = p.id AND user_id = :uid) as ben_begendim
        FROM posts p
        JOIN kullanicilar u ON u.id = p.user_id
        WHERE p.media_url IS NOT NULL AND p.media_url != ''
        ORDER BY p.created_at DESC
        LIMIT 20";

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --like-color: #ff3040;
            --unlike-color: #666;
            --card-bg: #ffffff;
            --text-dark: #262626;
            --text-light: #8e8e8e;
            --clr-primary: #637e4e;
            --clr-secondary: #00bcd4;
            --clr-accent: #ff4757;
            --clr-dark: #2f3542;
            --clr-light: #f1f2f6;
            --clr-gray: #a4b0be;
            --radius: 20px;
            --shadow-1: 0 4px 20px -5px rgba(0, 0, 0, .15);
            --shadow-2: 0 8px 30px -10px rgba(0, 0, 0, .25);
            --transition: .35s cubic-bezier(.4, 0, .2, 1);
        }

        body {
            background-color: #c5a7acff;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            padding-top: 90px;
            /* Navbar için boşluk */
        }

        .feed-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        @media (max-width: 992px) {
            .feed-container {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }
        }

        /* Navbar - ORİJİNAL KORUNUYOR */
        .top-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: linear-gradient(90deg, #637e4eff, #00bcd4);
            height: 60px;
            padding: 0 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

        /* Instagram tarzı kart */
        .ig-feed {
            max-width: 600px;
            margin: 0 auto;
        }

        .ig-card {
            background: var(--card-bg);
            border: 1px solid #dbdbdb;
            border-radius: 16px;
            margin-bottom: 24px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .ig-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .ig-header {
            display: flex;
            align-items: center;
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
        }

        .ig-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 12px;
            object-fit: cover;
            border: 2px solid var(--primary-color);
        }

        .ig-user-info {
            flex: 1;
        }

        .ig-username {
            font-weight: 600;
            font-size: 14px;
            color: var(--text-dark);
            margin-bottom: 2px;
        }

        .ig-user-meta {
            font-size: 12px;
            color: var(--text-light);
        }

        .ig-media-container {
            position: relative;
            width: 100%;
            height: 500px;
            overflow: hidden;
            cursor: pointer;
        }

        .ig-media {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .ig-media:hover {
            transform: scale(1.02);
        }

        /* Kalp overlay animasyonu */
        .heart-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            font-size: 120px;
            color: var(--like-color);
            opacity: 0;
            pointer-events: none;
            z-index: 2;
            text-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
        }

        .heart-overlay.active {
            animation: heartPop 0.8s ease-out;
        }

        @keyframes heartPop {
            0% {
                transform: translate(-50%, -50%) scale(0);
                opacity: 1;
            }

            15% {
                transform: translate(-50%, -50%) scale(1.3);
                opacity: 1;
            }

            100% {
                transform: translate(-50%, -50%) scale(1.5);
                opacity: 0;
            }
        }

        /* Aksiyon butonları */
        .ig-actions {
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .action-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.2s ease;
            color: var(--text-dark);
        }

        .like-btn {
            color: var(--unlike-color);
        }

        .like-btn.liked {
            color: var(--like-color);
            animation: likeBounce 0.4s ease;
        }

        .dislike-btn {
            color: var(--unlike-color);
        }

        .dislike-btn.disliked {
            color: #666;
        }

        @keyframes likeBounce {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }
        }

        .action-btn:hover {
            background-color: rgba(0, 0, 0, 0.05);
            transform: scale(1.1);
        }

        .save-btn {
            margin-left: auto;
            font-size: 20px;
        }

        /* Post bilgileri */
        .ig-info {
            padding: 0 16px 16px;
        }

        .ig-likes {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
            color: var(--text-dark);
        }

        .ig-caption {
            font-size: 14px;
            line-height: 1.5;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .ig-hashtags {
            color: var(--primary-color);
            font-weight: 500;
        }

        .ig-time {
            font-size: 12px;
            color: var(--text-light);
            text-transform: uppercase;
            margin-top: 8px;
        }

        /* Beğeni/Beğenmeme sayacı */
        .reaction-counter {
            display: flex;
            align-items: center;
            gap: 20px;
            margin: 15px 0;
            padding: 10px 0;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
        }

        .reaction-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--text-light);
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 20px;
            transition: all 0.2s ease;
        }

        .reaction-item:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .reaction-item i {
            font-size: 16px;
        }

        .reaction-item.liked {
            color: var(--like-color);
            font-weight: 600;
            background-color: rgba(255, 48, 64, 0.1);
        }

        .reaction-item.disliked {
            color: var(--unlike-color);
            font-weight: 600;
            background-color: rgba(102, 102, 102, 0.1);
        }

        /* Sağ sidebar */
        .sidebar {
            position: sticky;
            top: 100px;
            height: calc(100vh - 120px);
            overflow-y: auto;
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 3px;
        }

        /* Yükleme animasyonu */
        .loading {
            text-align: center;
            padding: 30px;
            color: var(--text-light);
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
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

        /* Floating Menu Button */
        .menu-floating-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
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
            transition: all 0.3s ease;
        }

        .menu-floating-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .feed-container {
                padding: 0 10px;
            }

            .ig-card {
                border-radius: 12px;
            }

            .ig-media-container {
                height: 400px;
            }

            .ig-actions {
                padding: 10px;
                gap: 15px;
            }

            .action-btn {
                font-size: 20px;
            }

            .menu-floating-btn {
                display: flex;
            }

            body {
                padding-top: 70px;
            }
        }

        @media (max-width: 480px) {
            .ig-media-container {
                height: 350px;
            }

            .reaction-counter {
                gap: 15px;
            }

            .sidebar {
                display: none;
            }
        }

        /* Konum Dropdown */
        .location-dropdown {
            position: relative;
        }

        .location-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            min-width: 250px;
            display: none;
            z-index: 1001;
            margin-top: 10px;
            overflow: hidden;
        }

        .location-menu.show {
            display: block;
            animation: dropdownFadeIn 0.3s ease;
        }

        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .location-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .location-header h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }

        .location-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
        }

        .current-location {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #666;
        }

        .refresh-location {
            background: none;
            border: none;
            color: #667eea;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .refresh-location:hover {
            background: #f0f0f0;
            transform: rotate(180deg);
        }

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.toggle-slider {
            background-color: #667eea;
        }

        input:checked+.toggle-slider:before {
            transform: translateX(26px);
        }

        /* Dropdown icon animation */
        .dropdown-icon {
            font-size: 10px;
            transition: transform 0.3s ease;
            margin-left: 5px;
        }

        .location-dropdown.open .dropdown-icon {
            transform: rotate(180deg);
        }

        /* Off-canvas */
        .offcanvas {
            width: 320px;
            backdrop-filter: blur(10px)
        }

        .list-group-item {
            border-radius: 12px;
            margin: 4px 8px;
            transition: background var(--transition), color var(--transition)
        }

        .list-group-item.active {
            background: linear-gradient(135deg, var(--clr-primary), var(--clr-secondary));
            border: none
        }
    </style>
</head>

<body class="bg-light">
    <!-- Top Navbar - ORİJİNAL KORUNUYOR -->
    <nav class="top-navbar">
        <div class="nav-left">
            <a class="navbar-brand" href="giris.php">
                <img src="img/friends.png" alt="Logo"> <?php echo $translations['site_adi']; ?>
            </a>
        </div>

        <div class="nav-right">
            <div class="location-dropdown" id="locationDropdown">
                <button class="location-btn" onclick="toggleLocationMenu()">
                    <i class="fas fa-location-arrow"></i>
                    <span class="location-text"><?php echo $translations['konum']; ?></span>
                    <i class="fas fa-chevron-down dropdown-icon"></i>
                </button>
                <div class="location-menu" id="locationMenu">
                    <div class="location-header">
                        <h6>Konum Servisleri</h6>
                        <label class="toggle-switch">
                            <input type="checkbox" id="locationToggle" onchange="toggleLocation()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="location-info">
                        <div class="current-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <span id="currentLocationText">Konum alınıyor...</span>
                        </div>
                        <button class="refresh-location" onclick="refreshLocation()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            <img src="<?php echo !empty($user_profile['profil_resmi']) ? $user_profile['profil_resmi'] : 'https://cdn-icons-png.flaticon.com/512/847/847969.png'; ?>"
                alt="Profil"
                class="profile-img"
                onerror="this.src='https://cdn-icons-png.flaticon.com/512/847/847969.png'">
            <button class="menu-btn" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Floating Menü Butonu -->
    <button class="menu-floating-btn" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- ===================================  OFF-CANVAS  =================================== -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="sidebar">
        <div class="offcanvas-header">
            <div class="d-flex align-items-center">
                <img src="<?php echo !empty($user_profile['profil_resmi']) ? $user_profile['profil_resmi'] : 'https://cdn-icons-png.flaticon.com/512/847/847969.png'; ?>"
                    width="50" height="50" class="rounded-circle me-3" onerror="this.src='https://cdn-icons-png.flaticon.com/512/847/847969.png';">
                <div>
                    <h6 class="mb-0"><?php echo $user_profile['ad'] . ' ' . $user_profile['soyad']; ?></h6>
                    <small class="text-muted"><?php echo $user_profile['cep_telefonu']; ?></small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <div class="list-group list-group-flush">
                <a href="#" class="list-group-item list-group-item-action active" onclick="getCurrentLocation()">
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
                    <i class="fas fa-bell"></i> <?php echo $translations['bildirimler']; ?>
                    <span class="badge bg-danger float-end">3</span>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <i class="fas fa-cog"></i> <?php echo $translations['ayarlar']; ?>
                </a>
                <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                    <i class="fas fa-sign-out-alt"></i> <?php echo $translations['cikis']; ?>
                </a>
            </div>
        </div>
    </div>

    <!-- Ana İçerik -->
    <div class="feed-container">
        <!-- Ana Feed -->
        <div class="ig-feed">
            <?php if (empty($posts)): ?>
                <div class="ig-card">
                    <div class="ig-info text-center py-5">
                        <i class="fas fa-camera fa-3x mb-3 text-muted"></i>
                        <h4>Henüz paylaşım yok</h4>
                        <p class="text-muted">İlk paylaşımı sen yap!</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="ig-card" id="post-<?php echo $post['id']; ?>">
                        <!-- Header -->
                        <div class="ig-header">
                            <img src="<?php echo !empty($post['profil_resmi']) ? $post['profil_resmi'] : 'https://cdn-icons-png.flaticon.com/512/847/847969.png'; ?>"
                                class="ig-avatar"
                                onerror="this.src='https://cdn-icons-png.flaticon.com/512/847/847969.png'">
                            <div class="ig-user-info">
                                <div class="ig-username"><?php echo htmlspecialchars($post['ad'] . ' ' . $post['soyad']); ?></div>
                                <div class="ig-user-meta">
                                    <?php echo $post['yas'] ? $post['yas'] . ' yaş' : ''; ?>
                                    <?php echo $post['sehir'] ? ' · ' . htmlspecialchars($post['sehir']) : ''; ?>
                                </div>
                            </div>
                            <button class="action-btn"><i class="fas fa-ellipsis-h"></i></button>
                        </div>

                        <!-- Media with Heart Overlay -->
                        <div class="ig-media-container"
                            data-post-id="<?php echo $post['id']; ?>"
                            onclick="handleDoubleTap(this)">
                            <img src="<?php echo $post['media_url']; ?>"
                                class="ig-media"
                                alt="Post"
                                onerror="this.src='https://via.placeholder.com/600x600?text=Resim+Yüklenemedi'">
                            <div class="heart-overlay">
                                <i class="fas fa-heart"></i>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="ig-actions">
                            <button class="action-btn like-btn <?php echo $post['ben_begendim'] ? 'liked' : ''; ?>"
                                data-post-id="<?php echo $post['id']; ?>"
                                onclick="toggleLike(this)">
                                <i class="<?php echo $post['ben_begendim'] ? 'fas' : 'far'; ?> fa-heart"></i>
                            </button>

                            <button class="action-btn dislike-btn"
                                data-post-id="<?php echo $post['id']; ?>"
                                onclick="toggleDislike(this)">
                                <i class="far fa-thumbs-down"></i>
                            </button>

                            <button class="action-btn">
                                <i class="far fa-comment"></i>
                            </button>

                            <button class="action-btn">
                                <i class="far fa-share-square"></i>
                            </button>

                            <button class="action-btn save-btn">
                                <i class="far fa-bookmark"></i>
                            </button>
                        </div>

                        <!-- Beğeni/Beğenmeme Sayacı -->
                        <div class="reaction-counter">
                            <div class="reaction-item <?php echo $post['ben_begendim'] ? 'liked' : ''; ?>"
                                onclick="document.querySelector(`.like-btn[data-post-id='<?php echo $post['id']; ?>']`).click()">
                                <i class="fas fa-heart"></i>
                                <span id="like-count-num-<?php echo $post['id']; ?>"><?php echo $post['begeni_sayisi']; ?></span>
                                <span>Beğen</span>
                            </div>

                            <div class="reaction-item"
                                onclick="document.querySelector(`.dislike-btn[data-post-id='<?php echo $post['id']; ?>']`).click()">
                                <i class="far fa-thumbs-down"></i>
                                <span id="dislike-count-<?php echo $post['id']; ?>">0</span>
                                <span>Beğenme</span>
                            </div>
                        </div>

                        <!-- Post Info -->
                        <div class="ig-info">
                            <div class="ig-likes">
                                <span id="like-text-<?php echo $post['id']; ?>">
                                    <?php echo $post['begeni_sayisi']; ?> kişi beğendi
                                </span>
                            </div>

                            <?php if (!empty($post['body'])): ?>
                                <div class="ig-caption">
                                    <strong><?php echo htmlspecialchars($post['ad'] . ' ' . $post['soyad']); ?></strong>
                                    <?php echo htmlspecialchars($post['body']); ?>
                                </div>
                            <?php endif; ?>

                            <div class="ig-time">
                                <?php echo date('d M Y', strtotime($post['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Loading Spinner -->
                <div class="loading" id="loading-spinner" style="display: none;">
                    <div class="loading-spinner"></div>
                    <p>Daha fazla yükleniyor...</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <?php include "liste.php"; ?>
        </div>
    </div>

    <script>
        // Konum menüsü toggle
        function toggleLocationMenu() {
            const dropdown = document.getElementById('locationDropdown');
            const menu = document.getElementById('locationMenu');

            dropdown.classList.toggle('open');
            menu.classList.toggle('show');

            // Diğer açık menüleri kapat
            document.querySelectorAll('.location-dropdown').forEach(item => {
                if (item !== dropdown) {
                    item.classList.remove('open');
                    item.querySelector('.location-menu').classList.remove('show');
                }
            });
        }

        // Dışarı tıklayınca menüyü kapat
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('locationDropdown');
            const menu = document.getElementById('locationMenu');

            if (!dropdown.contains(event.target)) {
                dropdown.classList.remove('open');
                menu.classList.remove('show');
            }
        });

        // Çift tıklama ile beğenme
        let tapCount = 0;
        let tapTimer;

        function handleDoubleTap(element) {
            tapCount++;

            if (tapCount === 1) {
                tapTimer = setTimeout(() => {
                    tapCount = 0;
                }, 300);
            } else if (tapCount === 2) {
                clearTimeout(tapTimer);
                tapCount = 0;

                // Kalp animasyonunu göster
                const heart = element.querySelector('.heart-overlay');
                heart.classList.add('active');
                setTimeout(() => {
                    heart.classList.remove('active');
                }, 800);

                // Postu beğen
                const postId = element.dataset.postId;
                const likeBtn = document.querySelector(`.like-btn[data-post-id="${postId}"]`);
                if (!likeBtn.classList.contains('liked')) {
                    toggleLike(likeBtn);
                }
            }
        }

        // Beğeni işlemi
        async function toggleLike(button) {
            const postId = button.dataset.postId;
            const isLiked = button.classList.contains('liked');
            const action = isLiked ? 'unlike' : 'like';

            try {
                const response = await fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=${action}&post_id=${postId}`
                });

                const result = await response.json();

                if (result.success) {
                    const likeCountElement = document.getElementById(`like-count-num-${postId}`);
                    let currentLikes = parseInt(likeCountElement.textContent);
                    const likeTextElement = document.getElementById(`like-text-${postId}`);

                    if (action === 'like') {
                        // Beğeni ekle
                        button.classList.add('liked');
                        button.innerHTML = '<i class="fas fa-heart"></i>';
                        likeCountElement.textContent = currentLikes + 1;
                        likeTextElement.textContent = (currentLikes + 1) + ' kişi beğendi';

                        // Reaction item güncelle
                        const reactionItem = document.querySelector(`#post-${postId} .reaction-item:first-child`);
                        reactionItem.classList.add('liked');
                    } else {
                        // Beğeniyi kaldır
                        button.classList.remove('liked');
                        button.innerHTML = '<i class="far fa-heart"></i>';
                        likeCountElement.textContent = Math.max(0, currentLikes - 1);
                        likeTextElement.textContent = Math.max(0, currentLikes - 1) + ' kişi beğendi';

                        // Reaction item güncelle
                        const reactionItem = document.querySelector(`#post-${postId} .reaction-item:first-child`);
                        reactionItem.classList.remove('liked');
                    }
                } else {
                    console.error('Beğeni işlemi başarısız:', result.message);
                    alert('Bu gönderiyi zaten beğenmişsiniz.');
                }
            } catch (error) {
                console.error('Hata:', error);
                alert('Beğeni işlemi sırasında bir hata oluştu.');
            }
        }

        // Beğenmeme işlemi (front-end örneği)
        function toggleDislike(button) {
            const postId = button.dataset.postId;
            const isDisliked = button.classList.contains('disliked');
            const dislikeCountElement = document.getElementById(`dislike-count-${postId}`);
            let currentDislikes = parseInt(dislikeCountElement.textContent);

            if (isDisliked) {
                // Beğenmeyi kaldır
                button.classList.remove('disliked');
                button.innerHTML = '<i class="far fa-thumbs-down"></i>';
                dislikeCountElement.textContent = Math.max(0, currentDislikes - 1);

                // Reaction item güncelle
                const reactionItem = document.querySelector(`#post-${postId} .reaction-item:last-child`);
                reactionItem.classList.remove('disliked');
            } else {
                // Beğenme yap
                button.classList.add('disliked');
                button.innerHTML = '<i class="fas fa-thumbs-down"></i>';
                dislikeCountElement.textContent = currentDislikes + 1;

                // Eğer beğeni varsa kaldır
                const likeBtn = document.querySelector(`.like-btn[data-post-id="${postId}"]`);
                if (likeBtn.classList.contains('liked')) {
                    toggleLike(likeBtn);
                }

                // Reaction item güncelle
                const reactionItem = document.querySelector(`#post-${postId} .reaction-item:last-child`);
                reactionItem.classList.add('disliked');
            }

            // Burada backend'e AJAX isteği gönderebilirsiniz
            // fetch('dislike.php', { method: 'POST', body: `post_id=${postId}&dislike=${!isDisliked}` })
        }

        // Sonsuz scroll
        let loading = false;
        let page = 1;

        window.addEventListener('scroll', () => {
            if (loading) return;

            const scrollTop = window.scrollY;
            const windowHeight = window.innerHeight;
            const docHeight = document.documentElement.scrollHeight;

            if (scrollTop + windowHeight >= docHeight - 100) {
                loadMorePosts();
            }
        });

        async function loadMorePosts() {
            loading = true;
            const spinner = document.getElementById('loading-spinner');
            spinner.style.display = 'block';

            try {
                // 1 saniye bekle (simülasyon)
                await new Promise(resolve => setTimeout(resolve, 1000));

                // Burada AJAX ile yeni postları yükleyebilirsiniz
                // const response = await fetch(`load_more_posts.php?page=${page}`);
                // const newPosts = await response.json();

                page++;
            } catch (error) {
                console.error('Yükleme hatası:', error);
            } finally {
                loading = false;
                spinner.style.display = 'none';
            }
        }

        // Konum fonksiyonları
        function toggleLocation() {
            const toggle = document.getElementById('locationToggle');
            const locationText = document.getElementById('currentLocationText');

            if (toggle.checked) {
                getCurrentLocation();
                localStorage.setItem('locationEnabled', 'true');
            } else {
                locationText.textContent = 'Konum servisleri kapalı';
                localStorage.setItem('locationEnabled', 'false');
            }
        }

        function getCurrentLocation() {
            const locationText = document.getElementById('currentLocationText');
            locationText.textContent = 'Konum alınıyor...';

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude.toFixed(4);
                        const lng = position.coords.longitude.toFixed(4);
                        locationText.textContent = `${lat}, ${lng}`;
                    },
                    (error) => {
                        switch (error.code) {
                            case error.PERMISSION_DENIED:
                                locationText.textContent = 'Konum izni reddedildi';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                locationText.textContent = 'Konum bilgisi alınamıyor';
                                break;
                            case error.TIMEOUT:
                                locationText.textContent = 'Konum alımı zaman aşımına uğradı';
                                break;
                            default:
                                locationText.textContent = 'Bilinmeyen hata';
                        }
                    }
                );
            } else {
                locationText.textContent = 'Tarayıcınız konum servisini desteklemiyor';
            }
        }

        function refreshLocation() {
            const toggle = document.getElementById('locationToggle');
            if (toggle.checked) {
                getCurrentLocation();
            }
        }

        // Sayfa yüklendiğinde
        document.addEventListener('DOMContentLoaded', function() {
            // Konum servisi durumunu kontrol et
            if (localStorage.getItem('locationEnabled') === 'true') {
                document.getElementById('locationToggle').checked = true;
                getCurrentLocation();
            }

            console.log('Line sayfası yüklendi');
        });
    </script>
</body>

</html>
