<?php
require_once DIR_APPLICATION.'../catalog/model/todopago/transaccion.php';

class ModelTodopagoTransaccionAdmin extends ModelTodopagoTransaccion {

    public function createTable(){
        $this->db->query("CREATE TABLE IF NOT  EXISTS `".DB_PREFIX."todopago_transaccion` (`id` INT NOT NULL AUTO_INCREMENT,`id_orden` INT NULL, `first_step` TIMESTAMP NULL,`params_SAR` TEXT NULL, `response_SAR` TEXT NULL, `second_step` TIMESTAMP NULL, `params_GAA` TEXT NULL, `response_GAA` TEXT NULL, `request_key` TEXT NULL, `public_request_key` TEXT NULL, `answer_key` TEXT NULL, PRIMARY KEY (`id`));");
    }

    public function dropTable(){
        $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."todopago_transaccion`;");
    }
}
