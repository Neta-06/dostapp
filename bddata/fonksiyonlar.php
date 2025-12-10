<?php
$IPAdresi				=	$_SERVER["REMOTE_ADDR"];
$ZamanDamgasi			=	time();
$TarihSaat				=	date("d.m.Y H:i:s", $ZamanDamgasi);
$SiteKokDizini			=	$_SERVER["DOCUMENT_ROOT"];
$ResimKlasoruYolu		=	"/ala/emlakresim/";
$VerotIcinKlasorYolu	=	$SiteKokDizini.$ResimKlasoruYolu;
$ResimIcinDosyaYolu		=	$SiteKokDizini.$ResimKlasoruYolu;

function TarihBul($Deger){
	$Cevir				=	date("d.m.Y H:i:s", $Deger);
	$Sonuc				=	$Cevir;
	return $Sonuc;
}

function UcGunIleriTarihBul(){
	global $ZamanDamgasi;
	$BirGun				=	86400;
	$Hesapla			=	$ZamanDamgasi+(3*$BirGun);
	$Cevir				=	date("d.m.Y", $Hesapla);
	$Sonuc				=	$Cevir;
	return $Sonuc;
}

function RakamlarHaricTumKarakterleriSil($Deger){
	$Islem				=	preg_replace("/[^0-9]/", "", $Deger);
	$Sonuc				=	$Islem;
	return $Sonuc;
}
function SayiliIcerikleriFiltrele($Deger){
	$BoslukSil			=	trim($Deger);
	$TaglariTemizle		=	strip_tags($BoslukSil);
	$EtkisizYap			=	htmlspecialchars($TaglariTemizle, ENT_QUOTES);
	$Temizle			=	RakamlarHaricTumKarakterleriSil($EtkisizYap);
	$Sonuc				=	$Temizle;
	return $Sonuc;
}
function TumBosluklariSil($Deger){
	$Islem				=	preg_replace("/\s|&nbsp;/", "", $Deger);
	$Sonuc				=	$Islem;
	return $Sonuc;
}

function DonusumleriGeriDondur($Deger){
	$GeriDondur			=	htmlspecialchars_decode($Deger, ENT_QUOTES);
	$Sonuc				=	$GeriDondur;
	return $Sonuc;
}

function Guvenlik($Deger){
	$BoslukSil			=	trim($Deger);
	$TaglariTemizle		=	strip_tags($BoslukSil);
	$EtkisizYap			=	htmlspecialchars($TaglariTemizle, ENT_QUOTES);
	$Sonuc				=	$EtkisizYap;
	return $Sonuc;
}

function Filtrele($Deger){
	$BoslukSil			=	trim($Deger);
	$TaglariTemizle		=	strip_tags($BoslukSil);
	$EtkisizYap			=	htmlspecialchars($TaglariTemizle, ENT_QUOTES);
	$Temizle			=	RakamlarHaricTumKarakterleriSil($EtkisizYap);
	$Sonuc				=	$Temizle;
	return $Sonuc;
}

function IbanBicimlendir($Deger){
	$BoslukSil			=	trim($Deger);
	$TumBoslukSil		=	TumBosluklariSil($BoslukSil);
	$BirinciBlok		=	substr($TumBoslukSil, 0, 4);
	$IkinciBlok			=	substr($TumBoslukSil, 4, 4);
	$UcuncuBlok			=	substr($TumBoslukSil, 8, 4);
	$DorduncuBlok		=	substr($TumBoslukSil, 12, 4);
	$BesinciBlok		=	substr($TumBoslukSil, 16, 4);
	$AltinciBlok		=	substr($TumBoslukSil, 20, 4);
	$YedinciBlok		=	substr($TumBoslukSil, 24, 2);
	$Duzenle			=	$BirinciBlok . " " . $IkinciBlok . " " . $UcuncuBlok . " " . $DorduncuBlok . " " . $BesinciBlok . " " . $AltinciBlok . " " . $YedinciBlok;
	$Sonuc				=	$Duzenle;
	return $Sonuc;
}

function AktivasyonKoduUret(){
	$IlkBesli			=	rand(10000, 99999);
	$IkinciBesli		=	rand(10000, 99999);
	$UcuncuBesli		=	rand(10000, 99999);
	$DorduncuBesli		=	rand(10000, 99999);
	$Kod				=	$IlkBesli . "-" . $IkinciBesli . "-" . $UcuncuBesli . "-" . $DorduncuBesli;
	$Sonuc				=	$Kod;
	return $Sonuc;
}

function FiyatBicimlendir($Deger){
	$Bicimlendir	=	number_format($Deger, "2", ",", ".");
	$Sonuc			=	$Bicimlendir;
	return $Sonuc;
}

function ResimAdiOlustur(){
	$Sonuc			=	substr(md5(uniqid(time())), 0, 25);
	return $Sonuc;
}

function NumarayiFormatla($TelefonNumarasi)
{
    $TelefonNumarasi = preg_replace('/[^0-9]/', '', $TelefonNumarasi);
    //TelefonNumarasi değişkenini tüm karakterlerden arındırıyoruz.
    if (strlen($TelefonNumarasi) > 10) {
        //TelefonNumarasi değişkeni 10 haneden büyükse
        $UlkeKodu = substr($TelefonNumarasi, 0, strlen($TelefonNumarasi) - 10);
        $AlanKodu = substr($TelefonNumarasi, -10, 3);
        $SonrakiUcHane = substr($TelefonNumarasi, -7, 3);
        $SonDortHane = substr($TelefonNumarasi, -4, 4);
      
        $TelefonNumarasi = '+' . $UlkeKodu . ' (' . $AlanKodu . ') ' . $SonrakiUcHane . ' ' . $SonDortHane;
        // Oluşan Sonuç = + 90 (555) 444-3322
    } else if (strlen($TelefonNumarasi) == 10) {
        //TelefonNumarasi değişkeni 10 haneye eşitse
        $AlanKodu = substr($TelefonNumarasi, 0, 3);
        $SonrakiUcHane = substr($TelefonNumarasi, 3, 3);
        $SonDortHane = substr($TelefonNumarasi, 6, 4);
        $TelefonNumarasi = '(' . $AlanKodu . ') ' . $SonrakiUcHane . ' ' . $SonDortHane;
        // Oluşan Sonuç = (555) 444-3322
    } else if (strlen($TelefonNumarasi) == 7) {
        //TelefonNumarasi değişkeni 7 haneye eşitse
        $SonrakiUcHane = substr($TelefonNumarasi, 0, 3);
        $SonDortHane = substr($TelefonNumarasi, 3, 4);
        $TelefonNumarasi = $SonrakiUcHane . ' ' . $SonDortHane;
        // Oluşan Sonuç = 444-3322
    }
    return $TelefonNumarasi;
}
	function seo($s) {
		$tr = array('ş','Ş','ı','I','İ','ğ','Ğ','ü','Ü','ö','Ö','Ç','ç','(',')','/',' ',',','?');
		$eng = array('s','s','i','i','i','g','g','u','u','o','o','c','c','','','-','-','','');
		$s = str_replace($tr,$eng,$s);
		$s = strtolower($s);
		$s = preg_replace('/&amp;amp;amp;amp;amp;amp;amp;amp;amp;.+?;/', '', $s);
		$s = preg_replace('/\s+/', '-', $s);
		$s = preg_replace('|-+|', '-', $s);
		$s = preg_replace('/#/', '', $s);
		$s = str_replace('.', '', $s);
		$s = trim($s, '-');
		return $s;
	} 
?>