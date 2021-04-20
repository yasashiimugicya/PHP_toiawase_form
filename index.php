<?php

define('MAIL_INQUIRY', 'schoo.iwata@gmail.com');
session_start();

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    // CSRF対策
    if (!isset($_POST['token']) || $_POST['token'] !== getToken()) {
        exit('処理を正常に完了できませんでした');
    }

    // バリデーション
    $inquiry = $_POST['inquiry'];
    $name    = $_POST['name'];
    $email   = $_POST['email'];
    $error   = array();

    if (empty($inquiry)) {
        $error['inquiry'] = '必ずご記入下さい';
    }

    if (empty($name)) {
        $error['name'] = '必ずご記入下さい';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // 注意: FILTER_VALIDATE_EMAIL は一部の有効なメールも弾きます。
        // 案件次第では正規表現の使用も必要です。
        $error['email'] = 'メールアドレスの形式が正しくありません';
    }

    // バリデーションエラーが無い場合お問い合わせを受付
    if (empty($error)) {

        $to      = MAIL_INQUIRY;
        $subject = "お問い合わせ: " . $name . '様より';
        $message = "email:\n" . $email . "\n問合せ本文:\n" . $inquiry;
        mb_language('Japanese');
        mb_internal_encoding('UTF-8');
        $flg = mb_send_mail($to, $subject, $message);

        if ($flg) {
            header('Location: thanks.html');
            exit;
        }
        exit('お問い合わせの受付に失敗しました');
    }
}

/**
 * HTMLの特殊文字をエスケープして返す
 */
function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * CSRF対策用 トークンを取得
 */
function getToken()
{
    return hash('sha256', session_id());
}

?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>お問い合わせフォーム</title>
</head>
<body>
    <h1>お問い合わせフォーム</h1>
    <form action="" method="post">
        <input type="hidden" name="token" value="<?php echo getToken(); ?>">
        <p>お問い合わせ内容 ※必須</p>
        <?php if (isset($error['inquiry'])) echo h($error['inquiry']); ?>
        <p><textarea name="inquiry" required rows="10" cols="100" maxlength="1000" minlength="10" placeholder="できるだけ詳しく入力して下さい (10文字以上 1000文字以内)"><?php if (isset($inquiry)) echo h($inquiry); ?></textarea></p>
        <p>お名前 ※必須</p>
        <?php if (isset($error['name'])) echo h($error['name']); ?>
        <p><input type="text" name="name" required vale="<?php if (isset($name)) echo h($name); ?>" placeholder="お名前" ></p>
        <p>ご連絡用Email ※必須</p>
        <?php if (isset($error['email'])) echo h($error['email']); ?>
        <p><input type="email" name="email" required vale="<?php if (isset($email)) echo h($email); ?>" placeholder="email@example.com" ></p>
        <p><input type="submit" value="送信"></p>
    </form>
</body>
</html>
