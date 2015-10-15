<?php echo $header; ?>
<div id="content">
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/payment.png" alt="" />Instalaci&oacute;n  TodoPago (<?php echo $todopago_version; ?>)</h1>
      <div class="buttons"><a href="<?php echo $button_install_action; ?>" class="button"><?php echo $button_install_text; ?></a><a href="<?php echo $button_cancel_action; ?>" class="button"><?php echo $button_cancel_text; ?></a></div>
    </div>
    <div class="content">
        <h3>Bienvenido a la instalaci&oacute;n de la exteci&oacute;n de pago TodoPago.</h3>
        <p>Se le informa que se rrealizar&aacute;n los siguientes cambios:</p>
        <ul>
            <li>Se agregar&aacute; una tabla <em>todopago_transaccion</em> a su base de datos la cu&aacute;l guardar&aacute; informaci&oacute;n sobre las transacciones realizadas por el medio de pago.</li>
            <li>Se setear&aacute; el c&oacute;digo postal obligatoria para Argentina. (Esto se debe a que por cuestones de seguridad aquellos clientes que no tengan c&oacute;digo postal no podr&aacute;n pagar con este medio.</li>
            <li>Se agregar&aacute; una nueva culumna a la tabla de zonas asignando un nuevo c&oacute;digo a las provincias argentinas necesario para el pago.</li>
        </ul>
    </div>
</div>
<script type="text/javascript"><!--

    window.onkeydown= function(){

	return (event.keyCode != 116) && (event.keyCode == 154);
	}
	history.pushState(null, null, 'index.php?route=payment/todopago/confirmInstallation&token=8cddbffc58936e11a463b38a37cd8cf0');
    window.addEventListener('popstate', function(event) {
    history.pushState(null, null, 'index.php?route=payment/todopago/configUninstallation&token=8cddbffc58936e11a463b38a37cd8cf0');
    });

    var clickpermitido = false;
    $(window).bind('click', function (e){
        if (e.target.nodeName.toLowerCase() == 'a'){
            clickpermitido = true;
        }
    });
    $(window).bind('beforeunload', function(e){

        e = e || window.event;

        if (clickpermitido){
            return;
        }
        e.preventDefault();
        e.stopPropagation();
        $.post("<?php echo $back_button_action; ?>", function(data){});
        return "<?php echo $back_button_message; ?>";
    });
//--></script>
<?php echo $footer; ?>
