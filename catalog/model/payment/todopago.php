<?php
require_once DIR_APPLICATION.'../admin/resources/todopago/Logger/loggerFactory.php';

class ModelPaymentTodopago extends Model {

    private $logger;

    public function __construct($registry){
        parent::__construct($registry);
    }

  public function getMethod($address, $total) {
    $this->load->language('payment/todopago');
    $method_data = array(
      'code'     => 'todopago',
      'title'    => $this->language->get('text_title'),
      'sort_order' => $this->config->get('todopago_sort_order')
      );
    
    return $method_data;
  }
    
public function setLogger($logger){
    $this->logger = $logger;
}

    public function getProducts($order_id){
        $products = $this->db->query("SELECT op.product_id, op.total, op.name, op.price, op.quantity, pd.description FROM `".DB_PREFIX."order_product` op INNER JOIN `".DB_PREFIX."product_description` pd ON op.product_id = pd.product_id  WHERE `order_id`=".(int)$order_id." AND language_id=".(int)$this->config->get('config_language_id').";");
        return $products->rows;
    }

    public function getSku($productId){
        $query = "SELECT sku from ".DB_PREFIX."product WHERE product_id = ".$productId.";";
        $this->logger->debug("SKU query: ".$query);

        $queryResult = $this->db->query($query);

        $sku = $queryResult->row['sku'];

        return $sku ?: $productId;
    }
  
  public function getProductCode($productId){
      //$productCode = $this->getAttribute($productId, "codigo del producto");

      $query = "SELECT c.name AS category FROM ".DB_PREFIX."product AS p INNER JOIN ".DB_PREFIX."product_to_category as pc ON p.product_id = pc.product_id INNER JOIN ".DB_PREFIX."category_description AS c ON pc.category_id = c.category_id WHERE p.product_id = ".$productId.";";
      $this->logger->debug("query productCode: ".$query);
      $result = $this->db->query($query);
      $productCode = $result->row['category'];

      return ($productCode != null)? $productCode : "default"; //Si no tiene categoría asignada, devueelve default.
  }
    
  private function getAttribute($productId, $attribute){
    try{
      $query = $this->db->query("SELECT ".DB_PREFIX."product_attribute.text FROM ".DB_PREFIX."product_attribute JOIN ".DB_PREFIX."attribute ON ".DB_PREFIX."attribute.attribute_id = ".DB_PREFIX."product_attribute.attribute_id JOIN ".DB_PREFIX."attribute_description ON ".DB_PREFIX."attribute.attribute_id = ".DB_PREFIX."attribute_description.attribute_id JOIN ".DB_PREFIX."attribute_group_description ON ".DB_PREFIX."attribute.attribute_group_id = ".DB_PREFIX."attribute_group_description.attribute_group_id WHERE product_id = 31 AND ".DB_PREFIX."attribute_description.name = '".$attribute."' AND ".DB_PREFIX."attribute_group_description.name = 'Prevencion de Fraude'");
      
      if(array_key_exists ( 'text' , $query->row )){
          return $att = $query->row['text'];  
      }     
    }catch (Exception $e){
        return "default";
      }
    }
    
    public function getCouponCode($order_id){
        $coupon_id = $this->db->query("SELECT coupon_id FROM  `".DB_PREFIX."coupon_history` WHERE `order_id` = $order_id");
            if(isset($coupon_id->row['coupon_id'])){
                $coupon_id = $coupon_id->row['coupon_id'];
                $coupon_code = $this->db->query("SELECT code FROM `".DB_PREFIX."coupon` WHERE `coupon_id` = $coupon_id");
                return $coupon_code->row['code'];
            }
        else{
            return null;
        }
    }
    
    public function getProvinceCode($ocCode, $order_id){
        $csCode = $this->db->query("select z.cs_code from ".DB_PREFIX."zone z inner join ".DB_PREFIX."country c on  z.country_id = c.country_id where c.iso_code_2 = 'AR' and code = '".$ocCode."';");
        
        return $csCode->row['cs_code'];
    }
    
    public function getQtyOrders($customerId){
        $qty = $this->db->query("SELECT COUNT(*) AS qty FROM ".DB_PREFIX."order WHERE customer_id = $customerId;");
        return $qty->row['qty'];
    }
    
    public function getDeadLine(){
        return $this->config->get('todopago_deadline');
    }
  }
