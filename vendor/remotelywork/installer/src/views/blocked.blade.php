<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;500&display=swap" rel="stylesheet">
    <title>Invalid License</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Jost', sans-serif;
        }

        .dark-bg {
            background: #032836;
            color: #fff;
        }

        .light-bg {
            background: #fff;
            color: #001219b3;
        }

        .unusual-page {
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .unusual-page .unusual-page-img {
            height: 250px;
            margin-bottom: 40px;
        }

        @media (max-width: 991px) {
            .unusual-page .unusual-page-img {
                height: 150px;
            }
        }

        .unusual-page .title {
            font-size: 62px;
            font-weight: 700;
            margin-bottom: 30px;
        }

        .back-to-home-btn {
            display: inline-block;
            padding: 13px 27px;
            border-radius: 50px;
            color: #ffffff;
            background: #e73667;
            font-weight: 500;
            font-size: 13px;
            text-transform: uppercase;
            box-shadow: 0px 0px 2px #00304966;
            text-decoration: none;
            margin-top: 20px;
        }

        @media (max-width: 991px) {
            .unusual-page .title {
                font-size: 42px;
            }
        }

        .unusual-page .description {
            font-size: 22px;
            font-weight: 300;
        }

        @media (max-width: 991px) {
            .unusual-page .description {
                font-size: 18px;
            }
        }

    </style>
</head>

<body class="dark-bg">
    <div class="unusual-page">
        <img class="unusual-page-img"
            src="{{ asset('global/installer/images/wrong.webp') }}"
            alt="" style="height: 250px; margin-bottom: 40px; max-width: 100%">
        <h2 class="title" style="font-size: 62px; font-weight: 700; margin-bottom: 30px;">Your license key is wrong!</h2>
        <p class="description" style="font-size: 22px; font-weight: 300;">Use a valid license to active the website.</p>
        <p class="description" style="font-size: 22px; font-weight: 300;">If you think it's wrong action, Contact our support. <br> Support Email : support@tdevs.co</p>
    </div>
</body>

</html>
