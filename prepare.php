<?php

error_reporting(0);
header('Content-Type: text/json');
header('Charset: UTF-8');

$request = $_POST;

$merchant_id = 'SIZNING_MERCHANT_ID';
$service_id = 'SIZNING_SERVICE_ID';
$merchant_user_id = 'SIZNING_MERCHANT_USER_ID';
$secret_key = 'SIZNING_SECRET_KEY';

// Проверка отправлено-ли все параметры
if (
    !(
        isset($request['click_trans_id']) &&
        isset($request['service_id']) &&
        isset($request['merchant_trans_id']) &&
        isset($request['amount']) &&
        isset($request['action']) &&
        isset($request['error']) &&
        isset($request['error_note']) &&
        isset($request['sign_time']) &&
        isset($request['sign_string']) &&
        isset($request['click_paydoc_id'])
    )
) {
    echo json_encode(array(
        'error' => -8,
        'error_note' => 'Error in request from click'
    ));
    exit;
}

// Проверка хеша
$sign_string = md5(
    $request['click_trans_id'] .
    $request['service_id'] .
    $secret_key .
    $request['merchant_trans_id'] .
    $request['amount'] .
    $request['action'] .
    $request['sign_time']
);

// check sign string to possible
if ($sign_string != $request['sign_string']) {
    echo json_encode(array(
        'error' => -1,
        'error_note' => 'SIGN CHECK FAILED!'
    ));
    exit;
}

if ((int) $request['action'] != 0) {
    echo json_encode(array(
        'error' => -3,
        'error_note' => 'Action not found'
    ));
    exit;
}

// merchant_trans_id - это ID пользователья который он ввел в приложении
// Здесь нужно проверить если у нас в базе пользователь с таким ID

$user = $request['merchant_trans_id'];
if (!$user) {
    echo json_encode(array(
        'error' => -5,
        'error_note' => 'User does not exist'
    ));
    exit;
} else {
    $url = "MANZIL";
    $host = "HOST";
    $user_d = "USER";
    $password = "PAROL";
    $db = "DATA_BASE_NAME";
    $link = mysqli_connect($host, $user_d, $password, $db);
    if (!$link) {
        exit();
    } else {
        $sql = mysqli_query($link, "SELECT * from user_temp WHERE telefon='$user' order by id desc");
        $row = mysqli_fetch_array($sql, MYSQLI_BOTH);
        $name = $row['ism'];
        $telefon = $user;
        $login = $row['login'];
        $parol = $row['parol'];
        $faoliyat = $row['faoliyat'];
        $rol = "user";

        $sql = mysqli_query($link, "INSERT INTO user (ism,login,telefon,parol,faoliyat,rol) VALUES ('$name','$login','$telefon','$parol','$faoliyat','$rol')");
        $sql = mysqli_query($link, "SELECT * from user WHERE telefon='$telefon' order by id desc");
        $data = mysqli_fetch_array($sql, MYSQLI_BOTH);
        $log_id = $data['id'];
    }
}

// Все проверки прошли успешно, тог здесь будем сохранять в базу что подготовка к оплате успешно прошла
// можно сделать отдельную таблицу чтобы сохранить входящих данных как лог
// и присвоит на параметр merchant_prepare_id номер лога

echo json_encode(array(
    'error' => 0,
    'error_note' => 'Success',
    'click_trans_id' => $request['click_trans_id'],
    'merchant_trans_id' => $request['merchant_trans_id'],
    'merchant_prepare_id' => $log_id,
));
exit;
?>