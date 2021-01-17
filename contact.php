<?php
session_start();
header('X-FRAME-OPTIONS: SAMEORIGIN');

require_once 'libs/functions.php';
require_once 'libs/mailvars.php';


//csrf対策
if (empty($_SESSION['token'])) {
  $token = bin2hex(openssl_random_pseudo_bytes(24));
  $_SESSION['token'] = $token;
} else {
  $token = $_SESSION['token'];
}


//postデータの管理
$name= isset($_POST['name']) ? $_POST['name'] : NULL;
$email= isset($_POST['email']) ? $_POST['email'] : NULL;
$subject= isset($_POST['subject']) ? $_POST['subject'] : NULL;
$body= isset($_POST['body']) ? $_POST['body'] : NULL;







//送信ボタンが押されている場合
if(isset($_POST['submitted'])) {

  //各種バリデーション
  $_POST = checkInput($_POST);

  if(isset($_POST['name'])) {
    $name = str_replace(array("\r", "\n", "\%0a", "%0d"), '', $_POST['name']);
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
  }

  if(isset($_POST['email'])) {
    $email = str_replace(array("\r", "\n", "\%0a", "%0d"), '', $_POST['email']);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
  }

  if(isset($_POST['subject'])) {
    $subject = str_replace(array("\r", "\n", "\%0a", "%0d"), '', $_POST['subject']);
    $subject = filter_var($_POST['subject'], FILTER_SANITIZE_STRING);
  }

  if(isset($_POST['body'])) {
    $body = filter_var($_POST['body'], FILTER_SANITIZE_STRING);
  }






  //メール本文を組み立てる
  $mail_body = 'お問い合わせ' . "\n\n";
  $mail_body .= "お名前: ". e($name). "\n";
  $mail_body .= "Email: ". e($email). "\n";
  $mail_body .= "お問い合わせ内容:\n\n". e($body);


  $mailTo = mb_encode_mimeheader(MAIL_TO_NAME) ."<" . MAIL_TO . ">";
  
  mb_language('ja');
  mb_internal_encoding('UTF-8');


  //$header = "From: " . mb_encode_mimeheader($name) ."<" . $email. "<\n";  //レンタルサーバーのため不要


  $token = filter_input(INPUT_POST, 'token');
  if (empty($_SESSION['token']) || $token !== $SESSION['token']) {
    die('正規の画面からご利用ください。');
  }
  $result = mb_send_mail($mailTo, $subject, $mail_body);

  //メールが送信された場合の処理
  //変数を空にする
  if($result) {
    $_POST = array();

    //変数の値を初期化
    $name = '';
    $email = '';
    $subject = '';
    $body = '';

    //二重送信を防止する
    $params = '?result' .$result;
    $url = (empty($_SERVER['HTTPS']) ?'http://' : 'https://'). $_SERVER['HTTP_HOST']. $_SERVER['SCRIPT_NAME'];

    header('Location: '. $url . $params);
    exit;
  }
}



?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="contact.css">
  <title>お問い合わせ</title>
</head>
<body>
  
  <div class="container">
    
    <div class="form-wrapper">

      <?php if (isset($_GET['result']) && $result): ?>
        <h3>送信が完了しました。</h3>

      <?php elseif (isset($result) && !$result): ?>
        <h3>送信失敗</h3>
        <p>送信に失敗しました。</p>
        <p>もう一度お試しください。</p>


      <?php endif ?>


      <form action="contact.php" method="post">

        <label for="contact_name">お名前：</label><br>
          <input type="text" id="contact_name" name="name" required value="<?php echo e($name)?>">
          <br>

        <label for="contact_email">メールアドレス：</label><br>
          <input type="text" id="contact_email" name="email" required value="<?php echo e($email)?>">
          <br>

          <label for="contact_email">件名：</label><br>
          <input type="text" id="contact_subject" name="subject" required value="<?php echo e($subject)?>">
          <br>
        
          <label for="contact_body">お問い合わせ内容：</label><br>
          <textarea name="body" id="contact_body" cols="30" rows="10" required><?php echo e($body)?></textarea>
          <br>

          <button name="submitted" type="submit">送信</button>

      </form>
    </div>

  </div>

</body>
</html>