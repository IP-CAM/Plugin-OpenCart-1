<?php
require_once DIR_APPLICATION.'../catalog/controller/todopago/vendor/autoload.php';
require_once DIR_APPLICATION.'resources/todopago/todopago_ctes.php';
require_once DIR_APPLICATION.'resources/todopago/Logger/loggerFactory.php';

class ControllerPaymentTodopago extends Controller{

	const INSTALL = 'install';
    const UPGRADE = 'upgrade';

    private $error = array();

    public function __construct($registry){
        parent::__construct($registry);
        $this->logger = loggerFactory::createLogger();
    }

    public function install(){
        $this->redirect($this->url->link('payment/todopago/confirm_installation', 'token=' . $this->session->data['token'], 'SSL')); //Redirecciono para poder salir del ciclo de instalación y poder mostrar mi pantalla.
    }

    public function confirm_installation(){
        //Preparo tpl
        $this->data['todopago_version'] = TP_VERSION;
        $this->data['button_install_text'] = 'Instalar';
        $this->data['button_cancel_text'] = 'Cancelar';
        $this->data['button_install_action'] = $this->url->link('payment/todopago/_install', 'action='.self::INSTALL.'&token=' . $this->session->data['token'], 'SSL');
        $this->data['button_cancel_action'] = $this->url->link('payment/todopago/_revert_installation', 'token=' . $this->session->data['token'], 'SSL'); //Al llegar la pantalla ell plugin ya se instaló en el commerce por lo qe hace falta dsinstalarlo
        $this->data['back_button_action'] = html_entity_decode($this->data['button_cancel_action']); //Para javascript lo necesito decodificado
        $this->data['back_button_message'] = "Esto interrumpirá la instalación";

        $this->template = 'todopago/install.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
            );

        $this->response->setOutput($this->render());
    }

    public function _install(){
        //Este es el método que se ocupa en realidad de la instalación así como del upgrade
        if(isset($this->request->get['action'])){

            $action = $this->request->get['action']; //Acción a realizar (puede ser self::INSTALL o self::UPGRADE)

            //Modelos necesarios
            $this->load->model('payment/todopago');
            $this->load->model('todopago/transaccion_admin');

            $this->logger->debug("Verifying required upgrades");
            /*******************************************************************
            *Al no tener breaks entrará en todos los case posteriores.         *
            *TODAS LAS VERSIONES DEBEN APARECER,                               *
            *de lo contrario LA VERSION QUE NO APAREZCA NO PODRÁ UPGRADEARSE   *
            *******************************************************************/
            $actualVersion = $this->model_payment_todopago->getVersion();
            $this->logger->debug("version actual: ".$actualVersion);
            $errorMessage = null;
            switch ($actualVersion){
                case "0.0.0":
                $this->logger->info('Begining install');
                case "0.9.0":
                $this->logger->debug("Upgrade to v0.9.9");
                try{
                        $this->model_todopago_transaccion_admin->createTable(); //Crea la tabla todopago_transaccion
                    }
                    catch (Exception $e){
                        $errorMessage = 'Fallo al crear la tabla todopago_transaccion';
                        $this->logger->fatal($errorMessage, $e);
                        break;
                    }
                    case "0.9.9":
                    $this->logger->debug("upgrade to v1.0.0");
                    try{
                        $this->model_payment_todopago->setProvincesCode(); //Guarda los códigos de prevencion de fraude para las provincias
                    }catch (Exception $e) {
                        $errorMessage = 'Fallo al grabar los codigos de provincias para preveción de Fraude';
                        $this->logger->fatal($errorMessage, $e);
                        break;
                    }
                    case "1.0.0":
                    $this->logger->debug("upgrade to v1.1.0");
                    case "1.1.0":
                    $this->logger->debug("upgrade to v1.1.1");
                    try{
                        $this->model_payment_todopago->setPostCodeRequired(); //Setea el código postal obligatorio para argentina
                    }
                    catch (Exception $e){
                        $errorMessage = 'Fallo al setear el código postal obligatorio para Argentina';
                        $this->logger->fatal($errorMessage, $e);
                        break;
                    }
                    case "1.1.1":
                    $this->logger->debug("upgrade to v1.2.0");
                    case "1.2.0":
                    $this->logger->debug("upgrade to v1.3.0");
                    case "1.3.0":
                    $this->logger->debug("upgrade to v1.4.0");
                    case "1.4.0":
                    $this->logger->debug("upgrade to v1.4.1");
                    case "1.4.1":
                    $this->logger->debug("upgrade to v1.5.0");
                    case "1.5.0":
                    $this->logger->debug("upgrade to v1.5.1");
                    case "1.5.1":
                    $this->logger->info("Plugin instalado/upgradeado");
                    try{
                        $this->model_payment_todopago->updateVersion($actualVersion); //Registra en la tabla el nro de Versión a la que se ha actualizado
                    }
                    catch (Exception $e){
                        $errorMessage = 'Fallo deconocido, se pedirá reintentar';
                        $this->logger->fatal($errorMessage, $e);
                        break;
                    }
                }
                if ($errorMessage == null){
                    if ($action == self::UPGRADE){
                        $this->session->data['success'] = 'Upgrade finalizado.';
                    } else {
                        $this->session->data['success'] = 'Instalación finalizada.';
                    }
                }
                else{
                    $this->session->data['success'] = 'Upgraded.';
                }
                $this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
            }
            else{
            $this->redirect($this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL')); //Nunca deberíamos haber llegado aquí, así que nos vamos
        }
    }

    public function _revert_installation(){ //Desinstalación silenciosa del plugin para el commerce (para cuando no se finaliza la instalación)
        $this->load->model('setting/extension');
        $this->model_setting_extension->uninstall('payment', 'todopago');
        $this->redirect($this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'));
    }

    public function uninstall(){
        $this->redirect($this->url->link('payment/todopago/config_uninstallation', 'token=' . $this->session->data['token'], 'SSL')); //Recirijo para salir del ciclo de desinstallación del plugin y pooder mostrar mi pantalla
    }

    public function config_uninstallation(){ //Permite seleccionar que cambios de instalación deshacer

        //Se prepara el tpl
        $this->data['todopago_version'] = TP_VERSION;
        $this->data['button_continue_text'] = 'Continue';
        $this->data['button_continue_action'] = $this->url->link('payment/todopago/_uninstall', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['back_button_message'] = "Esto ejecutará la instalación básica (No se ejecutará ninguna de las acciones descriptas en la página actual)";

        $this->template = 'todopago/uninstall.tpl';
        $this->children = array(
            'common/header',
            'common/footer'
            );
        $this->response->setOutput($this->render());
    }

    public function _uninstall(){ //Méto do de desinstalación interno, deshace los cambios seleccionados.
        $this->load->model('payment/todopago');
        $this->load->model('todopago/transaccion_admin');

        if (isset($this->request->post['revert_postcode_required'])){
            $this->model_payment_todopago->setPostCodeRequired(false);
        }
        if (isset($this->request->post['drop_column_cs_code'])){
            $this->model_payment_todopago->unsetProvincesCode();
        }
        if (isset($this->request->post['drop_table_todopago_transaccion'])){
            $this->model_todopago_transaccion_admin->dropTable();
        }
        $this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
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
            if ($this->request->post['upgrade'] == '1'){ //Si necesita upgradear llamamos al _install()
                $this->redirect($this->url->link('payment/todopago/_install', 'action='.self::UPGRADE.'&token=' . $this->session->data['token'], 'SSL'));
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
     $this->data['need_upgrade'] = (TP_VERSION > $installedVersion);
     $this->data['todopago_version'] = TP_VERSION;

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

     if (isset($this->request->post['todopago_segmentodelcomercio'])) {
         $this->data['todopago_segmentodelcomercio'] = $this->request->post['todopago_segmentodelcomercio'];
     } else {
         $this->data['todopago_segmentodelcomercio'] = $this->config->get('todopago_segmentodelcomercio');

     }

     if (isset($this->request->post['canaldeingresodelpedido'])){
         $this->data['canaldeingresodelpedido'] = $this->request->post['canaldeingresodelpedido'];
     } else {
         $this->data['canaldeingresodelpedido'] = $this->config->get('canaldeingresodelpedido');
     }

     if (isset($this->request->post['todopago_form'])){
         $this->data['todopago_form'] = $this->request->post['todopago_form'];
     } else {
         $this->data['todopago_form'] = $this->config->get('todopago_form');
     }


     if (isset($this->request->post['todopago_deadline'])){
         $this->data['todopago_deadline'] = $this->request->post['todopago_deadline'];
     } else {
         $this->data['todopago_deadline'] = $this->config->get('todopago_deadline');
     }

     if (isset($this->request->post['todopago_modotestproduccion'])){
         $this->data['todopago_modotestproduccion'] = $this->request->post['todopago_modotestproduccion'];
     } else {
         $this->data['todopago_modotestproduccion'] = $this->config->get('todopago_modotestproduccion');
     }

		//datos para tags ambiente test

     if (isset($this->request->post['todopago_authorizationHTTPtest'])) {
         $this->data['todopago_authorizationHTTPtest'] = $this->request->post['todopago_authorizationHTTPtest'];
     } else {
         $this->data['todopago_authorizationHTTPtest'] = $this->config->get('todopago_authorizationHTTPtest');
     }

     $this->logger->debug ( "1. Authorization: ".json_encode($this->data['todopago_authorizationHTTPtest']));

     if (isset($this->request->post['todopago_idsitetest'])){
         $this->data['todopago_idsitetest'] = $this->request->post['todopago_idsitetest'];
     } else {
         $this->data['todopago_idsitetest'] = $this->config->get('todopago_idsitetest');
     }

     if (isset($this->request->post['todopago_securitytest'])){
         $this->data['todopago_securitytest'] = $this->request->post['todopago_securitytest'];
     } else {
         $this->data['todopago_securitytest'] = $this->config->get('todopago_securitytest');
     }

		//datos para tags ambiente produccion

     if (isset($this->request->post['todopago_authorizationHTTPproduccion'])) {
         $this->data['todopago_authorizationHTTPproduccion'] = $this->request->post['todopago_authorizationHTTPproduccion'];
     } else {
         $this->data['todopago_authorizationHTTPproduccion'] = $this->config->get('todopago_authorizationHTTPproduccion');
     }

     if (isset($this->request->post['todopago_idsiteproduccion'])){
         $this->data['todopago_idsiteproduccion'] = $this->request->post['todopago_idsiteproduccion'];
     } else {
         $this->data['todopago_idsiteproduccion'] = $this->config->get('todopago_idsiteproduccion');
     }

     if (isset($this->request->post['todopago_securityproduccion'])){
         $this->data['todopago_securityproduccion'] = $this->request->post['todopago_securityproduccion'];
     } else {
         $this->data['todopago_securityproduccion'] = $this->config->get('todopago_securityproduccion');
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
  $this->load->model('todopago/transaccion_admin');
  $transaction = $this->model_todopago_transaccion_admin;
  $this->logger->debug('todopago -  step: '.$transaction->getStep($order_id));
//        if($transaction->getStep($order_id) == $transaction->getTransactionFinished()){
  $authorizationHTTP = $this->get_authorizationHTTP();
  $this->logger->debug ( "get_status():authorizationHTTP: ".json_encode($authorizationHTTP));
  $mode = $this->get_mode();
  try{
    $connector = new TodoPago\Sdk($authorizationHTTP, $mode);
    $optionsGS = array('MERCHANT'=>$this->get_id_site(), 'OPERATIONID'=>$order_id);
    $status = $connector->getStatus($optionsGS);
    $status_json = json_encode($status);
    $rta = '';
    if (!empty($status['Operations'])){
        foreach ($status['Operations'] as $key => $value) {
           if (! is_array ( $value )) {
              $value_json = json_encode($value);
              $rta .= "$key: $value_json \n";
          }
      }
  } else {
    $rta = 'No se ecuentra la operación. Esto puede deberse a que la operación no se haya finalizado o a una configuración erronea.';
}
}
catch(Exception $e){
    $this->logger->fatal("Ha surgido un error al consultar el estado de la orden: ", $e);
    $rta = 'ERROR AL CONSULTAR LA ORDEN';
}
//        }
//        else{
//            $rta = "NO HAY INFORMACIÓN DE PAGO";
//        }
echo($rta);

}

public function get_devolver(){
    $monto = $_POST["monto"];
    $order_id = $_POST['order_id'];
    $transaction_row = $this->db->query("SELECT request_key FROM `".DB_PREFIX."todopago_transaccion` WHERE id_orden=$order_id");
    $mode = $this->get_mode();
    $authorizationHTTP = $this->get_authorizationHTTP()["Authorization"];
    $request_key = $transaction_row->row["request_key"];
    
    if ($this->get_mode() == "test"){
            $rest_end_point = "https://developers.todopago.com.ar/t/1.1/api/Authorize";
        }
        else{


            $rest_end_point = "https://api.todopago.com.ar/t/1.1/api/Authorize";
        }    

    $data = array(
        "RequestType"=>"ReturnRequest",
        "Security"=>$this->get_security_code(),
        "RequestKey"=>$request_key,
        "Merchant"=>$this->get_id_site(),
        "Amount"=> $monto
        );

    $data_string = json_encode($data);                                                                                   

    $ch = curl_init($rest_end_point);                                                                      
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        "Authorization: $authorizationHTTP")                                                                       
    );                                                                                                                   

    $result = curl_exec($ch);

    $rta = json_decode($result);
    $this->logger->debug("get_devolver(): (data)".json_encode($data));
    $this->logger->debug("get_devolver(".$rest_end_point."): ".$result);

    echo ($rta->ReturnResponse->StatusMessage);
    echo "\n\r";

    if($rta->ReturnResponse->StatusCode==2011){
        $this->load->model("sale/return");
        $this->model_sale_return->addReturn($this->getReturnValues($order_id, $rta, $data["Amount"]));

              
        echo "DEVOLUCION REALIZADA";
    }else{
        echo "NO SE PUDO REALIZAR DEVOLUCION INTENTE MAS TARDE";
    }       

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
		/* Old source code
		 if ($this->get_mode () == "test") {
		return json_decode ( html_entity_decode ( $this->config->get ( 'todopago_authorizationHTTPtest' ) ), TRUE );
		} else {
		return json_decode ( html_entity_decode ( $this->config->get ( 'todopago_authorizationHTTPproduccion' ) ), TRUE );
  }*/
}

private function get_mode(){
  $this->logger->debug("get_mode(): " .mb_strtolower(html_entity_decode($this->config->get('todopago_modotestproduccion'))));
  return mb_strtolower( html_entity_decode($this->config->get('todopago_modotestproduccion')));
}

private function get_id_site(){
  if($this->get_mode() == MODO_TEST){
     return html_entity_decode($this->config->get('todopago_idsitetest'));
 }else{
     return html_entity_decode($this->config->get('todopago_idsiteproduccion'));
 }
}

private function get_security_code(){
  if($this->get_mode() == MODO_TEST){
     return html_entity_decode($this->config->get('todopago_securitytest'));
 }else{
     return html_entity_decode($this->config->get('todopago_securityproduccion'));
 }
}

private function getReturnValues($order_id, $resp, $amout){
  $this->load->model("sale/order");
  $order = $this->model_sale_order->getOrder($order_id);
  $returnValues = array(
        "order_id"=>$order_id,
        "firstname"=>$order["firstname"],
        "lastname"=>$order["lastname"],
        "telephone"=>$order["telephone"],
        "email"=>$order["email"],
        "product"=>"DEVOLUCION TODOPAGO",
        "model"=>"$".$amout,
        "comment"=>json_encode($resp),
        "customer_id"=>$order["customer_id"],
        "quantity"=>"1",
        "date_ordered"=>$order["date_added"],
        "product_id"=>0,
        "return_reason_id"=>0,
        "return_action_id"=>0,
        "return_status_id"=>0,
        "opened"=>0

    );

  return $returnValues;

}

    //Descomentar e implementar cuando se habiliten los verticales que requieren campos adicionales:
    /*private function createAttributeGroup($name){
        $data = array('sort_order' => 0);

        $this->load->model('catalog/attribute_group');
        $this->load->model('localisation/language');

        $languages = $this->model_localisation_language->getLanguages(); //obitene todos los idiomas instalados
            $attributeGroupDescription = array();
        foreach ($languages as $lang){
            $attributeGroupDescription[$lang['language_id']] = array('name' => $name); //setea el nombre en ese idioma
        }
        $data['attribute_group_description'] = $attributeGroupDescription;

        $this->model_catalog_attribute_group->addAttributeGroup($data); //Crea el attribute_group

        return $this->getAttributeGroupId($name); //devuelve el id del nuevo grupo
    }

    private function createAttribute($name, $attributeGroupId){
        $data = array(
            'sort_order' => 0,
            'attribute_group_id' => $attributeGroupId
        );

        $this->load->model('catalog/attribute');
        $this->load->model('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();
        $attributeDescription = array();
        foreach ($languages as $lang){
                $attributeDescription[$lang['language_id']] = array('name' => $name);
        }
        $data['attribute_description'] = $attributeDescription;

        $this->model_catalog_attribute->addAttribute($data);

    }

    private function getAttributeGroupId($attributeGroupName){

        $this->load->model('catalog/attribute_group');
        $attributeGroups = $this->model_catalog_attribute_group->getAttributeGroups();
        foreach ($attributeGroups as $attrGrp){
            if ($attrGrp['name'] == $attributeGroupName) {
                $attributeGroupId = $attrGrp['attribute_group_id'];
                break;
            }
        }
        return $attributeGroupId;
    }

    private function getAttributeId($attributeName){
        $this->load->model('catalog/attribute');
        $attributes = $this->model_catalog_attribute->getAttributes();
        foreach ($attributes as $attr){
            if ($attr['name'] == $attributeName) {
                $attributeId = $attr['attribute_id'];
                break;
            }
        }
        return $attributeId;
    }

    private function deleteControlFraudeAttributeGroup(){
        $controlFraudeAttributeGroupId = $this->getAttributeGroupId(TP_CS_ATTGROUP);
        $this->load->model('catalog/attribute');
        $controlFraudeAttributeGroupAttributes = $this->model_catalog_attribute->getAttributesByAttributeGroupId(array('filter_attribute_group_id' => $controlFraudeAttributeGroupId));
        foreach ($controlFraudeAttributeGroupAttributes as $attribute){
            $this->model_catalog_attribute->deleteAttribute($attribute['attribute_id']);
        }
        $attributeGroups = $this->model_catalog_attribute_group->getAttributeGroups();
        $this->model_catalog_attribute_group->deleteAttributeGroup($controlFraudeGroupId);
    }*/
}