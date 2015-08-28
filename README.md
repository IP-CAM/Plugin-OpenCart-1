<a name="inicio"></a>
OpenCart
============

Plug in para la integración con gateway de pago <strong>Todo Pago</strong>
- [Consideraciones Generales](#consideracionesgenerales)
- [Instalación](#instalacion)
- [Configuración](#configuracion)
 - [Configuración plug in](#confplugin)
- [Datos adiccionales para prevención de fraude](#PrevencionFraude)
- [Consulta de transacciones](#constrans)
- [Tablas de referencia](#tablas)

[](#consideracionesgenerales)
## Consideraciones Generales
El plug in de pagos de <strong>Todo Pago</strong>, provee a las tiendas Opencart de un nuevo m&eacute;todo de pago, integrando la tienda al gateway de pago.
La versión de este plug in esta testeada en PHP 5.4-5.3 y Opencart 1.5.6.4 (no es compatible copn versiones 2.x en adelante).

Por requerimientos del medio de pago el plugin configurará l código postal como obligatorio para Argentina. Los clientes que NO tengan código postal configurado NO podrán pagar.

<a name="instalacion"></a>
## Instalación
1. Descomprimir el archivo opencart-plugin-master.zip. 
2.	Copiar carpeta 'catalog' y 'admin' al root de opencart con los mismos nombres.
3.	Luego ir a 'extensions->payments' y ir a la opción Todo Pago e instalar.

![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/README.img/opencart_configuracion.PNG)

Observaci&oacute;n:
Descomentar: <em>extension=php_soap.dll</em> del php.ini, ya que para la conexión al gateway se utiliza la clase <em>SoapClient</em> del API de PHP.
<br />
[<sub>Volver a inicio</sub>](#inicio)

<a name="upgrade"></a>
## Upgrade desde una versión anterior
1. Descomprimir el archivo opencart-plugin-master.zip. 
2. Copiar carpeta 'catalog' y 'admin' al root de opencart con los mismos nombres.
3. Luego ir a 'extensions->payments' y ir a la opción Todo Pago y editar
4. AL hacerlo aparecerá un mensaje informando la necesidad de upgradear el plugin y un botón "upgrade" en lugar del de "guardar".

![imagen de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/README.img/opencart_configuracion.PNG)

Observaci&oacute;n:
Descomentar: <em>extension=php_soap.dll</em> del php.ini, ya que para la conexión al gateway se utiliza la clase <em>SoapClient</em> del API de PHP.
<br />
[<sub>Volver a inicio</sub>](#inicio)

<a name="configuracion"></a>
##Configuración

[configuración plug in](#confplugin).
<a name="confplugin"></a>
####Configuración plug in
Una vez instalado el plug in, ir a 'extensions->payments' y ir a la opción Todo Pago, hacer click en <em>edit</em>, para comenzar a configurar.
Para llegar al menu de configuración ir a: <em>System->Configuration</em> y seleccionar Paymenth Method en el menú izquierdo. . La configuracion del Plug in esta dividido en 5 solapas (GENERAL, AMBIENTE DEVELOPERS, AMBIENTE PRODUCCION, ESTADO DEL PEDIDO) y una solapa adiccional (Status de la Operación) que se utiliza para ver el estado de la orden (transacción) de manera on line.
![imagen de solapas de configuracion](https://raw.githubusercontent.com/TodoPago/imagenes/master/README.img/opencart1.5_pgv1.0.1/conf_solapas.PNG)

<sub><em>Menú principal</em></sub>

<a name="confplanes"></a>
<br />

[<sub>Volver a inicio</sub>](#inicio)
<a name="tca"></a>
## Nuevas columnas y atributos
El plug in para lograr las nuevas funcionalidades y su persistencia dentro del framework crear&aacute; nuevas tablas, columnas y atributos:

#####Nuevas Columnas:
1. en tabla order: todopagoclave.

<!--#####Nuevos atributos:
1. del tipo "attribute": fecha evento, codigo del producto, Tipo de envio, Tipo de servicio, Tipo de delivery.<br />
Los valores posiblespara cada uno de estos atributos deben ser:
![imagen nuevos catalogo producto](https://raw.githubusercontent.com/TodoPago/imagenes/master/README.img/open_attributes.PNG)
- Para **"codigo del poducto"** (solo para *Retail*): adult_content,  coupon, default, electronic_good, electronic_software, gift_certificate, handling_only, service, shipping_and_handling, shipping_only o subscription.
<br/>
[<sub>Volver a inicio</sub>](#inicio)

<a name="PrevencionFraude"></a>-->
## Prevención de Fraude
- [Consideraciones Generales](#cons_generales)
- [Consideraciones para vertical RETAIL](#cons_retail)

<a name="cons_generales"></a>
####Consideraciones Generales (para todas las verticales, por defecto RETAIL)
El plug in, toma valores est&aacute;ndar del framework para validar los datos del comprador. Principalmente se utiliza una instancia de la clase *checkout/order*.
Para acceder a los datos del comprador se utiliza el metodo $this->model_checkout_order->getOrder($order_id) que devuelve un array:

```php
   $this->load->model('checkout/order');
   $order = $this->model_checkout_order->getOrder($order_id);
-- Ciudad de Facturación: $order['payment_city'];
-- País de facturación: $order['payment_iso_code_2'];
-- Identificador de Usuario: $order['customer_id'];
-- Email del usuario al que se le emite la factura: $order['email'];
-- Nombre de usuario el que se le emite la factura: $order['payment_firstname'];
-- Apellido del usuario al que se le emite la factura: $order['payment_lastname'];
-- Teléfono del usuario al que se le emite la factura: $order['telephone'];
-- Provincia de la dirección de facturación: $order['payment_zone_code'];
-- Domicilio de facturación: $order['payment_address_1'];;
-- Complemento del domicilio. (piso, departamento): $order['payment_address_2'];
-- Moneda: $order['currency_code'];
-- Total:  $order['total'];
-- IP de la pc del comprador: $order['ip'];
```
Otros de los modelos utlilizados es <em>Customer</em> del cual a trav&eacute;s  del m&eacute;todo <em>getPasswordHash()</em>, se extrae el password del usuario (comprador) y la tabla <em>sales_flat_invoice_grid</em>, donde se consultan las transacciones facturadas al comprador. 
<a name="cons_retail"></a> 
####Consideraciones para vertical RETAIL
Las consideración para el caso de empresas del rubro <strong>RETAIL</strong> son similares a las <em>consideraciones generales</em> con la diferencia de se utiliza el m&eacute;todo getShippingAddress() que devuelve un objeto y del cual se utilizan los siguientes m&eacute;todos;
```php
-- Ciudad de envío de la orden: $order->getShippingAddress()->getCity();
-- País de envío de la orden: $order->getShippingAddress()->getCountry();
-- Mail del destinatario: $order->getShippingAddress()->getEmail();
-- Nombre del destinatario: $order->getShippingAddress()->getFirstname();
-- Apellido del destinatario: $order->getShippingAddress()->getLastname();
-- Número de teléfono del destinatario: $order->getShippingAddress()->getTelephone();
-- Código postal del domicio de envío: $order->getShippingAddress()->getPostcode();
-- Provincia de envío: $order->getShippingAddress()->getRegion();
-- Domicilio de envío: $order->getShippingAddress()->getStreet1();
-- Método de despacho: $order->getShippingDescription();
-- Código de cupón promocional: $order->getCuponCode();
-- Para todo lo referido productos: $order->getItemsCollection();
```
nota: el valor resultante de $order->getItemsCollection(), se usan como referencias para conseguir informaci&oacute;n del modelo catalog/producto - a través de los métodos <strong>getDescription(), getName(), getSku(), getQtyOrdered(), getPrice()</strong>-.

####Muy Importante
<strong>Provincias:</strong> uno de los datos requeridos para prevención común a todos los verticales  es el campo provinicia/state tanto del comprador como del lugar de envío, para tal fin el plug in utiliza el valor del campo región de las tablas de la orden.
<br />
[<sub>Volver a inicio</sub>](#inicio)

<a name="constrans"></a>
## Consulta de Transacciones
El plug in crea un nuevo <strong>tab</strong> para poder consultar <strong>on line</strong> las características de la transacci&oacute;n en el sistema de Todo Pago.
![imagen consulta de trnasacciones](https://raw.githubusercontent.com/TodoPago/imagenes/master/README.img/opencart_statusdelaoperacion.PNG)<br />
[<sub>Volver a inicio</sub>](#inicio)

<a name="tablas"></a>
## Tablas de Referencia
######[Provincias](#p)

<a name="p"></a>
<p>Provincias</p>
<table>
<tr><th>Provincia</th><th>Código</th></tr>
<tr><td>CABA</td><td>C</td></tr>
<tr><td>Buenos Aires</td><td>B</td></tr>
<tr><td>Catamarca</td><td>K</td></tr>
<tr><td>Chaco</td><td>H</td></tr>
<tr><td>Chubut</td><td>U</td></tr>
<tr><td>Córdoba</td><td>X</td></tr>
<tr><td>Corrientes</td><td>W</td></tr>
<tr><td>Entre Ríos</td><td>R</td></tr>
<tr><td>Formosa</td><td>P</td></tr>
<tr><td>Jujuy</td><td>Y</td></tr>
<tr><td>La Pampa</td><td>L</td></tr>
<tr><td>La Rioja</td><td>F</td></tr>
<tr><td>Mendoza</td><td>M</td></tr>
<tr><td>Misiones</td><td>N</td></tr>
<tr><td>Neuquén</td><td>Q</td></tr>
<tr><td>Río Negro</td><td>R</td></tr>
<tr><td>Salta</td><td>A</td></tr>
<tr><td>San Juan</td><td>J</td></tr>
<tr><td>San Luis</td><td>D</td></tr>
<tr><td>Santa Cruz</td><td>Z</td></tr>
<tr><td>Santa Fe</td><td>S</td></tr>
<tr><td>Santiago del Estero</td><td>G</td></tr>
<tr><td>Tierra del Fuego</td><td>V</td></tr>
<tr><td>Tucumán</td><td>T</td></tr>
</table>
[<sub>Volver a inicio</sub>](#inicio)
