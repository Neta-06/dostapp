<?php 
include("databs/xpdata.php");
include('databs/fonksiyonlar.php');

if(isset($_POST["telefon"]) && isset($_POST["password"])){
    $GelenTel = Guvenlik($_POST["telefon"]);
    $GelenPass = Guvenlik($_POST["password"]);
    
    // TELEFON FORMATINI TEMİZLE (ÖNEMLİ!)
    $GelenTel = preg_replace('/\D/', '', $GelenTel); // Sadece rakam
    $GelenTel = substr($GelenTel, -10); // Son 10 hane
    
    if(!empty($GelenTel) && !empty($GelenPass)){
        
        // Telefon kontrolü
        $KontrolSorgusu = $db->prepare("SELECT * FROM dbuser WHERE telefon = ?");
        $KontrolSorgusu->execute([$GelenTel]);
        
        if($KontrolSorgusu->rowCount() > 0){
            echo '<div style="text-align:center;">
                    <img src="img/Dikkat.png" width="100">
                    <p style="font-family:Arial Black; color:#000;">
                        Bu telefon kayıtlı! Pasif kullanıcıları kontrol edin.
                        <br><button onclick="history.back()" class="submit-btn">Geri</button>
                    </p></div>';
            exit();
        }
        
        // ŞİFREYİ SADECE BİR KEZ HASHLE
        $hashliSifre = password_hash($GelenPass, PASSWORD_DEFAULT);
        
        // Kullanıcı ekle
        $UyeEklemeSorgusu = $db->prepare("INSERT INTO dbuser SET telefon=?, dbpassword=?, sonuc=?");
        $basari = $UyeEklemeSorgusu->execute([$GelenTel, $hashliSifre, 1]);
        
        if($basari){
            header("Location: index.php?kayit=basarili");
        } else {
            echo "Kayıt hatası!";
        }
        exit();
        
    }
}

// HATA: Boş alanlar
echo '<div style="text-align:center;">
        <img src="img/Bilinmiyor.png" width="100">
        <p style="font-family:Arial Black; color:#000;">
            Telefon veya şifre boş!<br>
            <button onclick="history.back()" class="submit-btn">Geri</button>
        </p></div>';
?>
