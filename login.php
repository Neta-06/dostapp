<!DOCTYPE html>
<html lang="tr-TR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>login sayfası</title>
    <style>
        body{
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            text-align: justify;
            position: absolute;
            margin: auto;
            top: 20%;
            width: 95%;

        }

        form{
            margin: auto;
            font-size: 12px;
            width: 400px;
            height: 450px;
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
            width: 90%;
            box-sizing: border-box;
            background-color: transparent;
            border: 0px 0px 0px 0 px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            background-color: white;
            background-image: url('img/tel.png');
            background-position: 10px 10px; 
            background-repeat: no-repeat;
            padding: 12px 20px 12px 40px;
        }
        input[type=text1]{
            margin: auto;
            width: 90%;
            box-sizing: border-box;
            background-color: transparent;
            border: 0px 0px 0px 0 px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            background-color: white;
            background-image: url('img/password.png');
            background-position: 10px 10px; 
            background-repeat: no-repeat;
            padding: 12px 20px 12px 40px;
        }

        input[type=password] {
            margin: auto;
            width: 90%;
            box-sizing: border-box;
            background-color: transparent;
            border: 2px 0px 0px 0 px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            background-color: white;
            background-image: url('img/password.png');
            background-position: 10px 10px; 
            background-repeat: no-repeat;
            padding: 12px 20px 12px 40px;
          
            
        }
        .btn{
            margin: auto;
            font-weight: bolder;
            background-color: transparent;
            font-size: 14px; /* Yazı boyutunu buradan ayarlanıyor. */
            color: #0f0000;
            width: 90%; /*Buton boyutunu tam boyut yapıyor. */
            height: 10%;
            margin-bottom: 10px;
            margin-top: 10px;
            
        }

        .btn1{
            font-size: 12px; /* Yazı boyutunu buradan ayarlanıyor. */
            font-weight: bolder;
            background-color: transparent;
            color: #3f0303;
            width: 45%; /*Buton boyutunu tam boyut yapıyor. */
            height: 10%;
            margin-bottom: 5px;
            margin-top: 5px;
        }

        #center{
            margin: auto;
            width: 40%;
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
            transform: translateY(-145%);
            left: 150px;
            border: none;
            background: none;
            cursor: pointer;
        }

    </style>
</head>
<body>
    <form method="POST" action="kontrol.php">
        <div class="logo">

        </div>

        <div class="giris">
            <input type="text" class="form-giris" id="telefon" name="telefon" placeholder="5XX XXX XX XX" maxlength="13"/>
            <div id="telHelp" class="form-text">Numaranızı Ülke kodu olmadan giriniz 5XX XXX XX XX.</div>
        </div>
        <div class="sifre">
            <input type="password" id="password" name="password" placeholder="Password" /> 
            <div class="password"><i id="toggleIcon" class="fa fa-eye-slash" onclick="togglePasswordVisibility()" style="font-size:24px"></i></div>
        </div>

        <button type="submit" class="btn">Giriş</button></br>
        <div id="center">
        <button type="submit" class="btn1">Türkçe</button> / 
        <button type="submit" class="btn1">English</button>
        </div>
        <p><a href="yeniuser.php"> Yeni Kullanıcı</p>
    </form>
</body>
 
<script>
        function togglePasswordVisibility() {
            var passwordInput = document.getElementById('password');
            var toggleIcon = document.getElementById('toggleIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text1';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            }
        }
    </script>
    <script>
 document.getElementById('telefon').addEventListener('input', function(e) {
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