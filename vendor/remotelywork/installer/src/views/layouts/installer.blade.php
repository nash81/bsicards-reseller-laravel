<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
        integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="shortcut icon" href="https://s3.envato.com/files/423311610/tdevs-logo.jpg" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <title>@yield('title') - Script Installer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Manrope', sans-serif;
            background: #f9f9f9;
            font-size: 14px;
            font-weight: 500;
        }

        body p {
            font-size: 14px;
        }

        button,
        a {
            border: 0;
            text-decoration: none;
        }

        /* .container {
				width: 1100px;
			} */
        .top {
            margin-top: 60px;
        }

        .top h3 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .top p {
            font-size: 14px;
            margin-bottom: 20px;
            font-weight: 500;
            color: #8b8b8b;
        }

        .content {
            background: #fff;
            padding: 30px;
            border-radius: 4px;
            box-shadow: 0 0 2px #7e799366;
            margin-bottom: 45px;
        }

        .content .title {
            font-size: 20px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .content p {
            color: #8b8b8b;
        }

        .single-box {
            margin-bottom: 25px;
        }

        .single-box .form-label {
            font-weight: 800;
            font-size: 14px;
        }

        .single-box .form-control {
            height: 45px;
            border: 2px solid #ddd;
            border-radius: 4px;
            outline: none;
            font-size: 14px;
            font-weight: 600;
        }

        .single-box .form-control:focus {
            box-shadow: none;
            outline: none;
        }

        .single-box p {
            font-size: 12px;
            font-style: italic;
            margin-top: 2px;
        }

        .single-box .form-select {
            width: 100%;
            height: 45px;
            border: 2px solid #e5e8f2;
            box-sizing: border-box;
            border-radius: 4px;
            padding: 0 15px;
            transition: 0.3s;
            font-size: 14px;
            font-weight: 500;
            outline: none;
        }

        .single-box .form-select:focus {
            box-shadow: none;
            outline: none;
        }

        .single-box .submit-btn {
            display: inline-block;
            padding: 12px 22px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 14px;
            text-transform: capitalize;
            color: #ffffff;
            background: #5e3fc9;
            text-align: center;
        }
        .single-box .submit-btn:hover {
            background: #3c1fa1;
            color: #ffffff;
        }

        .table tbody tr td {
            font-size: 14px;
            font-weight: 700;
            color: #5c5c5c;
        }

        .step-progress {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            margin-bottom: 60px;
            position: relative;
        }
        .step-progress::after {
            position: absolute;
            content: "";
            width: 100%;
            height: 5px;
            background: #D11A6947;
            top: 65%;
            transform: translateY(-65%);
            z-index: -1;
        }
        .step-progress .single-step {
            text-align: center;
        }
        .step-progress .single-step h4 {
            font-size: 16px;
            font-weight: 700;
            color: #8b8b8b;
        }
        .step-progress .single-step .icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0px 0px 2px rgb(209 26 105);
        }
        .step-progress .single-step.finished h4 {
            color: #D11A69;
        }
        .step-progress .single-step.finished .icon {
            background: #D11A69;
        }
        .step-progress .single-step .icon img {
            width: 25px;
        }
        .top {
            margin-top: 60px;
        }
        .top h3 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 20px;
            text-align: center;
        }
        .top p {
            font-size: 14px;
            margin-bottom: 20px;
            font-weight: 500;
            color: #8b8b8b;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                @yield('steps')
            </div>
        </div>
        @yield('content')
    </div>
</body>

</html>
