<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!empty($_GET)) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.cashfree.com/pg/orders/" . $_GET['order_id'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "accept: application/json",
            "x-api-version: 2022-09-01",
            "x-client-id: <Your key>",
            "x-client-secret: <Your key>"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $response_data = json_decode($response);
        $payment_session_id = $response_data->payment_session_id;
        $order_status = $response_data->order_status;
        $order_id = $response_data->order_id;

        echo <<<HTML
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var paymentForm = document.getElementById('paymentForm');
                var orderConfirmed = document.getElementById('orderConfirmed');

                if ('$order_status' === 'PAID') {
                    paymentForm.style.display = 'none';
                    orderConfirmed.style.display = 'block';
                }
                 else if ('$order_status' === 'ACTIVE'){
                    paymentForm.style.display = 'none';
                    orderFailed.style.display = 'block';
                }
            });
            console.log("dfas")
        </script>
        HTML;
    }
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $contact = $_POST['contact'];
        $amount = $_POST['amount'];
        $description = $_POST['description'];
        $params = http_build_query(
            array(
                'name' => $name,
                'email' => $email,
                'contact' => $contact,
                'amount' => $amount,
                'description' => $description
            )
        );

        $url = 'https://www.your-domain.in/CreateTransaction?' . $params;
        $response = file_get_contents($url);
        $tid = json_decode($response)->tid;

        $order_id = $tid;

        $customer_id = uniqid();

        $curl = curl_init();

        $data = json_encode([
            'customer_details' => [
                'customer_phone' => $contact,
                'customer_id' => $customer_id,
                'customer_email' => $email
            ],
            'order_id' => "ISXP" . $order_id,
            'order_amount' => $amount,
            'order_note' => $description,
            'order_currency' => 'INR'
        ]);

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.cashfree.com/pg/orders",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "content-type: application/json",
                "x-api-version: 2022-09-01",
                "x-client-id: <Your key>",
                "x-client-secret: <Your key>"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $response_data = json_decode($response);
            $payment_session_id = $response_data->payment_session_id;
            $order_status = $response_data->order_status;
            $order_id = $response_data->order_id;

            echo '<input type="hidden" id="payment_session_id" value="' . $payment_session_id . '">';

            echo <<<HTML
            <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const cashfree = new Cashfree({mode: 'production'});
                    localStorage.setItem('data', '$payment_session_id');

                    if ('$order_status' === 'PAID') {
                        var paymentForm = document.getElementById('paymentForm');
                        var orderConfirmed = document.getElementById('orderConfirmed');
                        paymentForm.style.display = 'none';
                        orderConfirmed.style.display = 'block';
                    } else if ('$order_status' === 'ACTIVE') {
                        cashfree.checkout({
                            paymentSessionId: '$payment_session_id',
                            returnUrl: 'https://your-domain.in/payment-gatway/index.php?order_id={$order_id}',
                            redirectTarget: '_top'
                        }).then(function() {
                            console.log('on going redirection');
                        });
                    } else {
                        console.log('Payment status is not paid or active.');
                    }
                });
            </script>
            HTML;
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Payment Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&family=Playfair+Display:wght@600&display=swap"
          rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-size: cover;
            background-position: center;
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
        }

        .content-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            margin: 0 auto;
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 10px;
            max-width: 1000px;
            background-image: url("https://www.dbs.com/in/iwov-resources/media/images/learn/banners/what-is-international-debit-card-1404x630.jpg");
            background-color: rgba(0, 0, 0, 0.732);
            background-size: cover;
            background-position: center;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: -4px 32px 47px rgba(5, 1, 2, 1);
        }

        .payment-container {
            flex: 1;

        }

        .company-container {
            flex: 1;
            padding: 20px;

        }

        a {
            color: white
        }

        #paymentForm {
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);

        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #555;
            font-weight: bold;
        }

        #name, #email, #contact, #amount {
            width: 100%;
            padding: 12px;
            border: 2px solid #ff6666;
            border-radius: 40px;
            font-size: 16px;
            color: #333;
        }
        #description{
            width: 100%;
            padding: 12px;
            border: 2px solid #ff6666;
            border-radius: 20px;
            font-size: 16px;
            color: #333;
        }

        input[type="submit"] {
            display: block;
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        #orderConfirmed,
        #orderFailed {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 8px;
            background-color: #f9f9f9ae;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #orderConfirmed h3,
        #orderFailed h3 {
            color: #333;
            font-size: 40px;
            margin-bottom: 10px;
        }

        #orderConfirmed p,
        #orderFailed p {
            color: #666;
            font-size: 16px;
            margin-bottom: 20px;
        }

        #orderFailed a {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        #orderFailed a:hover {
            background-color: #45a049;
        }

        .image-square img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }


        .company-information h2 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }

        .company-information p {
            font-size: 16px;
            line-height: 1.4;
            color: #666;
        }


        @media screen and (max-width: 767px) {

            .company-container,
            .payment-container {
                margin-top: 0;
                padding: 0;
            }

            .content-container {
                margin-top: 0;
                margin-bottom: 0;
                padding: 30px;
            }

            #paymentForm {
                max-width: 100%;
            }

        }


        .shake {
            animation: justshake 0.3s forwards;
        }

        @keyframes justshake {
            25% {
                transform: translateX(5px);
            }
            50% {
                transform: translateX(-5px);
            }
            75% {
                transform: translateX(5px);
            }
            100% {
                transform: translateX(-5px);
            }
        }


    </style>
</head>

<body>

<!-- Header Start -->
<div class=" px-0">
    <div class="row gx-0">
        <div class="col-lg-5 d-none d-lg-block">
            <a href="http://www.google.com"
               class="navbar-brand w-100 h-100 m-0 ml-5 p-0 d-flex align-items-center justify-content-start">
                <img src="logo.png" width="150" height="80" alt=logo"">
            </a>
        </div>
        <div class="col-lg-7">
            <nav class="navbar navbar-expand-lg  p-1 p-lg-0">
                <a href="http://www.google.com" class="navbar-brand d-block d-lg-none">
                    <img src="logo.png" width="150" height="80" alt="">
                </a>
                <button type="button" class="navbar-toggler" data-bs-toggle="collapse"
                        data-bs-target="#navbarCollapse">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end m-3" id="navbarCollapse">
                    <ul class="navbar-nav">
                     
                        <li class="nav-item">
                            <a href="#" class="nav-link active">Pay Now</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</div>
<!-- Header End -->


<div class="content-container">

    <div class="company-container d-md-block  d-none d-lg-block">
        <div style="font-family: 'Playfair Display', serif;">
            <div>
                <div>
                    <h1 style="text-align: left;"><strong>Lorem ipsum dolor sit amet consectetur</strong></h1>
                    <div>&nbsp;</div>
                    <div style="text-align: left;">
                        <div>
                            <div>
                                <p>Thank you for choosing Company Name for your business.</p>
                                <p>&nbsp;</p>
                                <p>Lorem, ipsum dolor sit amet consectetur adipisicing elit. Tempore dicta a deserunt iusto aut, exercitationem culpa officia atque, eos ea id quod nemo, asperiores autem dolor error esse est voluptas.</p>
                                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Laudantium aspernatur accusantium ex sit sed nam commodi consequatur inventore quidem suscipit, culpa temporibus voluptates molestias. Ipsam perspiciatis fuga voluptatibus blanditiis cupiditate.</p>
                                <p>&nbsp;</p>
                            </div>
                        </div>
                    </div>
                    <div style="text-align: left;">
                        <p>Contact Us:</p>

                        <a
                                target="_blank" rel="noopener noreferrer"
                                style="text-decoration: none; font-size: 25px">company_web.in</a></div>
                    <a href="tel:8882702020" style="text-decoration: none; font-size: 25px">+91 9090XXXXX</a>
                </div>
            </div>
        </div>
    </div>
    <div class="payment-container">
        <div id="paymentForm">
            <div class="d-md-none  d-sm-block d-lg-none " style="display: flex;justify-content:center;"><h2>Payment
                    Form</h2></div>


            <form action="" method="POST">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" placeholder="John Doe" onfocusout="myFunction1()" required><br><br>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="example@example.com" onfocusout="myFunction2()"
                       required><br><br>

                <label for="contact">Contact:</label>
                <input type="tel" maxlength="10" id="contact" pattern="\d+" name="contact" placeholder="Mobile No."
                       onfocusout="myFunction3()" required><br><br>

                <label for="amount">Amount:</label>
                <input type="number" min="1" id="amount" name="amount" placeholder="Your Amount"
                       onfocusout="myFunction4()" required><br><br>
                <label for="description">Description:</label>
                <textarea type="text" min="1" id="description" name="description" placeholder="Tell us about your payment"
                          required></textarea><br><br>

                <input type="submit" id="submit" name="submit" onclick="myFunction5()" value="Pay">
            </form>


        </div>
        <div id="orderConfirmed" style="display: none;">
            <br>

            <h3><i class="fa-regular fa-circle-check fa-bounce" style="color: forestgreen; font-size: 101px"></i>
                <br><br>Order Confirmed
            </h3>
            <p style="color: black">Have a doubt or want quick assistance, call us on +91-8882702020 or Say Hi to us on
                our 24x7 Live Chat.</p>
        </div>

        <div id="orderFailed" style="display: none;">
            <br>
            <i class="fa-regular fa-circle-xmark fa-beat" style="color: darkred;font-size: 101px"></i>




            <br>
            <h3 style="margin-top: 25px">Order Failed</h3>
            <p> Your order has been Failed !</p>
            <a href="https://www.your-domain.in/payment-gatway/index.php" onclick="location.reload();">Retry</a>
        </div>
    </div>
</div>


<!-- Footer -->




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous">
</script>


<script>
    function myFunction1() {
        let name = document.getElementById("name").value;
        let lettersOnlyRegex = /^[A-Za-z.'\- ]+$/;
        if (name === "" || !name.match(lettersOnlyRegex)) {
            document.getElementById("name").classList.add("shake");
            setTimeout(function () {
                document.getElementById("name").classList.remove("shake");
            }, 300);
        }
    }

    function myFunction2() {
        var email = document.getElementById("email").value;
        let emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
        if (email === "" || !email.match(emailRegex)) {
            document.getElementById("email").classList.add("shake");
            setTimeout(function () {
                document.getElementById("email").classList.remove("shake");
            }, 300);
        }
    }

    function myFunction3() {
        var contact = document.getElementById("contact").value;
        let contactRegex = /^[1-9]{10}$/;
        if (contact === "" || !contact.match(contactRegex)) {
            document.getElementById("contact").classList.add("shake");
            setTimeout(function () {
                document.getElementById("contact").classList.remove("shake");
            }, 300);
        }
    }

    function myFunction4() {
        var amount = document.getElementById("amount").value;
        var greaterThanZeroRegex = /^[1-9][0-9]*$/;


        if (amount === "" || !amount.match(greaterThanZeroRegex)) {
            document.getElementById("amount").classList.add("shake");
            setTimeout(function () {
                document.getElementById("amount").classList.remove("shake");
            }, 300);
        }
    }

    function myFunction5() {
        var name = document.getElementById("name").value;
        var email = document.getElementById("email").value;
        var contact = document.getElementById("contact").value;
        var amount = document.getElementById("amount").value;
        if (name === "" || email === "" || contact === "" || amount === "") {
            document.getElementById("submit").classList.add("shake");
            setTimeout(function () {
                document.getElementById("submit").classList.remove("shake");
            }, 300);
        }
    }
</script>



</body>

</html>
