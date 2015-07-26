<?php
require_once dirname(__FILE__).'/../todopago/TodoPago/lib/Sdk.php';
include_once dirname(__FILE__).'/../todopago/phone.php';

class ControllerPaymentTodopago extends Controller {
    const NEW_ORDER = 0;
    const FIRST_STEP = 1;
    const SECOND_STEP = 2;
    const TRANSACCION_FINISHED = 3;
    const Y = 'S';
    const N = 'N';

    protected function index() {
        $this->language->load('payment/todopago');
        $this->load->model('todopago/transaccion');

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if ($order_info) {
            $this->data['order_id'] = $order_info['order_id'];

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/todopago.tpl')){
                $this->template = $this->config->get('config_template') . '/template/payment/todopago.tpl';
            } else {
                $this->template = 'default/template/payment/todopago.tpl';
            }
            //$_orderid = $this->data['orderid'];
            $this->data['action'] = $this->config->get('config_url')."index.php?route=payment/todopago/first_step_todopago";
            $this->render();
        }
    }

    public function first_step_todopago()
    {
        //$order_id =$_GET['order_id'];
        $order_id = $_POST['order_id']; 
        $this->load->model('todopago/transaccion');
        $this->model_todopago_transaccion->createRegister($order_id);
        if ($this->model_todopago_transaccion->getStep($order_id) == $this->model_todopago_transaccion->getFirstStep()){
            $this->writeLog($order_id, "first step");

            $this->load->model('checkout/order');
            $order = $this->model_checkout_order->getOrder($order_id);

            //confirma y pasa a pendiente la orden <-- este tienen que se configurable
            $this->model_checkout_order->confirm($order_id, 1);


            $payDataComercial = array();
            $payDataOperacion = array();

            //sacar todos los datos de la orden TODO

            $payData['canaldeingresodelpedido'] = $this->config->get('canaldeingresodelpedido');


            $authorizationHTTP = $this->get_authorizationHTTP();

            $paydata_comercial = $this->getOptionsSARComercio($order_id); 
            $paydata_operation = $this->getOptionsSAROperacion($order_id);

            try{
                $mode = ($this->get_mode()=="Test")?"test":"prod";
                $connector = new TodoPago\Sdk($authorizationHTTP, $mode);
                $paramsSAR['comercio'] = $paydata_comercial;
                $paramsSAR['operacion'] = $paydata_operation;
                $this->writeLog($order_id, "params SAR", json_encode($paramsSAR));
                $rta_first_step = $connector->sendAuthorizeRequest($paydata_comercial, $paydata_operation);
                $this->writeLog($order_id, "response SAR", json_encode($rta_first_step));
                $this->db->query("UPDATE ".DB_PREFIX."order SET todopagoclave='".$rta_first_step['RequestKey']."' WHERE  order_id=$order_id;");

                if($rta_first_step["StatusCode"] == -1){
                    $this->model_checkout_order->update($order_id, $this->config->get('todopago_order_status_id_pro'), "TODO PAGO: ".$rta_first_step['StatusMessage']);
                    $query = $this->model_todopago_transaccion->recordFirstStep($order_id, $paramsSAR, $rta_first_step, $rta_first_step['RequestKey'], $rta_first_step['PublicRequestKey']);
                    $this->writeLog($order_id, 'query', $query);
                    header('Location: '.$rta_first_step['URL_Request']);
                    //$this->redirect($rta_first_step['URL_Request']);
                }
                else{
                    $this->model_checkout_order->update($order_id, $this->config->get('todopago_order_status_id_rech'), "TODO PAGO: ".$rta_first_step['StatusMessage']);
                    $this->redirect($this->config->get('config_url')."index.php?route=payment/todopago/second_step_todopago&Order=".$order_id);
                }

            }catch (Exception $e){
                $this->writeLog($order_id, "error", json_encode($e));
                $this->model_checkout_order->update($order_id, $this->config->get('todopago_order_status_id_rech'), "TODO PAGO (Exception): ".$e);
                $this->redirect($this->config->get('config_url')."index.php?route=payment/todopago/_urlerror&Order=".$order_id);

            }
        }
        else{
            $this->writeLog($order_id, "Fallo al iniciar el first step, Ya se encuentra registrado un first step exitoso en la tabla todopago_transaccion");
            $this->redirect($this->url->link('common/home'));
        }

    }

    public function second_step_todopago(){
        $answer = $_GET['Answer'];
        $order_id = $_GET['Order'];
        $this->load->model('todopago/transaccion');
        if($this->model_todopago_transaccion->getStep($order_id) == $this->model_todopago_transaccion->getSecondStep()){
            $this->writeLog($order_id, "second step");

            $authorizationHTTP = $this->get_authorizationHTTP();

            $mode = ($this->get_mode()=="Test")?"test":"prod";
            $connector = new TodoPago\Sdk($authorizationHTTP, $mode);
            $this->load->model('checkout/order');
            $requestKey = $this->db->query("SELECT todopagoclave FROM ".DB_PREFIX."order WHERE order_id=$order_id")->row['todopagoclave'];
            $optionsAnswer = array(
                'Security' => $this->get_security_code(),
                'Merchant' => $this->get_id_site(),
                'RequestKey' => $requestKey,
                'AnswerKey' => $answer
            );
            $this->writeLog($order_id, "params GAA", json_encode($optionsAnswer));
            try{
                $rta_second_step = $connector->getAuthorizeAnswer($optionsAnswer);
                $this->writeLog($order_id, "response GAA", json_encode($rta_second_step));
                if(strlen($rta_second_step['Payload']['Answer']["BARCODE"]) > 0){
                    $nroop =  $order_id;
                    $venc = $rta_second_step['Payload']['Answer']["COUPONEXPDATE"];
                    $total = $rta_second_step['Payload']['Request']['AMOUNT'];
                    $code = $rta_second_step['Payload']['Answer']["BARCODE"];
                    $tipocode = $rta_second_step['Payload']['Answer']["BARCODETYPE"];
                    $empresa = $rta_second_step['Payload']['Answer']["PAYMENTMETHODNAME"];

                    $this->model_checkout_order->update($order_id, $this->config->get('todopago_order_status_id_off'), "TODO PAGO: ".$rta_second_step['StatusMessage']);    
                    $this->redirect($this->url->link("todopago/todopago/cupon&nroop=$nroop&venc=$venc&total=$total&code=$code&tipocode=$tipocode&empresa=$empresa"));  
                }
                
                if($rta_second_step['StatusCode']==-1){
                $this->writeLog($order_id, 'status code', $rta_second_step['StatusCode']);
                    
                    $this->model_checkout_order->update($order_id, $this->config->get('todopago_order_status_id_aprov'), "TODO PAGO: ".$rta_second_step['StatusMessage']);
                    
                    $query = $this->model_todopago_transaccion->recordSecondStep($order_id, $optionsAnswer, $rta_second_step, $answer);
                    $this->writeLog($order_id, "query", $query);
                    $this->redirect($this->url->link('checkout/success'));  
                }
                
                else{
                    $this->writeLog($order_id, 'fail', $rta_second_step['StatusCode']);

                    $this->model_checkout_order->update($order_id, $this->config->get('todopago_order_status_id_rech'), "TODO PAGO: ".$rta_second_step['StatusMessage']);
                    $this->redirect($this->config->get('config_url')."index.php?route=payment/todopago/_urlerror&Order=".$order_id);
                }
            }
            catch(Exception $e){
                $this->model_checkout_order->update($order_id, $this->config->get('todopago_order_status_id_rech'), "TODO PAGO (Exception): ".$e);
                $this->redirect($this->config->get('config_url')."index.php?route=payment/todopago/_urlerror&Order=".$order_id);

            }
        }
        else{
            $this->writeLog($order_id, "Fallo al iniciar el second step, Ya se encuentra registrado un second step exitoso en la tabla todopago_transaccion");
            $this->redirect($this->url->link('common/home'));
        }
    }

    public function _urlerror(){
        $this->data['order_id'] = $_GET['Order'];
        $this->document->setTitle("Fallo en el Pago");

        // define template file
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/todopago/todopago_fail.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/todopago/todopago_fail.tpl';
        } else {
            $this->template = 'default/template/todopago/todopago-fail.tpl';
        }

        // define children templates
        $this->children = array(
            'common/column_left',
            'common/column_right',
            'common/content_top',
            'common/content_bottom',
            'common/footer',
            'common/header'
        );

        // call the "View" to render the output
        $this->response->setOutput($this->render());
    }



    private function getOptionsSARComercio($order_id){
        $paydata_comercial ['URL_OK'] =  $this->config->get('config_url')."index.php?route=payment/todopago/second_step_todopago&Order=".$order_id;
        $paydata_comercial ['URL_ERROR'] = $this->config->get('config_url').'index.php?route=payment/todopago/second_step_todopago&Order='.$order_id;
        $paydata_comercial['Merchant'] = $this->get_id_site();
        $paydata_comercial['Security'] = $this->get_security_code();
        $paydata_comercial['EncodingMethod'] = 'XML';

        return $paydata_comercial;    
    }

    private function getOptionsSAROperacion($order_id){

        $this->load->model('checkout/order');
        $order = $this->model_checkout_order->getOrder($order_id);

        $this->load->model('payment/todopago');

        
        $paydata_operation['MERCHANT'] = $this->get_id_site();
        $paydata_operation['OPERATIONID'] = $order_id;
        $paydata_operation['AMOUNT'] = number_format($order['total'], 2, ".", "");
        $paydata_operation['CURRENCYCODE'] = "032";
        $paydata_operation['EMAILCLIENTE'] = $order['email'];
        
        //CYBERSOURCE GRAL PARAMS
        $paydata_operation['CSBTCITY'] = $order['payment_city'];
        $paydata_operation['CSBTCOUNTRY'] = $order['payment_iso_code_2'];
        $paydata_operation['CSBTCUSTOMERID'] = $order['customer_id'];
        $paydata_operation['CSBTIPADDRESS'] = $order['ip'];
        $paydata_operation['CSBTEMAIL'] = $order['email'];
        $paydata_operation['CSBTFIRSTNAME'] = $order['payment_firstname'];
        $paydata_operation['CSBTLASTNAME'] = $order['payment_lastname'];
        $paydata_operation['CSBTPHONENUMBER'] = phone::clean($order['telephone']);
        $paydata_operation['CSBTPOSTALCODE'] = $order['payment_postcode'];
        $paydata_operation['CSBTSTATE'] = $this->getProvinceCode($order['payment_zone_code'], $order_id);
        $paydata_operation['CSBTSTREET1'] = $order['payment_address_1'];
        //$paydata_operation['CSBTSTREET2'] = $order['payment_address_2']; <- PRISMA dijo
        $paydata_operation['CSPTCURRENCY'] = "ARS";
        $paydata_operation['CSPTGRANDTOTALAMOUNT'] = number_format($paydata_operation['AMOUNT'],2,".","");
        //$paydata_operation['CSMDD6'] = $this->config->get('canaldeingresodelpedido'); //PRISMA pidió que lo saquemos...

        $this->load->model('account/customer');
        $customer = $this->model_account_customer->getCustomer($paydata_operation['CSBTCUSTOMERID']);
        
        if(!empty($customer)){
        $paydata_operation['CSMDD7'] = $this->getDaysQty($customer['date_added']);
            
            $paydata_operation['CSMDD8'] = self::Y;
            
            $paydata_operation['CSMDD9'] = $customer['password'];   
            
            $paydata_operation['CSMDD10'] = $this->getQtyOrders($customer['customer_id']);
            
        } else
        {
            $paydata_operation['CSMDD8'] = self::N;
        }
        
        $paydata_operation['CSMDD11'] = phone::clean($order['telephone']);

        if ($this->config->get('segmentodelcomercio') == 'Retail')
        {
            $paydata_operation['CSSTCITY'] = empty($order['shipping_city'])?$order['payment_city']:$order['shipping_city'];
            $paydata_operation['CSSTCOUNTRY'] = empty($order['shipping_iso_code_2'])?$order['payment_iso_code_2']:$order['shipping_iso_code_2'];
            //CSSTEMAIL <-- falta, ver como solucionar tema.
            $paydata_operation['CSSTEMAIL'] = $order['email'];
            $paydata_operation['CSSTFIRSTNAME'] = empty($order['shipping_firstname'])?$order['payment_firstname']:$order['shipping_firstname'];
            $paydata_operation['CSSTLASTNAME'] = empty($order['shipping_lastname'])?$order['payment_lastname']:$order['shipping_lastname'];
            $paydata_operation['CSSTPHONENUMBER'] = phone::clean($order['telephone']);
            $paydata_operation['CSSTPOSTALCODE'] = empty($order['shipping_postcode'])?$order['payment_postcode']:$order['shipping_postcode'];
            $paydata_operation['CSSTSTATE'] = $this->getProvinceCode(empty($order['shipping_zone_code'])?$order['payment_zone_code']:$order['shipping_zone_code'], $order_id);
            $paydata_operation['CSSTSTREET1'] = empty($order['shipping_address_1'])?$order['payment_address_1']:$order['shipping_address_1'];
            //$paydata_operation['CSSSTREET2'] = $order['shipping_city']; <-PRISMA dijo
            $paydata_operation['CSMDD12'] = $this->config->get('deadline');
            $paydata_operation['CSMDD13'] = $order['shipping_method'];

            $coupon_id = $this->db->query("SELECT coupon_id FROM  `".DB_PREFIX."coupon_history` WHERE `order_id` = $order_id");
            if(isset($coupon_id->row['coupon_id'])){
                $coupon_id = $coupon_id->row['coupon_id'];
                $coupon_code = $this->db->query("SELECT code FROM `".DB_PREFIX."coupon` WHERE `coupon_id` = $coupon_id");
                $paydata_operation['CSMDD16'] = $coupon_code->row['code'];
            } 

            ////////ACÁ LOS PRODUCTOS
            $products = $this->db->query("SELECT op.product_id, op.total, op.name, op.price, op.quantity, pd.description FROM `".DB_PREFIX."order_product` op INNER JOIN `".DB_PREFIX."product_description` pd ON op.product_id = pd.product_id  WHERE `order_id`=$order_id");   
            $products_array = $products->rows;
            //CSIPRODUCTCODE
            $code_values = array();
            foreach ($products_array as $key => $value) {
                $code_value = $this->model_payment_todopago->getProductCode($value['product_id']);
                $code_values[] = str_replace('#', '', empty($code_value)?"default":$code_value);
            }
            $paydata_operation['CSITPRODUCTCODE'] = join($code_values, "#");
            //CSITPRODUCTDESCRIPTION
            $description_values = array();
            foreach ($products_array as $key => $value){
                $replace = array("\n","\r",'\n','\r','&nbsp;');
                $description = str_replace('#', '', $value['description']);
                $description_values[] = substr(TodoPago\Sdk::sanitizeValue(str_replace($replace, '', strip_tags(htmlspecialchars_decode($description)))),0,50);
            }
            $paydata_operation['CSITPRODUCTDESCRIPTION'] = join($description_values, "#");
            //CSITPRODUCTNAME
            $name_values = array();
            foreach ($products_array as $key => $value) {
                $name_values[] = str_replace('#', '', $value['name']); 
            }
            $paydata_operation['CSITPRODUCTNAME'] = join($name_values, "#");
            //CSITPRODUCTSKU
            $sku_values = array();
            foreach ($products_array as $key => $value){
                $sku_values[] = $value['product_id'];
            }
            $paydata_operation['CSITPRODUCTSKU'] = join($sku_values, "#");
            //CSITTOTALAMOUNT
            $total_values = array();
            foreach ($products_array as $key => $value) {
                $total_values[] = number_format($value['total'], 2,".","");
            }
            $paydata_operation['CSITTOTALAMOUNT']= join($total_values, "#");
            //CSITQUANTITY
            $quantity_values = array();
            foreach ($products_array as $key => $value) {
                $quantity_values[] = $value['quantity'];
            }
            $paydata_operation['CSITQUANTITY']= join($quantity_values, "#");
            //CSITUNITPRICE
            $price_values = array();
            foreach ($products_array as $key => $value) {
                $price_values[] = number_format($value['price'], 2, ".", "");
            }
            $paydata_operation['CSITUNITPRICE']= join($price_values, "#");
        }

        if ($this->config->get('segmentodelcomercio') == 'Ticketing'){
            ////////ACÁ LOS PRODUCTOS
            $products = $this->db->query("SELECT op.product_id, op.total, op.name, op.price, op.quantity, pd.description FROM `".DB_PREFIX."order_product` op INNER JOIN `".DB_PREFIX."product_description` pd ON op.product_id = pd.product_id  WHERE `order_id`=$order_id");
            $products_array = $products->rows;
            
            /*CDMDD33-----------------------------------------+
            |Segun la dooc es general dentro de Ticketing,    |
            |pero la fecha de evento es un atributo propio    |
            |de cada producto... *                            |
            |Averiguar cómo es eso, la consulta para obtener  |
            |la fecha del evento es algo así:                 |
            |                                                 |
            |SELECT pa.`text` as 'fecha_evento'               |
            |FROM `oc_product_attribute` pa                   |
            |INNER JOIN `oc_attribute_description` ad         |
            |ON pa.attribute_id = ad.attribute_id             |
            |INNER JOIN `oc_attribute` a                      |
            |ON ad.attribute_id = a.attribute_id              |
            |INNER JOIN `oc_attribute_group_description` agd  |
            |ON a.attribute_group_id = agd.attribute_group_id |
            |WHERE ad.name = 'fecha evento'                   |
            |AND agd.name = 'Prevencion de Fraude'            |
            |AND pa.product_id = 48                           |
            +------------------------------------------------*/
            
            //CSIPRODUCTCODE
            $code_values = array();
            foreach ($products_array as $key => $value) {
                $code_values[] = $this->model_payment_todopago->getProductCode($value['product_id']);
            }
            $paydata_operation['CSIPRODUCTCODE'] = join($code_values, "#");

            //CSITPRODUCTNAME
            $name_values = array();
            foreach ($products_array as $key => $value) {
                $name_values[] = $value['name']; 
            }
            //CSITPRODUCTDESCRIPTION
            $description_values = array();
            foreach ($products_array as $key => $value){
                $replace = array('\n','\r','&nbsp;');
                $description = $value['description'];
                $description_values[] = substr(str_replace($replace, '', strip_tags($descripcion)),0,50);
            }
            $paydata_operation['CSITPRODUCTDESCRIPTION'] = join($description_values, "#");
            $paydata_operation['CSITPRODUCTNAME'] = join($name_values, "#"); 
            //CSITTOTALAMOUNT
            $total_values = array();
            foreach ($products_array as $key => $value) {
                $total_values[] = number_format($value['total'], 2 , ".", "");
            }
            $paydata_operation['CSITTOTALAMOUNT']= join($total_values, "#");
            //CSITQUANTITY
            $quantity_values = array();
            foreach ($products_array as $key => $value) {
                $quantity_values[] = $value['quantity'];
            }
            $paydata_operation['CSITQUANTITY']= join($quantity_values, "#");
            //CSITPRICE
            $price_values = array();
            foreach ($products_array as $key => $value) {
                $price_values[] = number_format($value['price'], 2, ".", "");
            }
            $paydata_operation['CSITPRICE']= join($price_values, "#");   
        }

        if ($this->config->get('segmentodelcomercio') == 'Services'){
            ////////ACÁ LOS PRODUCTOS
            $products = $this->db->query("SELECT op.product_id, op.total, op.name, op.price, op.quantity, pd.description FROM `".DB_PREFIX."order_product` op INNER JOIN `".DB_PREFIX."product_description` pd ON op.product_id = pd.product_id  WHERE `order_id`=$order_id");  
            $products_array = $products->rows;
            //CSIPRODUCTCODE
            $code_values = array();
            foreach ($products_array as $key => $value) {
                $code_values[] = $this->model_payment_todopago->getProductCode($value['product_id']);
            }
            $paydata_operation['CSIPRODUCTCODE'] = join($code_values, "#");

            //CSITPRODUCTDESCRIPTION
            $description_values = array();
            foreach ($products_array as $key => $value){
                $replace = array('\n','\r','&nbsp;');
                $description = $value['description'];
                $description_values[] = substr(str_replace($replace, '', strip_tags($descripcion)),0,50);
            }
            $paydata_operation['CSITPRODUCTDESCRIPTION'] = join($description_values, "#");
            //CSITPRODUCTNAME
            $name_values = array();
            foreach ($products_array as $key => $value) {
                $name_values[] = $value['name']; 
            }
            $paydata_operation['CSITPRODUCTNAME'] = join($name_values, "#"); 
            //CSITTOTALAMOUNT
            $total_values = array();
            foreach ($products_array as $key => $value) {
                $total_values[] = number_format($value['total'], 2 , ".", "");
            }
            $paydata_operation['CSITTOTALAMOUNT']= join($total_values, "#");
            //CSITQUANTITY
            $quantity_values = array();
            foreach ($products_array as $key => $value) {
                $quantity_values[] = $value['quantity'];
            }
            $paydata_operation['CSITQUANTITY']= join($quantity_values, "#");
            //CSITPRICE
            $price_values = array();
            foreach ($products_array as $key => $value) {
                $price_values[] = number_format($value['price'], 2, ".", "");
            }
            $paydata_operation['CSITPRICE']= join($price_values, "#");
        }

        if ($this->config->get('segmentodelcomercio') == 'Digital Goods'){
            ////////ACÁ LOS PRODUCTOS
            $products = $this->db->query("SELECT product_id, total, name, price, quantity FROM `".DB_PREFIX."order_product` WHERE `order_id`=$order_id");   
            $products_array = $products->rows;
            //CSIPRODUCTCODE
            $code_values = array();
            foreach ($products_array as $key => $value) {
                $code_values[] = $this->model_payment_todopago->getProductCode($value['product_id']);
            }
            $paydata_operation['CSIPRODUCTCODE'] = join($code_values, "#");

            //CSITPRODUCTDESCRIPTION
            $description_values = array();
            foreach ($products_array as $key => $value){
                $replace = array('\n','\r','&nbsp;');
                $description = $value['description'];
                $description_values[] = substr(str_replace($replace, '', strip_tags($descripcion)),0,50);
            }
            $paydata_operation['CSITPRODUCTDESCRIPTION'] = join($description_values, "#");
            //CSITPRODUCTNAME
            $name_values = array();
            foreach ($products_array as $key => $value) {
                $name_values[] = $value['name']; 
            }
            $paydata_operation['CSITPRODUCTNAME'] = join($name_values, "#"); 
            //CSITTOTALAMOUNT
            $total_values = array();
            foreach ($products_array as $key => $value) {
                $total_values[] = number_format($value['total'], 2 , ".", "");
            }
            $paydata_operation['CSITTOTALAMOUNT']= join($total_values, "#");
            //CSITQUANTITY
            $quantity_values = array();
            foreach ($products_array as $key => $value) {
                $quantity_values[] = $value['quantity'];
            }
            $paydata_operation['CSITQUANTITY']= join($quantity_values, "#");
            //CSITPRICE
            $price_values = array();
            foreach ($products_array as $key => $value) {
                $price_values[] = number_format($value['price'], 2, ".", "");
            }
            $paydata_operation['CSITPRICE']= join($price_values, "#");
        }

        return $paydata_operation;
    }

    private function get_authorizationHTTP(){
        return  json_decode(html_entity_decode($this->config->get('authorizationHTTP')),TRUE);
    }

    private function get_mode(){
        return html_entity_decode($this->config->get('modotestproduccion'));
    }

    private function get_id_site(){
        if($this->get_mode()=="Test"){
            return html_entity_decode($this->config->get('idsitetest'));
        }else{
            return html_entity_decode($this->config->get('idsiteproduccion'));
        }
    }

    private function get_security_code(){
        if($this->get_mode()=="Test"){
            return html_entity_decode($this->config->get('securitytest'));
        }else{
            return html_entity_decode($this->config->get('securityproduccion'));
        }
    }
    private function writeLog($order_id, $action, $params = false){
        $logMessage = "todopago - orden ".$order_id.": ".$action;
        $logMessage .= $params? " - parametros: ".json_encode($params):'';
        $this->log->write($logMessage);
    }
    
    private function getProvinceCode($ocCode, $order_id){
//        $csCode = $this->db->query("select z.cs_code from ".DB_PREFIX."zone z inner join ".DB_PREFIX."country c on  z.country_id = c.country_id where c.iso_code_2 = 'AR' and code = '".$ocCode."';");
//        
//        return $csCode->row['cs_code'];
        $this->writeLog("ocState", $ocCode);
        switch ($ocCode){
            case 'AN':
                $csCode = 'V';
                break;
            case 'TF':
                $csCode = 'V';
                break;
            case 'BA':
                $csCode = 'B';
                break;
            case 'CA':
                $csCode = 'K';
                break;
            case 'CH':
                $csCode = 'H';
                break;
            case 'CU':
                $csCode = 'U';
                break;
            case 'CO':
                $csCode = 'X';
                break;
            case 'CR':
                $csCode = 'W';
                break;
            case "DF":
                $csCode = 'C';
                break;
            case 'ER':
                $csCode = 'R';
                break;
            case 'FO':
                $csCode = 'P';
                break;
            case 'JU':
                $csCode = 'Y';
                break;
            case 'LP':
                $csCode = 'L';
                break;
            case 'LR':
                $csCode = 'F';
                break;
            case 'ME':
                $csCode = 'M';
                break;
            case 'MI':
                $csCode = 'N';
                break;
            case 'NE':
                $csCode = 'Q';
                break;
            case 'RN':
                $csCode = 'R';
                break;
            case 'SA':
                $csCode = 'A';
                break;
            case 'SJ':
                $csCode = 'J';
                break;
            case 'SL':
                $csCode = 'D';
                break;
            case 'SC':
                $csCode = 'Z';
                break;
            case 'SF':
                $csCode = 'S';
                break;
            case 'SD':
                $csCode = 'G';
                break;
            case 'TU':
                $csCode = 'T';
                break;
        }
            $this->writeLog("csState", $csCode);
            return $csCode;
    }
    
    private function getDaysQty($date){
        $date = new DateTime($date);
        $now = new DateTime();
        
        $diff = $date->diff($now);
        return $diff->days;
    }
    
    private function getQtyOrders($customerId){
        $qty = $this->db->query("SELECT COUNT(*) AS qty FROM ".DB_PREFIX."order WHERE customer_id = $customerId;");
        return $qty->row['qty'];
    }
}
