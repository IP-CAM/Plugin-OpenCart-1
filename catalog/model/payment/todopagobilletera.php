<?php
require_once DIR_APPLICATION . '/model/payment/todopago.php';

class ModelPaymentTodopagoBilletera extends ModelPaymentTodopago
{

    public function __construct($registry)
    {
        parent::__construct($registry);
    }

    public function getMethod($address, $total)
    {
        $query = "SELECT value FROM " . DB_PREFIX . "setting WHERE `key` = 'todopago_bannerbilletera';";
        $queryResult = $this->db->query($query);

        $logoBannerBilletera = $queryResult->row['value'];

        $method_data = array(
            'code' => 'todopagobilletera',
            'title' => '<img src="https://todopago.com.ar/sites/todopago.com.ar/files/billetera/'.$logoBannerBilletera.'.jpg">',
            'terms' => '',
            'sort_order' => $this->config->get('todopago_sort_order')
        );

        return $method_data;
    }

  
}
