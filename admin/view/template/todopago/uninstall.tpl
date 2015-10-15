<?php echo $header; ?>
<div id="content">
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/payment.png" alt="" />Desinstalaci&oacute;n  TodoPago (<?php echo $todopago_version; ?>)</h1>
      <div class="buttons"><a id="continueButton" class="button"><?php echo $button_continue_text; ?></a></div>
    </div>
    <div class="content">
        <h3>Procederemos con la desinstalaci&oacute;n de la exteci&oacute;n de pago TodoPago.</h3>
        <p>Seleccione los campos :</p>
        <form id="form" action="<?php echo $button_continue_action; ?>" method="post" enctype="multipart/form-data" >
            <ul>
            <li><input type="checkbox" checked="true" id="check_todopago_transaccion" name="drop_table_todopago_transaccion">Eliminar tabla <em>todopago_transaccion</em>, esta lle va un registro de las transacciones hechas con el edio de pago en casoo de hacerlo, ya no podr&aacute; hacer un get status de las transacciones guardadas si reinstala el plugin (Lass transacciones se mantendr&aacute;n en el registro del commerce, seleccionelo si est&aacute; seguro de que no volvera a instalar el medio.</input></li>
            <li><input type="checkbox" checked="true" id="check_ostcode_required" name="revert_postcode_required">Se setear&aacute; el c&oacute;digo postal como no obolgatoria para Argentina. (Cuando se instal&oacute; la exensi&oacute;n se puso obolgatorio)</input></li>
            <li><input type="checkbox" checked="true" id="check_cs_code" name="drop_column_cs_code">Se eliminar&aacute; la columna agregada a la tabla de zonas que tiene el c&oacute;digo de las provincias argentinas necesario para el pago por este medio.</input></li>
            </ul>
        </form>
    </div>
</div>
<script type="text/javascript"><!--

    window.onkeydown= function(){

	return (event.keyCode != 116) && (event.keyCode == 154);
	}
	history.pushState(null, null, 'index.php?route=payment/todopago/confirmInstallation&token=8cddbffc58936e11a463b38a37cd8cf0');
    window.addEventListener('popstate', function(event) {
    history.pushState(null, null, 'index.php?route=payment/todopago/confirmInstallation&token=8cddbffc58936e11a463b38a37cd8cf0');
    });
    var clickpermitido = false;
    $('#continueButton').bind('click', function (e){
        clickpermitido = true;
        $('#form').submit();
    });
    $(window).bind('beforeunload', function(e){

        e = e || window.event;

        if (clickpermitido){
            return;
        }
        e.preventDefault();
        e.stopPropagation();
        return "<?php echo $back_button_message; ?>";
    });
//--></script>
<?php echo $footer; ?>
