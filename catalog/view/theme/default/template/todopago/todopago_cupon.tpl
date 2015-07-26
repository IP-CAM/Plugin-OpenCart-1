<?php 
echo $header;
echo $column_left;
echo $column_right; 

$tipo_code = $_GET['tipocode'];
$bar = $_GET['code'];
$order_id = $_GET['nroop'];
$vencimiento = $_GET['venc'];
$total = $_GET['total'];
$empresa = $_GET['emp'];
$this->load->model('checkout/order');
$order = $this->model_checkout_order->getOrder($order_id);
?>
<div id="content" style="width: 75%;">
	
	<div class="titulos"><h2><?php echo $order['store_name'] ?><h2><hr></div>
	<div><div class="titulos">Nro de Operaci&oacute;n:</div><em><strong><?php echo $order_id ?></strong></em><hr></div>
	<div><div class="titulos">Total a pagar</div>$ <?php echo $total ?><hr></div>
	<div><div class="titulos">Vencimiento</div> <?php echo $vencimiento ?><hr></div>
	<div class="titulos"><h3>DATOS PERSONALES<h3><hr></div>
	<div><div class="titulos">Nombre</div> <?php echo $order['firstname']." ". $order['lastname']?> <hr></div>
	<div><div class="titulos">Podr&aacute;s pagar este cup&oacute;n en los locales de:</div><?php echo $empresa ?><hr></div>
	
	<img style="width: 100%; height: 100px" src="<?php echo 'http://localhost:8888/opencart-1.5.6.4/upload/index.php?route=todopago/todopago/codebar&tipo_code='.$tipo_code.'&bar='.$bar; ?>" /> <br /> 
	<div class="right">
		<input type="button" name="imprimir" value="Imprimir" onclick="window.print();" class="button">
		<a href="<?php echo $this->url->link('common/home')?>">Click aca para ir a la pagina principal.</a>
	</div>
	<br />
</div>

<?php echo $footer; ?>