<?php

$mail = filter_var( $_POST['mail'], FILTER_SANITIZE_EMAIL);
$pass = filter_var( $_POST['pass'], FILTER_SANITIZE_STRING); 
$ambiente = $_POST["tab"];
$data = array("USUARIO"=> $mail, "CLAVE"=>$pass);

$end_point = ($ambiente == "test") ? "https://developers.todopago.com.ar/api/Credentials":"https://apis.todopago.com.ar/api/Credentials";

$curl = curl_init();

curl_setopt_array($curl, array(
                                CURLOPT_URL => $end_point,
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => "",
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 30,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => "POST",
                                CURLOPT_POSTFIELDS => json_encode($data),
                                CURLOPT_HTTPHEADER => array(
                                  "cache-control: no-cache",
                                  "content-type: application/json",
                                ),
                              )
                  );
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
