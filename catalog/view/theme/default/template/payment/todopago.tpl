

<form action="<?php echo $action; ?>" method="post">
	<input type="hidden" name="order_id" value="<?php echo $order_id ?>"/> 
	
	<div class="buttons">
		<div class="center">
	<img src="catalog/view/theme/default/image/todopago.jpg" />	<br />
	<input type="submit" id="boton_pago" value="Confirmar Pago" class="button" />
		</div>
	</div>
</form>


<script>
/*var orderid = '<?php echo $order_id; ?>';
var x;
x=$(document);
x.ready(inicializarEventos);

function inicializarEventos()
{
	$('#validando').hide();
	var x;
	x=$("#boton_pago");
	x.click(presionBoton)
}

function presionBoton()
{
	$("#boton_pago").hide();
	$('#validando').show();
	$.get("http://localhost:8888/opencart-1.5.6.4/upload/index.php?route=payment/todopago/first_step_todopago",{order_id:orderid},llegadaDatos);
}

function llegadaDatos(rta)
{
  rta_json = JSON.parse(rta);
  //window.location.replace("http://stackoverflow.com");

}*/
</script>


