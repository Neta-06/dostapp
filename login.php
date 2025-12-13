<?php
session_start();
require_once "config/database.php";
require_once "config/functions.php";

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'tr';
$translations = loadLanguage($lang);

$error = '';
$success = '';

// Çıkış mesajını kontrol et
if (isset($_GET['message']) && $_GET['message'] == 'logout_success') {
    $success = $translations['mesaj1'];
}

if ($_POST && isset($_POST['cep_telefonu'])) {
    $database = new Database();
    $db = $database->getConnection();
  
    // Telefon numarasını temizle ve formatla
    $cep_telefonu = $_POST['cep_telefonu'];
    $cep_telefonu = preg_replace('/[^0-9]/', '', $cep_telefonu);
    // Hata ayıklama için
    error_log("Gönderilen telefon: " . $cep_telefonu);
    
    // Telefon numarasını veritabanı formatına çevir
    
    error_log("Veritabanı formatı: " . $cep_telefonu);
    
    $sifre = $_POST['sifre'];
    $kod = $_POST['kod'];
    $telefon = $kod . $cep_telefonu;
    
    if (empty($cep_telefonu) || empty($sifre)) {
        $error = $translations['mesaj2'];
    } else {
        // Önce +90 eklenmiş formatta ara
        $query = "SELECT * FROM kullanicilar WHERE cep_telefonu = :cep_telefonu AND aktif = 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":cep_telefonu", $telefon);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($sifre, $row['sifre'])) {
                    $_SESSION['kullanici_id'] = $row['id'];
                    $_SESSION['kullanici_adi'] = $row['ad'] . ' ' . $row['soyad'];
                    $_SESSION['cep_telefonu'] = $row['cep_telefonu'];
                    $_SESSION['lang'] = $lang;
                    
                    // Son giriş zamanını güncelle
                    $update_query = "UPDATE kullanicilar SET son_giris = NOW() WHERE id = :id";
                    $update_stmt = $db->prepare($update_query);
                    $update_stmt->bindParam(":id", $row['id']);
                    $update_stmt->execute();
                    
                    // Dil desteği eklendi
                    error_log($translations['mesaj3']);
                    header("Location: index.php");
                    exit;
                } else {
                    $error = $translations['hata1'];
                }
            } else {
                // +90 formatında bulunamadıysa, sadece numarayı ara (544... formatında)
                $simple_phone = substr($cep_telefonu, 3); // +90'ı kaldır
                error_log("+90 formatında bulunamadı, basit format deneniyor: " . $simple_phone);
                
                $query2 = "SELECT * FROM kullanicilar WHERE cep_telefonu = :cep_telefonu AND aktif = 1";
                $stmt2 = $db->prepare($query2);
                $stmt2->bindParam(":cep_telefonu", $simple_phone);
                
                if ($stmt2->execute() && $stmt2->rowCount() == 1) {
                    $row = $stmt2->fetch(PDO::FETCH_ASSOC);
                    
                    if (password_verify($sifre, $row['sifre'])) {
                        $_SESSION['kullanici_id'] = $row['id'];
                        $_SESSION['kullanici_adi'] = $row['ad'] . ' ' . $row['soyad'];
                        $_SESSION['cep_telefonu'] = $row['cep_telefonu'];
                        $_SESSION['lang'] = $lang;
                        
                        // Son giriş zamanını güncelle
                        $update_query = "UPDATE kullanicilar SET son_giris = NOW() WHERE id = :id";
                        $update_stmt = $db->prepare($update_query);
                        $update_stmt->bindParam(":id", $row['id']);
                        $update_stmt->execute();
                        
                        error_log($translations['mesaj3']);
                        header("Location: index.php");
                        exit;
                    } else {
                        $error = $translations['hata1'];
                    }
                } else {
                    $error = $translations['hata2'];
                }
            }
        } else {
            $error = $translations['hata3'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title><?php echo $translations['giris']; ?> - <?php echo $translations['site_adi']; ?></title>
    <link rel="stylesheet" href="css/intlTelInput.css">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>

        .iti { width: 100%; }
        .iti__flag {background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/img/flags.png");}
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
          .iti__flag {background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/img/flags@2x.png");}
        }
        
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #c0e2e7;
            --light-color: #75808b;
            --dark-color: #343a40;
            --text-color: #333;
            --border-color: #e0e0e0;
            --shadow-light: 0 2px 10px rgba(0,0,0,0.08);
            --shadow-medium: 0 5px 25px rgba(0,0,0,0.15);
            --gradient-primary: linear-gradient(135deg, var(--primary-color), #6e748d);
            --gradient-secondary: linear-gradient(135deg, var(--secondary-color), #868e96);
        }

        body{
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            text-align: justify;
            position: absolute;
            margin: auto;
            top: 15%;
            width: 100%;

        }

        form{
            margin: auto;
            font-size: 12px;
            width: 380px;
            height: 500px;
            background-color: aliceblue;
            border-color: bisque;
            overflow: auto;
            border-radius: 30% 10% 30% 10%;
            padding: 20px;
            box-shadow: 15px  5px 15px rgb(210, 245, 177);
            text-align: center;
        }

        input[type=text]{
            margin: auto;
            width: 100%;
            box-sizing: border-box;
            background-color: transparent;
            border: 0px 0px 0px 0 px solid #ccc;
            border-radius: 14px;
            font-size: 14px;
            background-color: white;
            background-position: 10px 10px; 
            background-repeat: no-repeat;
            padding: 12px 20px 12px 40px;
        }
        input[type=text1]{
            margin: auto;
            width: 100%;
            box-sizing: border-box;
            background-color: transparent;
            border: 0px 0px 0px 0 px solid #ccc;
            border-radius: 14px;
            font-size: 14px;
            background-color: white;
            background-image: url('img/password.png');
            background-position: 10px 10px; 
            background-repeat: no-repeat;
            padding: 12px 20px 12px 40px;
        }

        input[type=password] {
            margin: auto;
            width: 100%;
            box-sizing: border-box;
            background-color: transparent;
            border: 2px 0px 0px 0 px solid #ccc;
            border-radius: 14px;
            font-size: 14px;
            background-color: white;
            background-image: url('img/password.png');
            background-position: 10px 10px; 
            background-repeat: no-repeat;
            padding: 12px 20px 12px 40px;
          
            
        }

        .btn{
            margin: auto;
            font-weight: bolder;
            font-size: 14px; /* Yazı boyutunu buradan ayarlanıyor. */
            color: #0f0000;
            width: 100%; /*Buton boyutunu tam boyut yapıyor. */
            height: 10%;
            margin-bottom: 10px;
            border-radius: 15px;
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.3s ease;
           
        }


        .btn1{
            font-size: 12px; /* Yazı boyutunu buradan ayarlanıyor. */
            font-weight: bolder;
            background-color: transparent;
            color: #3f0303;
            width: 45%; /*Buton boyutunu tam boyut yapıyor. */
            height: 10%;
        }

        .btn-primary {
            background: var(--gradient-primary);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3, #5a67d8);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }

        .btn-secondary {
            background: var(--gradient-secondary);
            border: none;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6268, #707a82);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .btn-outline-secondary {
            border-color: var(--secondary-color);
            color: var(--secondary-color);
        }

        .btn-outline-secondary:hover {
            background-color: var(--secondary-color);
            color: white;
        }

        #center{
            margin: auto;
            
            padding: 5px;
        }

        .logo{
            background-image: url('img/friends.png');
            background-repeat:no-repeat;
            width: 150px;
            height: 150px;
            margin: auto;
            margin-bottom: 20px;
            background-repeat: no-repeat;
            background-position: center;
            border: 50px 50px 50px 50px solid #d11515;
            box-shadow: 15px 20px 60px 10px #a1a04b;
            border-radius: 50% 50% 50% 50%;


        }

        form [id="toggleIcon"] {
            position:relative;
            transform: translateY(-195%);
            left: 140px;
            border: none;
            background: none;
            cursor: pointer;
        }

    </style>
</head>
<body>
    <div id="center">

            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            </div>
    </div>

    <form method="POST" id="loginForm">
        <div class="logo">

        </div>

        <div class="giris">
            <input type="text" class="form-giris" id="cep_telefonu" name="cep_telefonu" placeholder="5XX XXX XX XX" maxlength="13"/>
            <div id="telHelp" class="form-text">Numaranızı Ülke kodu olmadan giriniz 5XX XXX XX XX.</div>
        </div>
        <div class="sifre">
            <input type="password" id="sifre" name="sifre" placeholder="<?php echo $translations['sifre']; ?>" /> 
            <div class="password"><i id="toggleIcon" class="fa fa-eye-slash" onclick="togglePasswordVisibility()" style="font-size:16px"></i></div>
        </div>


        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-sign-in-alt me-2"></i>
            <?php echo $translations['giris']; ?>
        </button>


        <div id="center">
            <a href="register.php?lang=<?php echo $lang; ?>" class="text-decoration-none">
                <?php echo $translations['kayit_ol']; ?>
            </a>
        </div>

        <div class="btn-group" role="group">
            <a href="?lang=tr" class="btn btn-sm btn-outline-secondary <?php echo $lang == 'tr' ? 'active' : ''; ?>">Türkçe</a>
            <a href="?lang=en" class="btn btn-sm btn-outline-secondary <?php echo $lang == 'en' ? 'active' : ''; ?>">English</a>
        </div>
     <input type="hidden" id="kod" name="kod" value="+90">
    </form>
    
</body>
   <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/all.min.js"></script>
    <script src="js/intlTelInput.min.js"></script>

    <script>
        // Telefon input başlatma
        const input = document.querySelector("#cep_telefonu");
        
        // intlTelInput başlat - BASİT AYARLAR
        const iti = window.intlTelInput(input, {
            initialCountry: "tr",
            preferredCountries: ["tr", "us", "gb", "de", "fr"],
            separateDialCode: true,
            nationalMode: false, // Uluslararası format
            
        });

        let seciliUlkeKodu = '+90';

        input.addEventListener('countrychange', function() {
            seciliUlkeData = iti.getSelectedCountryData();
            seciliUlkeKodu = '+' + seciliUlkeData.dialCode;
            
            // JS değişkeni → PHP hidden input
            document.getElementById("kod").value = seciliUlkeKodu;
            
            console.log('Ülke Kodu:', seciliUlkeKodu);  // +90
        });

        // Form submit öncesi garanti
        document.querySelector('form').addEventListener('submit', function() {
            document.getElementById("ulke_kodu").value = seciliUlkeKodu;
        });

        function togglePasswordVisibility() {
            var passwordInput = document.getElementById('sifre');
            var toggleIcon = document.getElementById('toggleIcon');
            if (passwordInput.type === "password") {
                passwordInput.type = "text1";
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = "password";
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            }
        }
    </script>
    <script>
 document.getElementById('cep_telefonu').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, ''); // Sadece rakamları tut
    value = value.substring(0, 10); // Maks 10 hane
    
    // Doğru formatlama: 5XX XXX XX XX (toplam 10 rakam)
    let formatted = '';
    if (value.length > 0) formatted += value.substring(0, 3);  // 5XX
    if (value.length > 3) formatted += ' ' + value.substring(3, 6); // XXX
    if (value.length > 6) formatted += ' ' + value.substring(6, 8); // XX
    if (value.length > 8) formatted += ' ' + value.substring(8, 10); // XX
    
    e.target.value = formatted.trim();
});

    </script>
</html>