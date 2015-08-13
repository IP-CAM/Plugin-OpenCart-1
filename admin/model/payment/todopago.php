<?php
require_once DIR_APPLICATION.'resources/todopago/todopago_ctes.php';

class ModelPaymentTodopago extends Model {

	public function get_orders()
	{
		$get_orders = $this->db->query("SELECT order_id, date_added ,store_name, firstname, lastname, total  FROM `".DB_PREFIX."order` WHERE order_status_id<>0 AND payment_code='todopago';");
		return $get_orders;
	}
    
    public function getVersion(){
        $actualVersionQuery = $this->db->query("SELECT value FROM `".DB_PREFIX."setting` WHERE `group` = 'todopago' AND `key` = 'version'");
        $actualVersion = ($actualVersionQuery->num_rows == 0)? "0." : $actualVersionQuery->row['value'];
        if($actualVersion == "0."){
            $todopagoclavecolumn = $this->db->query("SHOW COLUMNS FROM `".DB_PREFIX."order` LIKE 'todopagoclave'"); //Esta consulta sólo es válida para MySQL 5.0.1+
        //En caso de haber desinstaldo el plugin al instalarlo de nuevo la columna todopagoclave ya se econtraría creada, así que verificamos que no exista antes de crearla
            
            $actualVersion .= ($todopagoclavecolumn->num_rows != 1)? "0.0" : "9.0";
        }
	   return $actualVersion;
    }
    
    public function upgrade(){
        $this->writeLog("Verifying required upgrades");
        /*******************************************************************
        *Al no tener breaks entrará en todos los case posteriores.         *
        *TODAS LAS VERSIONES DEBEN APARECER,                               *
        *de lo contrario la version que no aparezca NUNCA PODRÁ UPGRADEARSE*
        *******************************************************************/
        $actualVersion = $this->getVersion();
        $this->writeLog("version actual", $actualVersion);
        switch ($actualVersion){
            case "0.0.0":
                $this->install();
            case "0.9.0":
                $this->upgrade0_9_9();
            case "0.9.9":
                $this->upgrade1_0_0();
            case "1.0.0":
                $this->writeLog("upgrade to v1.1.0");
            case "1.1.0":
                $this->upgrade1_1_1();
            case "1.1.1":
                $this->writeLog("upgrade to v1.2.0");
            case "1.2.0":
                $this->db->query("UPDATE ".DB_PREFIX."setting SET `value`='".TP_VERSION."' WHERE `group`='todopago' AND `key`='version';");
        }
    }
    
    private function install(){
        $storeId = 0;
        
        $this->writeLog('Begining install');
          
            
		  $this->db->query("ALTER TABLE `".DB_PREFIX."order` ADD `todopagoclave` VARCHAR( 255 );");
		
		$this->db->query("INSERT INTO ".DB_PREFIX."attribute_group (sort_order) VALUES (0);");
		
		$this->db->query("INSERT INTO ".DB_PREFIX."attribute_group_description(attribute_group_id, language_id, name)
			SELECT (SELECT MAX(attribute_group_id) attribute_group_id FROM ".DB_PREFIX."attribute_group ), language_id, 'Prevencion de Fraude'  
			FROM ".DB_PREFIX."language;");

		$this->db->query("INSERT INTO ".DB_PREFIX."attribute (`attribute_group_id`, `sort_order`) 
			VALUES ((SELECT MAX(attribute_group_id) FROM ".DB_PREFIX."attribute_group ), 0);");
		
		$this->db->query("INSERT INTO ".DB_PREFIX."attribute_description (attribute_id, language_id, name)
			SELECT (SELECT MAX(attribute_id) attribute_id FROM ".DB_PREFIX."attribute), language_id, 'fecha evento' 
			FROM ".DB_PREFIX."language;");
		$this->db->query("INSERT INTO ".DB_PREFIX."attribute (`attribute_group_id`, `sort_order`) 
			VALUES ((SELECT MAX(attribute_group_id) FROM ".DB_PREFIX."attribute_group ), 0);");
		
		$this->db->query("INSERT INTO ".DB_PREFIX."attribute_description (attribute_id, language_id, name)
			SELECT (SELECT MAX(attribute_id) attribute_id FROM ".DB_PREFIX."attribute), language_id, 'codigo del producto' 
			FROM ".DB_PREFIX."language;");

		$this->db->query("INSERT INTO ".DB_PREFIX."attribute (`attribute_group_id`, `sort_order`) 
			VALUES ((SELECT MAX(attribute_group_id) FROM ".DB_PREFIX."attribute_group ), 0);");
		
		$this->db->query("INSERT INTO ".DB_PREFIX."attribute_description (attribute_id, language_id, name)
			SELECT (SELECT MAX(attribute_id) attribute_id FROM ".DB_PREFIX."attribute), language_id, 'Tipo de envio' 
			FROM ".DB_PREFIX."language;");

		$this->db->query("INSERT INTO ".DB_PREFIX."attribute (`attribute_group_id`, `sort_order`) 
			VALUES ((SELECT MAX(attribute_group_id) FROM ".DB_PREFIX."attribute_group ), 0);");
		
		$this->db->query("INSERT INTO ".DB_PREFIX."attribute_description (attribute_id, language_id, name)
			SELECT (SELECT MAX(attribute_id) attribute_id FROM ".DB_PREFIX."attribute), language_id, 'Tipo de servicio' 
			FROM ".DB_PREFIX."language;");

		$this->db->query("INSERT INTO `".DB_PREFIX."attribute` (`attribute_group_id`, `sort_order`) 
			VALUES ((SELECT MAX(attribute_group_id) FROM ".DB_PREFIX."attribute_group ), 0);");
		
		$this->db->query("INSERT INTO ".DB_PREFIX."attribute_description (attribute_id, language_id, name)
			SELECT (SELECT MAX(attribute_id) attribute_id FROM ".DB_PREFIX."attribute), language_id, 'Tipo de delivery' 
			FROM ".DB_PREFIX."language;");
        }
    
    private function upgrade0_9_9(){
        
        $this->writeLog("Upgrade to v0.9.9");
        
        $this->db->query("CREATE TABLE IF NOT  EXISTS `".DB_PREFIX."todopago_transaccion` (`id` INT NOT NULL AUTO_INCREMENT,`id_orden` INT NULL, `first_step` TIMESTAMP NULL,`params_SAR` TEXT NULL, `response_SAR` TEXT NULL, `second_step` TIMESTAMP NULL, `params_GAA` TEXT NULL, `response_GAA` TEXT NULL, `request_key` TEXT NULL, `public_request_key` TEXT NULL, `answer_key` TEXT NULL, PRIMARY KEY (`id`));");
    }
    
    private function upgrade1_0_0(){
        $this->writeLog("upgrade to v1.0.0");
        
        $this->setProvincesCode();
    }
    
    private function setProvincesCode(){
        $cs_codeColumn = $this->db->query("SHOW COLUMNS FROM `".DB_PREFIX."zone` LIKE 'cs_code'");
        
        if($cs_codeColumn->num_rows == 0){
            $this->db->query("ALTER TABLE `".DB_PREFIX."zone` ADD cs_code char(1);");
        }
        
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'V' WHERE code = 'AN' OR code = 'TF';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'B' WHERE code = 'BA';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'K' WHERE code = 'CA';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'H' WHERE code = 'CH';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'U' WHERE code = 'CU';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'X' WHERE code = 'CO';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'W' WHERE code = 'CR';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'C' WHERE code = 'DF';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'R' WHERE code = 'ER';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'P' WHERE code = 'FO';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'Y' WHERE code = 'JU';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'L' WHERE code = 'LP';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'F' WHERE code = 'LR';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'M' WHERE code = 'ME';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'N' WHERE code = 'MI';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'Q' WHERE code = 'NE';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'R' WHERE code = 'RN';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'A' WHERE code = 'SA';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'J' WHERE code = 'SJ';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'D' WHERE code = 'SL';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'Z' WHERE code = 'SC';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'S' WHERE code = 'SF';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'G' WHERE code = 'SD';");
        $this->db->query("UPDATE `".DB_PREFIX."zone` SET cs_code = 'T' WHERE code = 'TU';");
    }
    
    private function upgrade1_1_1(){
        $this->writeLog("upgrade to v.1.1.1");
        
        $this->db->query("UPDATE `".DB_PREFIX."country` set postcode_required=1 Where iso_code_2='AR';"); //Hace obligatorio el códigoo postal para Argentiiina ya que es necesario para que la compra sea procesada.
    }
    
     function writeLog($action, $params = false){
        $logMessage = "todopago - ".$action;
        $logMessage .= $params? " - parametros: ".json_encode($params):'';
        $this->log->write($logMessage);
    }
}



