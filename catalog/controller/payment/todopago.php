<?php
require_once DIR_APPLICATION.'controller/todopago/vendor/autoload.php';
require_once DIR_APPLICATION.'../admin/resources/todopago/todopago_ctes.php';
require_once DIR_APPLICATION.'controller/todopago/ControlFraude/includes.php';
require_once DIR_APPLICATION.'../admin/resources/todopago/Logger/loggerFactory.php';

class ControllerPaymentTodopago extends Controller {
    
    private $order_id;
    
    public function __construct($registry){
        parent::__construct($registry);
        $this->logger = loggerFactory::createLogger();
    }

    protected function index() {

        $this->language->load('payment/todopago');
        $this->load->model('todopago/transaccion');

        $this->load->model('checkout/order');
        $this->logger->debug("session_data: ".json_encode($this->session->data));
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $this->logger->debug("order_info: ".json_encode($order_info));
        

        if ($order_info) {
            $this->data['order_id'] = $order_info['order_id'];

            if($this->config->get('todopago_form')=="hibrid"){
                if (file_exists(DIR_TEMPLATE.$this->config->get('config_template').'/template/payment/todopago.tpl')){
                    $this->template = $this->config->get('config_template') . '/template/payment/todopago.tpl';
                } else {
                    $this->template = 'default/template/payment/todopago.tpl';
                }
            }else{
                if (file_exists(DIR_TEMPLATE.$this->config->get('config_template').'/template/payment/todopago_redirect.tpl')){
                    $this->template = $this->config->get('config_template') . '/template/payment/todopago_redirect.tpl';
                } else {
                    $this->template = 'default/template/payment/todopago_redirect.tpl';
                }
            }
            $this->data['action'] = $this->config->get('config_url')."index.php?route=payment/todopago/first_step_todopago";
            $this->render();
        }
    }

    public function first_step_todopago()
    {
        $this->order_id = $_REQUEST['order_id'];
        $this->logger->debug("order_id, entrda fstST: ".$this->order_id);
        
        $this->prepareOrder();
        
        if ($this->model_todopago_transaccion->getStep($this->order_id) == $this->model_todopago_transaccion->getFirstStep()){
            $this->logger->info("first step");

            $paramsSAR = $this->getPaydata();
            //$payData['canaldeingresodelpedido'] = $this->config->get('canaldeingresodelpedido');
            $authorizationHTTP = $this->get_authorizationHTTP();
            $mode = ($this->get_mode()==MODO_TEST)?"test":"prod";
            $this->logger->debug("first step Authorization: ".json_encode($authorizationHTTP));
            $this->logger->debug('Mode: '.$mode);
            try{
                $this->callSAR($authorizationHTTP, $mode, $paramsSAR);
            }catch (Exception $e){
                $this->logger->error("Ha surgido un error en el fist step", $e);
                $this->model_checkout_order->update($this->order_id, $this->config->get('todopago_order_status_id_rech'), "TODO PAGO (Exception): ".$e);
                $this->redirect($this->config->get('config_url')."index.php?route=payment/todopago/url_error&Order=".$this->order_id);

            }
        }
        else{
            $this->logger->warn("Fallo al iniciar el first step, Ya se encuentra registrado un first step exitoso en la tabla todopago_transaccion");
            $this->redirect($this->url->link('common/home'));
        }
    }
    
    private function prepareOrder(){
        $this->setLoggerForPayment($this->order_id);

        $this->load->model('todopago/transaccion');
        
        $this->model_todopago_transaccion->createRegister($this->order_id);
        
        $this->load->model('checkout/order');
        //confirma y pasa a pendiente la orden <-- este tienen que ser configurable
        $this->model_checkout_order->confirm($this->order_id, 1);
    }
    
    private function getPaydata(){
            $this->load->model('account/customer');
            $customer = $this->model_account_customer->getCustomer($this->order['customer_id']);
            
            $this->load->model('payment/todopago');
            $this->model_payment_todopago->setLogger($this->logger);
            
            $controlFraude = ControlFraudeFactory::getControlfraudeExtractor($this->config->get('todopago_segmentodelcomercio'), $this->order, $customer, $this->model_payment_todopago, $this->logger);
            $controlFraude_data = $controlFraude->getDataCF();

            $paydata_comercial = $this->getOptionsSARComercio(); 
            $paydata_operation = $this->getOptionsSAROperacion($controlFraude_data);
            
            return array('comercio' => $paydata_comercial, 'operacion' => $paydata_operation);
    }
    
    private function callSAR($authorizationHTTP, $mode, $paramsSAR){
                $connector = new TodoPago\Sdk($authorizationHTTP, $mode);
                $paydata_comercial = $paramsSAR['comercio'];
                $paydata_operation = $paramsSAR['operacion'];
                $this->logger->info("params SAR: ".json_encode($paramsSAR));
                $rta_first_step = $connector->sendAuthorizeRequest($paydata_comercial, $paydata_operation);
                
                if($rta_first_step["StatusCode"] == 702){
                    $this->logger->debug("Reintento");
                    $rta_first_step = $connector->sendAuthorizeRequest($paydata_comercial, $paydata_operation);
                }
                $this->logger->info("response SAR: ".json_encode($rta_first_step));

                if($rta_first_step["StatusCode"] == -1){
                    $query = $this->model_todopago_transaccion->recordFirstStep($this->order_id, $paramsSAR, $rta_first_step, $rta_first_step['RequestKey'], $rta_first_step['PublicRequestKey']);
                    $this->logger->debug('query recordFiersStep(): '.$query);
                    $this->model_checkout_order->update($this->order_id, $this->config->get('todopago_order_status_id_pro'), "TODO PAGO: ".$rta_first_step['StatusMessage']);
                    if ($this->config->get('todopago_form')=="hibrid"){
                    echo json_encode($rta_first_step);} else {header('Location: '.$rta_first_step['URL_Request']);}
                }
                else{
                    $query = $this->model_todopago_transaccion->recordFirstStep($this->order_id, $paramsSAR, $rta_first_step);
                    $this->logger->debug('query recordFirstStep(): '.$query);
                    $this->model_checkout_order->update($this->order_id, $this->config->get('todopago_order_status_id_rech'), "TODO PAGO: ".$rta_first_step['StatusMessage']);
                    $this->redirect($this->config->get('config_url')."index.php?route=payment/todopago/url_error&Order=".$this->order_id);
                }
    }

    public function second_step_todopago(){
        $this->order_id = $_GET['Order'];
        $answer = $_GET['Answer'];
        $this->load->model('todopago/transaccion');
        $this->setLoggerForPayment();
        
        if($this->model_todopago_transaccion->getStep($this->order_id) == $this->model_todopago_transaccion->getSecondStep()){
           
            //Starting second Step
            $this->logger->info("second step");

            $authorizationHTTP = $this->get_authorizationHTTP();
            $this->logger->debug ( "second_step_todopago():authorizationHTTP: ".json_encode($authorizationHTTP));

            $mode = ($this->get_mode()==MODO_TEST)?"test":"prod";
            $this->load->model('checkout/order');
            $requestKey = $this->model_todopago_transaccion->getRequestKey($this->order_id);
            $optionsAnswer = array(
                'Security' => $this->get_security_code(),
                'Merchant' => $this->get_id_site(),
                'RequestKey' => $requestKey,
                'AnswerKey' => $answer
            );
            $this->logger->info("params GAA: ".json_encode($optionsAnswer));
            try{
                $this->callGAA($authorizationHTTP, $mode, $optionsAnswer);
            }
            catch(Exception $e){
                $this->model_checkout_order->update($this->order_id, $this->config->get('todopago_order_status_id_rech'), "TODO PAGO (Exception): ".$e);
                $this->logger->error("Error en el Second Step", $e);
                $this->redirect($this->config->get('config_url')."index.php?route=payment/todopago/url_error&Order=".$this->order_id);

            }
        }
        else{
            $this->logger->warn("Fallo al iniciar el second step, Ya se encuentra registrado un second step exitoso en la tabla todopago_transaccion");
            $this->redirect($this->url->link('common/home'));
        }
    }
    
    private function callGAA($authorizationHTTP, $mode, $optionsAnswer){
            $connector = new TodoPago\Sdk($authorizationHTTP, $mode);
            $rta_second_step = $connector->getAuthorizeAnswer($optionsAnswer);
            $this->logger->info("response GAA: ".json_encode($rta_second_step));
            $query = $this->model_todopago_transaccion->recordSecondStep($this->order_id, $optionsAnswer, $rta_second_step);
            $this->logger->debug("query recordSecondStep(): ".$query);
            
            if(strlen($rta_second_step['Payload']['Answer']["BARCODE"]) > 0){
                $this->showCoupon($rta_second_step);
            }

            if($rta_second_step['StatusCode']==-1){
            $this->logger->debug('status code: '.$rta_second_step['StatusCode']);

                $this->model_checkout_order->update($this->order_id, $this->config->get('todopago_order_status_id_aprov'), "TODO PAGO: ".$rta_second_step['StatusMessage']);

                $this->redirect($this->url->link('checkout/success'));  
            }
            else{
                $this->logger->warn('fail: '.$rta_second_step['StatusCode']);
                $this->model_checkout_order->update($this->order_id, $this->config->get('todopago_order_status_id_rech'), "TODO PAGO: ".$rta_second_step['StatusMessage']);
                $this->redirect($this->config->get('config_url')."index.php?route=payment/todopago/url_error&Order=".$this->order_id);
            }
    }
    
    private function showCoupon($rta_second_step){
            $nroop =  $this->order_id;
            $venc = $rta_second_step['Payload']['Answer']["COUPONEXPDATE"];
            $total = $rta_second_step['Payload']['Request']['AMOUNT'];
            $code = $rta_second_step['Payload']['Answer']["BARCODE"];
            $tipocode = $rta_second_step['Payload']['Answer']["BARCODETYPE"];
            $empresa = $rta_second_step['Payload']['Answer']["PAYMENTMETHODNAME"];

            $this->model_checkout_order->update($this->order_id, $this->config->get('todopago_order_status_id_off'), "TODO PAGO: ".$rta_second_step['StatusMessage']);    
            $this->redirect($this->url->link("todopago/todopago/cupon&nroop=$nroop&venc=$venc&total=$total&code=$code&tipocode=$tipocode&empresa=$empresa"));  
    }

    public function url_error(){
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



    private function getOptionsSARComercio(){
        $paydata_comercial ['URL_OK'] =  $this->config->get('config_url')."index.php?route=payment/todopago/second_step_todopago&Order=".$this->order_id;
        $paydata_comercial ['URL_ERROR'] = $this->config->get('config_url').'index.php?route=payment/todopago/second_step_todopago&Order='.$this->order_id;
        $paydata_comercial['Merchant'] = $this->get_id_site();
        $paydata_comercial['Security'] = $this->get_security_code();
        $paydata_comercial['EncodingMethod'] = 'XML';

        
        return $paydata_comercial;    
    }

    private function getOptionsSAROperacion($controlFraude){

        $this->load->model('checkout/order');
        
        $this->order = $this->model_checkout_order->getOrder($this->order_id);

        $paydata_operation['MERCHANT'] = $this->get_id_site();
        $paydata_operation['OPERATIONID'] = $this->order_id;
        $paydata_operation['AMOUNT'] = number_format($this->order['total'], 2, ".", "");
        $paydata_operation['CURRENCYCODE'] = "032";
        $paydata_operation['EMAILCLIENTE'] = $this->order['email'];
        $var = $this->config->get('todopago_maxinstallments');
         if($var != null){
          $paydata_operation['MAXINSTALLMENTS'] = $this->config->get('todopago_maxinstallments');
         }
        $paydata_operation = array_merge($paydata_operation, $controlFraude);
        
           $this->logger->debug("Paydata operación: ".json_encode($paydata_operation));
            
        return $paydata_operation;
    }

    private function get_authorizationHTTP(){
    	$data;
    	if ($this->get_mode () == MODO_TEST) {
    		$data =  $this->config->get ( 'todopago_authorizationHTTPtest' );
    	}else {
    		$data =  $this->config->get ( 'todopago_authorizationHTTPproduccion' );
    	}
    	
    	$data = html_entity_decode ($data);
    	
    	$decoded_json = json_decode ($data, TRUE);
    	if (json_last_error() === JSON_ERROR_NONE) {
    		// JSON is valid
    		return $decoded_json;
    	}else{
    	
    		$decoded_array['Authorization'] = $data;
    		return $decoded_array;
    	}
    	/*  Old source code
    	 if ($this->get_mode () == "test") {
    	return json_decode ( html_entity_decode ( $this->config->get ( 'todopago_authorizationHTTPtest' ) ), TRUE );
    	} else {
    	return json_decode ( html_entity_decode ( $this->config->get ( 'todopago_authorizationHTTPproduccion' ) ), TRUE );
    	}*/
    }

    private function get_mode(){
    	//$this->logger->debug("get_mode(): " .mb_strtolower(html_entity_decode($this->config->get('todopago_modotestproduccion'))));
        return mb_strtolower( html_entity_decode($this->config->get('todopago_modotestproduccion')));
    }

    private function get_id_site(){
        if($this->get_mode()==MODO_TEST){
            return html_entity_decode($this->config->get('todopago_idsitetest'));
        }else{
            return html_entity_decode($this->config->get('todopago_idsiteproduccion'));
        }
    }

    private function get_security_code(){
        if($this->get_mode()==MODO_TEST){
            return html_entity_decode($this->config->get('todopago_securitytest'));
        }else{
            return html_entity_decode($this->config->get('todopago_securityproduccion'));
        }
    }

    private function setLoggerForPayment(){
        $this->load->model('checkout/order');
        $this->order= $this->model_checkout_order->getOrder($this->order_id);
        $this->logger->debug("order_info: ".json_encode($this->order));
        $mode = ($this->get_mode()==MODO_TEST)?"test":"prod";
        $this->logger = loggerFactory::createLogger(true, $mode, $this->order['customer_id'], $this->order['order_id']);
    }
}