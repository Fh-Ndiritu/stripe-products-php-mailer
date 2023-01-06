<?php
  require __DIR__.'/PHPMailer-master/src/Exception.php';
  require __DIR__.'/PHPMailer-master/src/PHPMailer.php';
  require __DIR__.'/PHPMailer-master/src/SMTP.php';
  
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\SMTP;
  use PHPMailer\PHPMailer\Exception;

$logo = "https://amaze-yourself.com/wp-content/uploads/2021/07/Amaze_yourself_logo_kleur.png";
$key = "your_stripe_secret_key";


$body = @file_get_contents('php://input');
$event_json = json_decode($body);

// for extra security, retrieve from the Stripe API
$event_id = $event_json->id;
$request_url = "https://api.stripe.com/v1/events/$event_id";

$curl = curl_init($request_url);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer $key",
  'Content-Type: application/json'
]);

$event = json_decode(curl_exec($curl));
curl_close($curl);

if ($event->type !== 'checkout.session.completed') return;

$customer_email = $event->data->object->customer_details->email;
$customer_name = $event->data->object->customer_details->name;

$request_url = "https://api.stripe.com/v1/checkout/sessions/".$event->data->object->id."/line_items";


$curl = curl_init($request_url);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
  "Authorization: Bearer $key",
  'Content-Type: application/json'
]);

$response = json_decode(curl_exec($curl));
curl_close($curl);

$stripe_id = $response->data[0]->price->id;



$servername = "localhost";
$database = "database_name";
$username = "username";
$password = "password";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
  echo $conn->connect_error;
  die();
}


$sendables =  $plain =  $product_name = "";
$i = 1;

$sql = "SELECT * FROM stripe_products WHERE stripe_id = '$stripe_id'";
$result = $conn->query($sql);
if($result->num_rows<1) return;

while ($row = $result->fetch_object()) {
    if ($i == 1) { $product_name = $row->stripe_name; }
    $sendables .="$i: &nbsp;&nbsp;&nbsp;<a target='_blank' href='$row->link'> $row->file_name</a> <br/><br/>";
    $plain .="$row->file_name : $row->link";
    $i++;
}

$body = 
  "<div> 
    <h2>Thank you for purchasing '$product_name' </h2> <br/><br/> <p>
    To access the content, please click or download from the links below:</p>
  </div>";

  $plain_body = "Thank you for purchasing '$product_name'  <br/><br/>$plain.";
  

$body .=$sendables;
//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = 3;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'server_name';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'sending_email';                     //SMTP username
    $mail->Password   = 'sending_email_password';                               //SMTP password
    $mail->SMTPSecure = 'tls';            //Enable implicit TLS encryption
    $mail->Port       = 587;       
    $mail->SMTPOptions = array(
        'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
        )
        );                             //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('sender_email', 'Name of Sender');
    $mail->addAddress($customer_email, " $customer_name");     //Add a recipient

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Thank you for your purchase!';
    $mail->Body    = $body."<br/><br/> <img src='$logo'>";
    $mail->AltBody = $plain_body;
    $mail->send();
    
    //Admin Recipients
    $mail->setFrom('sender_email', 'Name of Sender');
    $mail->addAddress('admin_email', "Admin");    

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'New Purchase';
    $mail->Body    = "Sent to $customer_email <br/> $sendables";
    $mail->send();

    
    echo "Message has been sent";
    
} catch (Exception $e) {
  echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo} ";
}

?>
