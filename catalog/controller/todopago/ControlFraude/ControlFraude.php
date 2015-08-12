<?php
//include_once dirname(__FILE__).'/todopago_log.php';

abstract class Controlfraude{

    protected $order;
    private $customer;
    protected $model;
    
	public function __construct($order, $customer, $model){
        $this->order = $order;
		$this->customer = $customer;
        $this->model = $model;
        
        $order_id = $this->order['order_id'];
        
        $this->order['payment_zone_cs_code'] = $this->model->getProvinceCode($this->order['payment_zone_code'], $order_id);
        $this->order['shipping_zone_cs_code'] = $this->model->getProvinceCode($this->order['shipping_zone_code'], $order_id);
        $this->order['coupon_code'] = $this->model->getCouponCode($order_id);
        
        //$this->log = new TodoPagoLog($this->order['order_id'], $this->customer['customer_id']);
	}

	public function getDataCF(){
		$datosCF = $this->completeCF();
		return array_merge($datosCF, $this->completeCFVertical());
	}

	private function completeCF(){
		$payDataOperacion = array();
        
		$billingAdress = $this->order['payment_city'];
		//$this->log->writeTP("CSBTCITY - Ciudad de facturaci&oacute;n");
		$payDataOperacion ['CSBTCITY'] = $this->getField($this->order['payment_city']);
        
		//$this->log->writeTP(" CSBTCOUNTRY - pa&iacute;s de facturaci&oacute;n (ver si magento utiliza C&oacute;digo ISO)");
		$payDataOperacion ['CSBTCOUNTRY'] = $this->order['payment_iso_code_2'];
        
		//$this->log->writeTP(" CSBTCUSTOMERID - identificador del usuario (no correo electronico)");
		$payDataOperacion ['CSBTCUSTOMERID'] = $this->order['customer_id'];

		if($payDataOperacion ['CSBTCUSTOMERID']=="" or $payDataOperacion ['CSBTCUSTOMERID']==null)
		{
			$payDataOperacion ['CSBTCUSTOMERID']= "guest".date("ymdhs");
		}
        
		//$this->log->writeTP(" CSBTIPADDRESS - ip de la pc del comprador");
		$payDataOperacion ['CSBTIPADDRESS'] = $this->order['ip'];
        
		//$this->log->writeTP(" CSBTEMAIL - email del usuario al que se le emite la factura");
		$payDataOperacion ['CSBTEMAIL'] = $this->order['email'];
        
		//$this->log->writeTP(" CSBTFIRSTNAME - nombre de usuario el que se le emite la factura");
		$payDataOperacion ['CSBTFIRSTNAME'] = $this->order['payment_firstname'];
        
		//$this->log->writeTP(" CSBTLASTNAME - Apellido del usuario al que se le emite la factura");
		$payDataOperacion ['CSBTLASTNAME'] = $this->order['payment_lastname'];
        
		//$this->log->writeTP(" CSBTPOSTALCODE - Código Postal de la dirección de facturación");
		$payDataOperacion ['CSBTPOSTALCODE'] = $this->order['payment_postcode'];
        
		//$this->log->writeTP(" CSBTPHONENUMBER - Tel&eacute;fono del usuario al que se le emite la factura. No utilizar guiones, puntos o espacios. Incluir c&oacute;digo de pa&iacute;s");
		$payDataOperacion ['CSBTPHONENUMBER'] = phone::clean($this->order['telephone']);
        
		//$this->log->writeTP(" CSBTSTATE - Provincia de la direcci&oacute;n de facturaci&oacute;n (hay que cambiar esto!!! por datos hacepatdos por el gateway)");
		$payDataOperacion ['CSBTSTATE'] =  $this->order['payment_zone_cs_code'];
            
		//$this->log->writeTP(" CSBTSTREET1 - Domicilio de facturaci&oacute;n (calle y nro)");
		$payDataOperacion ['CSBTSTREET1'] = $this->order['payment_address_1'];
        
		////$this->log->writeTP(" CSBTSTREET2 - Complemento del domicilio. (piso, departamento)_ No Mandatorio");
		//$payDataOperacion ['CSBTSTREET2'] = $this->order['payment_address_2'];
        
		//$this->log->writeTP(" CSPTCURRENCY- moneda");
		$payDataOperacion ['CSPTCURRENCY'] = "ARS";
        
		//$this->log->writeTP(" CSPTGRANDTOTALAMOUNT - 999999[.CC] Con decimales opcional usando el puntos como separador de decimales. No se permiten comas, ni como separador de miles ni como separador de decimales.");
		$payDataOperacion ['CSPTGRANDTOTALAMOUNT'] = number_format($this->order['total'], 2, ".", "");
        
		////$this->log->writeTP(" CSMDD6 - Canal de venta");
		//$payDataOperacion ['CSMDD6'] = $this->config->get('canaldeingresodelpedido');
        
		if(!empty($this->customer)){
        //$this->log->writeTP(" CSMDD7 - Fecha Registro Comprador (num Dias) - ver que pasa si es guest");
        $payDataOperacion['CSMDD7'] = $this->getDaysQty($this->customer['date_added']);
            
		//$this->log->writeTP(" CSMDD8 - Usuario Guest? (S/N). En caso de ser Y, el campo CSMDD9 no deber&acute; enviarse");
            $payDataOperacion['CSMDD8'] = "S";
            
			//$this->log->writeTP(" CSMDD9 - Customer password Hash: criptograma asociado al password del comprador final");
            $payDataOperacion['CSMDD9'] = $this->customer['password'];   
            
            $payDataOperacion['CSMDD10'] = $this->model->getQtyOrders($this->customer['customer_id']);
            
        } else
        {
            $payDataOperacion['CSMDD8'] = "N";
        }

		//$this->log->writeTP(" CSMDD11 Customer Cell Phone");
        
		$payDataOperacion['CSMDD11'] = phone::clean($this->order['telephone']);

		return $payDataOperacion;

	}

	private function _sanitize_string($string){
		$string = htmlspecialchars_decode($string);

		$re = "/\\[(.*?)\\]|<(.*?)\\>/i";
		$subst = "";
		$string = preg_replace($re, $subst, $string);

		$replace = array("!","'","\'","\"","  ","$","\\","\n","\r",
			'\n','\r','\t',"\t","\n\r",'\n\r','&nbsp;','&ntilde;',".,",",.","+", "%", "-", ")", "(", "°");
		$string = str_replace($replace, '', $string);

		$cods = array('\u00c1','\u00e1','\u00c9','\u00e9','\u00cd','\u00ed','\u00d3','\u00f3','\u00da','\u00fa','\u00dc','\u00fc','\u00d1','\u00f1');
		$susts = array('Á','á','É','é','Í','í','Ó','ó','Ú','ú','Ü','ü','Ṅ','ñ');
		$string = str_replace($cods, $susts, $string);

		$no_permitidas= array ("á","é","í","ó","ú","Á","É","Í","Ó","Ú","ñ","À","Ã","Ì","Ò","Ù","Ã™","Ã ","Ã¨","Ã¬","Ã²","Ã¹","ç","Ç","Ã¢","ê","Ã®","Ã´","Ã»","Ã‚","ÃŠ","ÃŽ","Ã”","Ã›","ü","Ã¶","Ã–","Ã¯","Ã¤","«","Ò","Ã","Ã„","Ã‹");
		$permitidas= array ("a","e","i","o","u","A","E","I","O","U","n","N","A","E","I","O","U","a","e","i","o","u","c","C","a","e","i","o","u","A","E","I","O","U","u","o","O","i","a","e","U","I","A","E");
		$string = str_replace($no_permitidas, $permitidas ,$string);

		return $string;
	}
    
    private function getDaysQty($date){
        $date = new DateTime($date);
        $now = new DateTime();
        
        $diff = $date->diff($now);
        return $diff->days;
    }

	protected function getMultipleProductsInfo(){
		$payDataOperacion = array();
        
		$products = $this->model->getProducts($this->order['order_id']);
        ///datos de la orden separados con #
		$productcode_array = array();
		$description_array = array();
		$name_array = array();
		$sku_array = array();
		$totalamount_array = array();
		$quantity_array = array();
		$price_array = array();

		foreach($products as $item){
			
			$productcode_array[] = $this->model->getProductCode($item['product_id']);

			$_description = $item['description'];
			$_description = $this->getField($_description);
			$_description = trim($_description);
			$_description = substr($_description, 0,15);
			$description_array [] = str_replace("#","",$_description);

			$product_name = $item['name'];
			$name_array [] = $product_name;

			$sku = $item['product_id']; //Uso el id en lugar del SKU ya que el id es requerido por opencart y el SKU no.
			$sku_array [] = $this->getField($sku);

			
			$totalamount_array[] = number_format($item['total'], 2 , ".", "");

			$quantity_array [] = intval($item['quantity']);

			$price_array [] = number_format($item['price'], 2, ".", "");

		}
		$payDataOperacion ['CSITPRODUCTCODE'] = join('#', $productcode_array);
		$payDataOperacion ['CSITPRODUCTDESCRIPTION'] = join("#", $description_array);
		$payDataOperacion ['CSITPRODUCTNAME'] = join("#", $name_array);
		$payDataOperacion ['CSITPRODUCTSKU'] = join("#", $sku_array);
		$payDataOperacion ['CSITTOTALAMOUNT'] = join("#", $totalamount_array);
		$payDataOperacion ['CSITQUANTITY'] = join("#", $quantity_array);
		$payDataOperacion ['CSITUNITPRICE'] = join("#", $price_array);
		return $payDataOperacion;
	}

	public function getField($datasources){
		$return = "";
		try{

			$return = $this->_sanitize_string($datasources);
			//$this->log->writeTP("devolvio $return");

		}catch(Exception $e){
			//$this->log->writeTP("Modulo de pago - TodoPago ==> operation_id:  $this->order->getIncrementId() - no se pudo agregar el campo: Exception: $e");
		}

		return $return;
	}

	protected abstract function completeCFVertical();

}