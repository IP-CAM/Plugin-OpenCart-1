<?php
include_once DIR_APPLICATION.'resources/todopago/Logger/loggerFactory.php';
require_once DIR_APPLICATION.'resources/todopago/todopago_ctes.php';

class ModelPaymentTodopago extends Model {

    public function __construct($registry){
        parent::__construct($registry);
        $this->logger = loggerFactory::createLogger();
    }

	public function get_orders()
	{
		$get_orders = $this->db->query("SELECT order_id, date_added ,store_name, firstname, lastname, total  FROM `".DB_PREFIX."order` WHERE order_status_id<>0 AND payment_code='todopago';");
		return $get_orders;
	}
    
    public function getVersion(){
        $actualVersionQuery = $this->db->query("SELECT value FROM `".DB_PREFIX."setting` WHERE `group` = 'todopago' AND `key` = 'todopago_version'");
        $actualVersion = ($actualVersionQuery->num_rows == 0)? "0.0.0" : $actualVersionQuery->row['value'];

	   return $actualVersion;
    }

    
    public function updateVersion($actualVersion){
        if($actualVersion == '0.0.0') {
            $this->db->query("INSERT INTO ".DB_PREFIX."setting (store_id, `group`, `key`, value, serialized) VALUES (0, 'todopago', 'todopago_version', '".TP_VERSION."', 0);");
        }
        else{
            $this->db->query("UPDATE ".DB_PREFIX."setting SET `value`='".TP_VERSION."' WHERE `group`='todopago' AND `key`='todopago_version';");
        }
    }
    
    public function setProvincesCode(){
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
    
    public function unsetProvincesCode(){
        $this->db->query("ALTER TABLE `".DB_PREFIX."zone` DROP COLUMN cs_code;");
    }

    public function setPostCodeRequired($required = true){
        $this->db->query("UPDATE `".DB_PREFIX."country` set postcode_required=".(int)$required." Where iso_code_2='AR';"); //Hace obligatorio el código postal para Argentina ya que es necesario para que la compra sea procesada. En caso de que $required = false lo setea  como no obligatorio.
    }
}



