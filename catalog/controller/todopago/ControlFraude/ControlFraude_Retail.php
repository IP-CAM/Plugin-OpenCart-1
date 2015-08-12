<?php

class ControlFraude_Retail extends ControlFraude{

    protected function completeCFVertical(){
        $payDataOperacion = array();
        //$this->log->writeTP("CSSTCITY - Ciudad de env&iacute;o de la orden");
        $payDataOperacion ['CSSTCITY'] = $this->getField(empty($this->order['shipping_city'])?$this->order['payment_city']:$this->order['shipping_city']);
        
        //$this->log->writeTP("CSSTCOUNTRY Pa&iacute;s de env&iacute;o de la orden");
        $payDataOperacion ['CSSTCOUNTRY'] = $this->getField(empty($this->order['shipping_iso_code_2'])?$this->order['payment_iso_code_2']:$this->order['shipping_iso_code_2']);
        
        //$this->log->writeTP("CSSTEMAIL Mail del destinatario");
        $payDataOperacion ['CSSTEMAIL'] = $this->getField($this->order['email']);
        
        //$this->log->writeTP("CSSTFIRSTNAME Nombre del destinatario");
        $payDataOperacion ['CSSTFIRSTNAME'] = empty($this->order['shipping_firstname'])?$this->order['payment_firstname']:$this->order['shipping_firstname'];
        
        //$this->log->writeTP("CSSTLASTNAME Apellido del destinatario");
        $payDataOperacion ['CSSTLASTNAME'] = empty($this->order['shipping_lastname'])?$this->order['payment_lastname']:$this->order['shipping_lastname'];
        
        //$this->log->writeTP("CSSTPHONENUMBER N&uacute;mero de tel&eacute;fono del destinatario");
        $payDataOperacion ['CSSTPHONENUMBER'] = phone::clean($this->order['telephone']);
        
        //$this->log->writeTP("CSSTPOSTALCODE C&oacute;digo postal del domicilio de env&iacute;o");
        $payDataOperacion ['CSSTPOSTALCODE'] = empty($this->order['shipping_postcode'])?$this->order['payment_postcode']:$this->order['shipping_postcode'];
        
        //$this->log->writeTP("CSSTSTATE Provincia de envacute;o");
        $payDataOperacion ['CSSTSTATE'] = empty($this->order['shipping_zone_cs_code'])?$this->order['payment_zone_cs_code']:$this->order['shipping_zone_cs_code'];
        
        //$this->log->writeTP("CSSTSTREET1 Domicilio de env&iacute;o");
        $payDataOperacion ['CSSTSTREET1'] = empty($this->order['shipping_address_1'])?$this->order['payment_address_1']:$this->order['shipping_address_1'];
        
        //$paydata_operation['CSSSTREET2'] = $this->order['shipping_city']; 
        
        //$this->log->writeTP("CSMDD12 Shipping DeadLine (Num Dias)");
        $paydata_operation ['CSMDD12'] = $this->model->getDeadLine();
        
        //$this->log->writeTP("CSMDD13 M&eacute;todo de Despacho");
        $payDataOperacion ['CSMDD13'] = $this->getField($this->order['shipping_method']);
        
        //$this->log->writeTP("CSMDD14 Customer requires Tax Bill ? (S/N) No");
                //$payData ['CSMDD14'] = "";
        
        //$this->log->writeTP("CSMDD15 Customer Loyality Number No");
                //$payData ['CSMDD15'] = "";
        //$this->log->writeTP("CSMDD16 Promotional / Coupon Code");
        $payDataOperacion ['CSMDD16'] = $this->getField($this->order['coupon_code']);
        $payDataOperacion = array_merge($this->getMultipleProductsInfo(), $payDataOperacion);
        return $payDataOperacion;
    }
}