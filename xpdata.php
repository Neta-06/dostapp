<?php

try{
	$db	= new PDO("mysql:host=localhost;dbname=friends;charset=UTF8", "root", "");
}catch(PDOException $Hata){
	//echo "Bağlantı Hatası<br />" . $Hata->getMessage(); // Bu alanı kapatın çünkü site hata yaparsa kullanıcılar hata değerini görmesin.
	die();
}

if(isset($_SESSION["Bilisim-NET"])){
	$KullaniciSorgusu		=	$db->prepare("SELECT * FROM dbuser where telefon = ? LIMIT 1");
	$KullaniciSorgusu->execute([$_SESSION["Bilisim-NET"]]);
	$KullaniciSayisi		=	$KullaniciSorgusu->rowCount();
	$Neta					=	$KullaniciSorgusu->fetch(PDO::FETCH_ASSOC);

	if($KullaniciSayisi>0){
		$userid				=	$Neta["id"];
		$telefon			=	$Neta["telefon"];
		$dbpassword			=	$Neta["dbpassword"];
		$sonuc				=	$Neta["sonuc"];
	
	}else{
		//echo "Kullanıcı Sorgusu Hatalı"; // Bu alanı kapatın çünkü site hata yaparsa kullanıcılar hata değerini görmesin.
		die();
	}
}

?>