<?php
/**
 * Created by PhpStorm.
 * User: maximiliano
 * Date: 03/08/17
 * Time: 11:03
 */

require_once DIR_APPLICATION . '../admin/resources/todopago/Logger/loggerFactory.php';

class ModelTodopagoGetstatus extends Model
{
    public function callATodoPago($action, $orderId)
    {
        if (function_exists('curl_version'))
            $ch = curl_init();
        else {
            $this->logger->warn("Instale el módulo CURL");
        }
        if (isset($ch)) {
            curl_setopt($ch, CURLOPT_URL, $action);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "order_id=$orderId");
            curl_setopt($ch, CURLOPT_HEADER, false);
            // receive server response ...
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close($ch);
            return json_decode($server_output);
        } else {
            return 503;
        }
    }
}