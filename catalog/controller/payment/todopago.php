<?php
require_once dirname(__FILE__).'/../todopago/TodoPago/lib/Sdk.php';
require_once dirname(__FILE__).'/../todopago/ControlFraude/includes.php';

class ControllerPaymentTodopago extends Controller {
    
    private $order_id;
    
    protected function index() {
        $this->language->load('payment/todopago');
        $this->load->model('todopago/transaccion');

        $this->load->model('checkout/order');
        $this->writeLog("session_data", $this->session->data);
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        //$this->writeLog("order_info", $order_info);
        

        if ($order_info) {
            $this->data['order_id'] = $order_info['order_id'];

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/todopago.tpl'))             {
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
        $this->order_id = $_POST['order_id'];
        $this->writeLog("order_id, entrda fstST", $this->order_id);
        
        $this->prepareOrder();
        
        if ($this->model_todopago_transaccion->getStep($this->order_id) == $this->model_todopago_transaccion->getFirstStep()){
            $this->writeLog("first step");

            $paramsSAR = $this->getPaydata();
            //$payData['canaldeingresodelpedido'] = $this->config->get('canaldeingresodelpedido');
            $authorizationHTTP = $this->get_authorizationHTTP();
            $mode = ($this->get_mode()=="Test")?"test":"prod";

            try{
                $this->callSAR($authorizationHTTP, $mode, $paramsSAR);
            }catch (Exception $e){
                $this->writeLog("error", json_encode($e));
                $this->model_checkout_order->update($this->order_id, $this->config->get('todopago_order_status_id_rech'), "TODO PAGO (Exception): ".$e);
                $this->redirect($this->config->get('config_url')."index.php?route=payment/todopago/_urlerror&Order=".$this->order_id);

            }
        }
        else{
            $this->writeLog("Fallo al iniciar el first step, Ya se encuentra registrado un first step exitoso en la tabla todopago_transaccion");
            $this->redirect($this->url->link('common/home'));
        }
    }
    
    private function prepareOrder(){
        $this->load->model('todopago/transaccion');
        
        $this->model_todopago_transaccion->createRegister($this->order_id);
        
        $this->load->model('checkout/order');
        //confirma y pasa a pendiente la orden <-- este tienen que se configurable
        $this->model_checkout_order->confirm($this->order_id, 1);
        
        $this->order = $this->model_checkout_order->getOrder($this->order_id);
    }
    
    private function getPaydata(){
            $this->load->model('account/customer');
            $customer = $this->model_account_customer->getCustomer($this->order['customer_id']);
            
            $this->load->model('payment/todopago');
            
            $controlFraude = ControlFraudeFactory::getControlfraudeExtractor($this->config->get('segmentodelcomercio'), $this->order, $customer, $this->model_payment_todopago);
            $controlFraude_data = $controlFraude->getDataCF();

            $paydata_comercial = $this->getOptionsSARComercio(); 
            $paydata_operation = $this->getOptionsSAROperacion($controlFraude_data);
            
            return array('comercio' => $paydata_comercial, 'operacion' => $paydata_operation);
    }
    
    private function callSAR($authorizationHTTP, $mode, $paramsSAR){
                $connector = new TodoPago\Sdk($authorizationHTTP, $mode);
                $paydata_comercial = $paramsSAR['comercio'];
                $paydata_operation = $paramsSAR['operacion'];
                $this->writeLog("params SAR", json_encode($paramsSAR));
                $rta_first_step = $connector->sendAuthorizeRequest($paydata_comercial, $paydata_operation);
                
                if($rta_first_step["StatusCode"] == 702){
                    $rta_first_step = $connector->sendAuthorizeRequest($paydata_comercial, $paydata_operation);
                }
                $this->writeLog("response SAR", json_encode($rta_first_step));

                if($rta_first_step["StatusCode"] == -1){
                    $this->db->query("UPDATE ".DB_PREFIX."order SET todopagoclave='".$rta_first_step['RequestKey']."' WHERE  order_id=$this->order_id;");
                    $query = $this->model_todopago_transaccion->recordFirstStep($this->order_id, $paramsSAR, $rta_first_step, $rta_first_step['RequestKey'], $rta_first_step['PublicRequestKey']);
                    $this->writeLog('query recordFiersStep()', $query);
                    $this->model_checkout_order->update($this->order_id, $this->config->get('todopago_order_status_id_pro'), "TODO PAGO: ".$rta_first_step['StatusMessage']);
                    header('Location: '.$rta_first_step['URL_Request']);
                    //$this->redirect($rta_first_step['URL_Request']);
                }
                else{
                    $query = $this->model_todopago_transaccion->recordFirstStep($this->order_id, $paramsSAR, $rta_first_step);
                    $this->writeLog('query recordFirstStep()', $query);
                    $this->model_checkout_order->update($this->order_id, $this->config->get('todopago_order_status_id_rech'), "TODO PAGO: ".$rta_first_step['StatusMessage']);
                    $this->redirect($this->config->get('config_url')."index.php?route=payment/todopago/_urlerror&Order=".$this->order_id);
                }
    }

    public function second_step_todopago(){
        //data Recovery and definition
        $answer = $_GET['Answer'];
        $this->order_id = $_GET['Order'];
        $this->load->model('todopago/transaccion');
        
        if($this->model_todopago_transaccion->getStep($this->order_id) == $this->model_todopago_transaccion->getSecondStep()){
           
            //Starting second Step
            $this->writeLog("second step");

            $authorizationHTTP = $this->get_authorizationHTTP();

            $mode = ($this->get_mode()=="Test")?"test":"prod";
            $this->load->model('checkout/order');
            $requestKey = $this->db->query("SELECT todopagoclave FROM ".DB_PREFIX."order WHERE order_id=$this->order_id")->row['todopagoclave'];
            $optionsAnswer = array(
                'Security' => $this->get_security_code(),
                'Merchant' => $this->get_id_site(),
                'RequestKey' => $requestKey,
                'AnswerKey' => $answer
            );
            $this->writeLog("params GAA", json_encode($optionsAnswer));
            try{
                $this->callGAA($authorizationHTTP, $mode, $optionsAnswer);
            }
            catch(Exception $e){
                $this->model_checkout_order->update($this->order_id, $this->config->get('todopago_order_status_id_rech'), "TODO PAGO (Exception): ".$e);
                $this->redirect($this->config->get('config_url')."index.php?route=payment/todopago/_urlerror&Order=".$this->order_id);

            }
        }
        else{
            $this->writeLog("Fallo al iniciar el second step, Ya se encuentra registrado un second step exitoso en la tabla todopago_transaccion");
            $this->redirect($this->url->link('common/home'));
        }
    }
    
    private function callGAA($authorizationHTTP, $mode, $optionsAnswer){
            $connector = new TodoPago\Sdk($authorizationHTTP, $mode);
            $rta_second_step = $connector->getAuthorizeAnswer($optionsAnswer);
            $this->writeLog("response GAA", json_encode($rta_second_step));
            $query = $this->model_todopago_transaccion->recordSecondStep($this->order_id, $optionsAnswer, $rta_second_step);
            $this->writeLog("query recordSecondStep()", $query);
            
            if(strlen($rta_second_step['Payload']['Answer']["BARCODE"]) > 0){
                $this->showCoupon($rta_second_step);
            }

            if($rta_second_step['StatusCode']==-1){
            $this->writeLog('status code', $rta_second_step['StatusCode']);

                $this->model_checkout_order->update($this->order_id, $this->config->get('todopago_order_status_id_aprov'), "TODO PAGO: ".$rta_second_step['StatusMessage']);

                $this->redirect($this->url->link('checkout/success'));  
            }
            else{
                $this->writeLog('fail', $rta_second_step['StatusCode']);

                $this->model_checkout_order->update($this->order_id, $this->config->get('todopago_order_status_id_rech'), "TODO PAGO: ".$rta_second_step['StatusMessage']);
                $this->redirect($this->config->get('config_url')."index.php?route=payment/todopago/_urlerror&Order=".$this->order_id);
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



    private function getOptionsSARComercio(){
        $paydata_comercial ['URL_OK'] =  $this->config->get('config_url')."index.php?route=payment/todopago/second_step_todopago&Order=".$this->order_id;
        $paydata_comercial ['URL_ERROR'] = $this->config->get('config_url').'index.php?route=payment/todopago/second_step_todopago&Order='.$this->order_id;
        $paydata_comercial['Merchant'] = $this->get_id_site();
        $paydata_comercial['Security'] = $this->get_security_code();
        $paydata_comercial['EncodingMethod'] = 'XML';

        return $paydata_comercial;    
    }

    private function getOptionsSAROperacion($controlFraude){

        $this->load->model('payment/todopago');
        $this->load->model('checkout/order');
        
        $this->order = $this->model_checkout_order->getOrder($this->order_id);

        $paydata_operation['MERCHANT'] = $this->get_id_site();
        $paydata_operation['OPERATIONID'] = $this->order_id;
        $paydata_operation['AMOUNT'] = number_format($this->order['total'], 2, ".", "");
        $paydata_operation['CURRENCYCODE'] = "032";
        $paydata_operation['EMAILCLIENTE'] = $this->order['email'];
        
        $paydata_operation = array_merge($paydata_operation, $controlFraude);
        
           $this->writeLog("Paydat operación", $paydata_operation); 
            

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
    private function writeLog($action, $params = false){
        $logMessage = "todopago - orden ".$this->order_id.": ".$action;
        $logMessage .= $params? " - parametros: ".json_encode($params):'';
        $this->log->write($logMessage);
    }
}
