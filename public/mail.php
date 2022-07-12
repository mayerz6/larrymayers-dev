<?php


 require './assets/PHPMailer/class.phpmailer.php';
 require './assets/PHPMailer/class.pop3.php';
 require './assets/PHPMailer/class.smtp.php';
 require './assets/PHPMailer/class.phpmaileroauth.php';
 require './assets/classes/User.php';

 
// use PHPMailer;
//use PHPMailer\PHPMailer\Exception;

extract($_POST);

// $bevCost = isset($_POST['bevCost']) ? $_POST['bevCost'] : '';

$valStatus = true;
$u = New User;

$errMsg = array();

if(!isset($email) || empty($email)){
    $valStatus = false;
    $errMsg[] = "Blank firstname entries are NOT allowed!!!";
}

if(!isset($msgTopic) || empty($msgTopic)){
    $valStatus = false;
    $errMsg[] = "Blank surname entries are NOT allowed!!!";
}

if(!isset($usrMsg) || empty($usrMsg)){
    $valStatus = false;
    $errMsg[] = "Blank contact entries are NOT allowed!!!";
}

if( isset($email) && isset($msgTopic) && isset($usrMsg) ){

    echo json_encode(
        array(
            'ok' => $valStatus,
            'messages' => $errMsg
        )
        );
    
        switch ($msgTopic)  
        {     
            case 1:   
                $msgTopic = "Technical Consultancy";  
                     break;  
            case 2:   
                $msgTopic = "Mentorship Queries";  
                     break;  
            case 3:   
                $msgTopic = "Job Interests";  
                     break;  
            case 4:   
                $msgTopic = "General Feedback";  
                     break;    
        } 
    
        //PHPMailer Object
    $mail = new PHPMailer(true); //Argument true in constructor enables exceptions
    $mail->isSMTP();
    $mail->isHTML(true);
    
    
    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 2;
    
    //Ask for HTML-friendly debug output
    $mail->Debugoutput = 'html';
    
    //Set the hostname of the mail server
    //$mail->Host = 'imap.titan.email';
    $mail->Host = User::getHost();
    // use
    // $mail->Host = gethostbyname('smtp.gmail.com');
    // if your network does not support SMTP over IPv6
    
    //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
    $mail->Port = 587;
    
    //Set the encryption system to use - ssl (deprecated) or tls
    $mail->SMTPSecure = 'tls';
    
    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;
    
    
    //$mail->Username = "info@larrymayers.site";
    $mail->Username = User::getEmail();
   // $mail->Password = "M@y3rZ.S0urc#!9a";
    $mail->Password = User::getPwd();
    
    //From email address and name
   // $mail->From = "info@larrymayers.site";
    $mail->From = User::getEmail();;
    $mail->FromName = "Larry Mayers";
    
    //To address and name
    $mail->addAddress("larry.mayers@outlook.com", "Larry Mayers");
    // $mail->addAddress("info@larrymayers.site", "Larry Mayers"); //Recipient name is optional
    
    //Address to which recipient will reply
    // $mail->addReplyTo("larrymayers101@gmail.com", "Reply");
    
    //CC and BCC
     //$mail->addCC("info@larrymayers.site");
    //$mail->addBCC("larrymayers101@gmail.com");
    
    //Send HTML or Plain Text email
    $mail->isHTML(true);
    
    //Set the subject line
    $mail->Subject = "Larry Mayers Site - Contact Form Messages";
    $mail->Body = "<p><b>" . $msgTopic . "</b> - Please review the received message below.</p><br>" . $usrMsg;
    //$mail->AltBody = "This is the plain text version of the email content";
    
    
    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
    // $mail->msgHTML(file_get_contents('contents.html'));
    
    //Replace the plain text body with one created manually
    // $mail->AltBody = 'This is a plain-text message body';
    
    //Attach an image file
    // $mail->addAttachment('images/phpmailer_mini.png');
    
    
    
    try {
        $mail->send();
        echo "Message has been sent successfully";
       
    
    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
    
}
