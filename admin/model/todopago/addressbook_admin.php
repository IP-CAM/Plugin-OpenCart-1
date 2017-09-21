<?php

require_once DIR_APPLICATION . '../catalog/model/todopago/addressbook.php';

/**
 * Address Book Model Admin
 * User: TodoPago
 * Date: 03/07/17
 * Time: 12:01
 */
class ModelTodopagoAddressbookAdmin extends ModelTodopagoAddressbook
{
    public function createTable()
    {
        $this->db->query("CREATE TABLE IF NOT  EXISTS `" . DB_PREFIX . "todopago_addressbook` (`id` INT NOT NULL AUTO_INCREMENT, `md5_hash` VARCHAR(33), `street` VARCHAR(100), `state` VARCHAR(3), `city` VARCHAR(100), `country` VARCHAR(3), `postal` VARCHAR(50), PRIMARY KEY (id));");
    }

    public function dropTable()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "todopago_addressbook`;");
    }
}