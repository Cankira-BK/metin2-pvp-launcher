<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>NUYA2 - MKD BILISIM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <style>
        body {
            font-family: Arial;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            color: #ffffff;
        }
        a, a:hover { color: #ffffff; }
        h1 { font-size: 16px; line-height: 14px; font-weight: bold; margin-bottom: -5px; }
        p { font-size: 12px; line-height: 15px; font-weight: normal; }
        .background {
            background: url('images/bg.png') no-repeat;
            display: block;
            height: 300px;
            left: 0;
            position: relative;
            top: 0;
            width: 560px;
        }
        .allwidth {
            position: absolute;
            top: 5px;
            left: 5px;
            width: 552px;
            height: 280px;
        }
        .text01 {
            margin-top: 135px;
            margin-left: 75px;
            width: 405px;
            height: 135px;
            display: block;
        }
        .footer {
            clear: both;
            color: white;
            display: block;
            font-family: Verdana,Arial,Helvetica,sans-serif;
            font-size: 12px;
            font-weight: bold;
            left: 0;
            margin: 0;
            overflow: hidden;
            position: absolute;
            text-align: center;
            top: 288px;
            width: 560px;
            height: 25px;
            z-index: 10;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetch('api/content.php')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('icerik').innerHTML = data.content;
                    } else {
                        document.getElementById('icerik').innerHTML = "İçerik yüklenemedi!";
                    }
                })
                .catch(() => {
                    document.getElementById('icerik').innerHTML = "Bağlantı hatası!";
                });
        });
    </script>
</head>
<body>
    <div class="background">
        <div class="allwidth" style="background:url('images/bg_01_city.jpg') no-repeat;">
            <div class="text01" dir="ltr">
                <div id="icerik">Yükleniyor...</div>
            </div>
        </div>
    </div>
</body>
</html>
