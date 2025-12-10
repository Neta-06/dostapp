<!DOCTYPE html>
<html lang="tr-TR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form  method="POST" action="kayit.php">
                <div class="row mb-3">
                  <label for="inputText" class="col-sm-2 col-form-label">Telefon No</label>
                  <div class="col-sm-10">
                    <input type="text" name="telefon" id="telefon" class="form-control">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="inputEmail" class="col-sm-2 col-form-label">Şifre</label>
                  <div class="col-sm-10">
                    <input type="password" name="password" id="password" class="form-control">
                  </div>
                </div>
                <button type="submit">Kayıt Et</button>
</form>

</body>
</html>