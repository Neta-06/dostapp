<?php
session_start(); ob_start();
include("databs/xpdata.php");
include('databs/fonksiyonlar.php');

if (isset($_POST["telefon"]) && isset($_POST["password"])) {
    $GelenTel = Guvenlik($_POST["telefon"]);
    $GelenPass = Guvenlik($_POST["password"]);
    
    // TELEFON TEMİZLE (ÖNEMLİ!)
    $GelenTel = preg_replace('/\D/', '', $GelenTel); // Sadece rakam
    $GelenTel = substr($GelenTel, -10); // Son 10 hane
    
    if(!empty($GelenTel) && !empty($GelenPass)){
        
        // 1. TELEFONU BUL
        $KullaniciSorgu = $db->prepare("SELECT * FROM dbuser WHERE telefon = ?");
        $KullaniciSorgu->execute([$GelenTel]);
        $KullaniciKaydi = $KullaniciSorgu->fetch(PDO::FETCH_ASSOC);
        
        if($KullaniciKaydi){
            // 2. ŞİFRE DOĞRULA (ESKI HASH İLE)
            if(password_verify($GelenPass, $KullaniciKaydi['dbpassword'])){
                $_SESSION["Bilisim-NET"] = $GelenTel;
                header("Location: index.php");
                exit();
            } else {
                header("Location: hata.php?hata=sifre");
                exit();
            }
        } else {
            header("Location: hata.php?hata=telefon");
            exit();
        }
    }
}
header("Location: hata.php?hata= bos");
exit();
?>
