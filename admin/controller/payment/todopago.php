<?php
require_once dirname(__FILE__).'/../../../catalog/controller/todopago/TodoPago/lib/Sdk.php';
require_once DIR_APPLICATION.'resources/todopago/todopago_ctes.php';
require_once DIR_APPLICATION.'resources/todopago/Logger/loggerFactory.php';

class ControllerPaymentTodopago extends Controller{
    const VERSION = TP_VERSION;

	private $error = array();

    public function __construct($registry){
        parent::__construct($registry);
        $this->logger = loggerFactory::createLogger();
    }
	
	public function install(){

        $this->load->model('payment/todopago');
        
        $actualVersion = $this->model_payment_todopago->getVersion();
        
        if (self::VERSION > $actualVersion){
            
            $this->model_payment_todopago->upgrade();
        }
        else{
            $this->logger->info("todopago - Ya existía una versión posterior");
        }
            
	}

	public function index() {

        $this->language->load('payment/todopago');
		$this->document->setTitle('TodoPago Configuration');
		$this->document->addScript('view/javascript/todopago/jquery.dataTables.min.js');
        $this->document->addStyle('view/stylesheet/todopago/jquery.dataTables.css');
        $this->document->addStyle('view/stylesheet/todopago.css');
		$this->load->model('setting/setting');
        $this->load->model('payment/todopago');
        
		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$this->model_setting_setting->editSetting('todopago', $this->request->post);
            if ($this->request->post['upgrade'] == '1'){
                $this->model_payment_todopago->upgrade();
                $this->session->data['success'] = 'Upgraded.';
            }
            else{
			$this->session->data['success'] = 'Saved.';
                
            }
			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = "Todo Pago";
        
        //Upgrade verification
        $installedVersion= $this->model_payment_todopago->getVersion();
        $this->data['installed_version'] = $installedVersion;
        $this->data['need_upgrade'] = (self::VERSION > $installedVersion)? true : false;
        $this->data['version'] = self::VERSION;

		$this->data['entry_text_config_two'] = $this->language->get('text_config_two');
		$this->data['button_save'] = $this->data['need_upgrade']? "Upgrade" : $this->language->get('text_button_save');
		$this->data['button_cancel'] = $this->language->get('text_button_cancel');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['entry_status'] = $this->language->get('entry_status');

		$this->data['action'] = $this->url->link('payment/todopago', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		//datos para el tag general
		if (isset($this->request->post['todopago_status'])) {
			$this->data['todopago_status'] = $this->request->post['todopago_status'];
		} else {
			$this->data['todopago_status'] = $this->config->get('todopago_status');
		}

		if (isset($this->request->post['authorizationHTTP'])) {
			$this->data['authorizationHTTP'] = $this->request->post['authorizationHTTP'];
		} else {
			$this->data['authorizationHTTP'] = $this->config->get('authorizationHTTP');
		}

		if (isset($this->request->post['segmentodelcomercio'])) {
			$this->data['segmentodelcomercio'] = $this->request->post['segmentodelcomercio'];
		} else {
			$this->data['segmentodelcomercio'] = $this->config->get('segmentodelcomercio');
            
		}

		//PRISMA pidió que saquemos el canal de ingreso
        /*if (isset($this->request->post['canaldeingresodelpedido'])){
			$this->data['canaldeingresodelpedido'] = $this->request->post['canaldeingresodelpedido'];
		} else {
			$this->data['canaldeingresodelpedido'] = $this->config->get('canaldeingresodelpedido');
		}*/

		if (isset($this->request->post['deadline'])){
			$this->data['deadline'] = $this->request->post['deadline'];
		} else {
			$this->data['deadline'] = $this->config->get('deadline');
		}

		if (isset($this->request->post['modotestproduccion'])){
			$this->data['modotestproduccion'] = $this->request->post['modotestproduccion'];
		} else {
			$this->data['modotestproduccion'] = $this->config->get('modotestproduccion');
		}

		//datos para tags ambiente test
		if (isset($this->request->post['idsitetest'])){
			$this->data['idsitetest'] = $this->request->post['idsitetest'];
		} else {
			$this->data['idsitetest'] = $this->config->get('idsitetest');
		}

		if (isset($this->request->post['securitytest'])){
			$this->data['securitytest'] = $this->request->post['securitytest'];
		} else {
			$this->data['securitytest'] = $this->config->get('securitytest');
		}

		//datos para tags ambiente produccion
		if (isset($this->request->post['idsiteproduccion'])){
			$this->data['idsiteproduccion'] = $this->request->post['idsiteproduccion'];
		} else {
			$this->data['idsiteproduccion'] = $this->config->get('idsiteproduccion');
		}

		if (isset($this->request->post['securityproduccion'])){
			$this->data['securityproduccion'] = $this->request->post['securityproduccion'];
		} else {
			$this->data['securityproduccion'] = $this->config->get('securityproduccion');
		}

		//datos para estado del pedido	
		if (isset($this->request->post['todopago_order_status_id_aprov'])) {
			$this->data['todopago_order_status_id_aprov'] = $this->request->post['todopago_order_status_id_aprov'];
		} else {
			$this->data['todopago_order_status_id_aprov'] = $this->config->get('todopago_order_status_id_aprov');
		}

		if (isset($this->request->post['todopago_order_status_id_rech'])) {
			$this->data['todopago_order_status_id_rech'] = $this->request->post['todopago_order_status_id_rech'];
		} else {
			$this->data['todopago_order_status_id_rech'] = $this->config->get('todopago_order_status_id_rech');
		}

		if (isset($this->request->post['todopago_order_status_id_off'])) {
			$this->data['todopago_order_status_id_off'] = $this->request->post['todopago_order_status_id_off'];
		} else {
			$this->data['todopago_order_status_id_off'] = $this->config->get('todopago_order_status_id_off');
		}

		if (isset($this->request->post['todopago_order_status_id_pro'])) {
			$this->data['todopago_order_status_id_pro'] = $this->request->post['todopago_order_status_id_pro'];
		} else {
			$this->data['todopago_order_status_id_pro'] = $this->config->get('todopago_order_status_id_pro');
		}

		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$this->template = 'payment/todopago.tpl';

		$this->children = array(
			'common/header',
			'common/footer'
			);
		$this->response->setOutput($this->render());
	}

	public function get_status()
	{
		$order_id = $_GET['order_id'];
        $this->load->model('todopago/transaccion');
        $transaction = $this->model_todopago_transaccion;
            $this->logger->info('todopago -  step: '.$transaction->getStep($order_id));
            $this->logger->info('todopago - RTRANSACTION_FINISHED: '.$transaction->getTransactionFinished());
        if($transaction->getStep($order_id) == $transaction->getTransactionFinished()){
            $authorizationHTTP = $this->get_authorizationHTTP();
            $mode = $this->get_mode();
            try{
                $connector = new TodoPago\Sdk($authorizationHTTP, $mode);
                $optionsGS = array('MERCHANT'=>$this->get_id_site(), 'OPERATIONID'=>$order_id);
                $status = $connector->getStatus($optionsGS);
                $status_json = json_encode($status);
                $rta = '';
                foreach ($status['Operations'] as $key => $value) {
                    $rta .= "$key: $value \n";
                }
            }
            catch(Exception $e){
                $this->logger->fatal("Ha surgido un error al consultar el estado de la orden: ", $e);
                $rta = 'ERROR AL CONSULTAR LA ORDEN';
            }
        }
        else{
            $rta = "NO HAY INFORMACIÓN DE PAGO";
        }
		echo($rta);
        
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
}
