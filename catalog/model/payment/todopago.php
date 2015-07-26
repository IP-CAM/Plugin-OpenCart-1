<?php
class ModelPaymentTodopago extends Model {
  public function getMethod($address, $total) {
    $this->load->language('payment/todopago');
    
    $method_data = array(
      'code'     => 'todopago',
      'title'    => $this->language->get('text_title'),
      'sort_order' => $this->config->get('todopago_sort_order')
      );
    
    return $method_data;
  }
  
  public function getProductCode($productId){
    return $this->getAttribute($productId, "codigo del producto");
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
  }