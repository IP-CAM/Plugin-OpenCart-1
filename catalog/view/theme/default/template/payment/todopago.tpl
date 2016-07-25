
<?php $url_second_step =  $this->config->get('config_url')."index.php?route=payment/todopago/second_step_todopago&Order=".$order_id; ?>
<?php if ($this->config->get("todopago_modotestproduccion") == "Test") { ?>
	<script type="text/javascript" src="https://developers.todopago.com.ar/resources/TPHybridForm-v0.1.js"></script>
<?php } else { ?>
	<script type="text/javascript" src="https://forms.todopago.com.ar/resources/TPHybridForm-v0.1.js"></script>
<?php } ?>
<div class="buttons" id="confirmar_pago_view">
	<div class="center">
		<img src="catalog/view/theme/default/image/todopago.jpg" />	<br />
		<input type="button" id="confirmar_pago" onclick="init_my_form()" value="Confirmar Pago" class="button" />
	</div>
</div>

<script type="text/javascript">

	function init_my_form(){
		console.log("<?php echo $action ?>"+"&order_id="+"<?php echo $order_id?>");
        $("#confirmar_pago").prop('disabled', true);
		$.get("<?php echo $action ?>"+"&order_id="+"<?php echo $order_id?>", function(data) {
			data_json = JSON.parse(data);
			console.log(data_json.PublicRequestKey);
			setTimeout(todopago_init_form(data_json.PublicRequestKey), 1000);
			$("#formualrio_hibrido").show();
			$("#confirmar_pago_view").hide();
		});
	}
</script>

<div id="formualrio_hibrido" class="checkout-product" hidden>
	<table>
		<thead>
			<tr>
				<td><div id="tp-logo"></div></td>
				<td><span class="tp-label">Elegí tu forma de pago </span></td>
			</tr>
		</thead>

		<tbody>

			<tr>
				<td>
					<div>
						<select id="formaDePagoCbx"></select>	
					</div>
				</td>
				<td>
					<div>
						<select id="bancoCbx"></select>
					</div>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<div>
						<select id="promosCbx"></select>
						<label id="labelPromotionTextId" class="left"></label>
					</div>
				</td>
			</tr>

			<tr>
				<td>
					<div>
						<input id="numeroTarjetaTxt" class="left" />
					</div>
				</td>
				<td>
					<div>
						<input id="codigoSeguridadTxt" />
						<label id="labelCodSegTextId" class="left"></label>
					</div>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<input id="mesTxt" >
				/
					<input id="anioTxt">
				</td>
			</tr>


			<tr>
				<td colspan="2">
					<div>
						<input id="apynTxt" class="left" />
					</div>
				</td>
			</tr>
				
			<tr>
				<td>
					
						<select id="tipoDocCbx"></select>
					
				</td>
				<td>
						<input id="nroDocTxt"/>					
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<div>
						<input id="emailTxt" class="left" /><br/>
					</div>
				</td>
			</tr>

		</tbody>
		<tfoot>
		<tr>
		<td colspan="2">
				<input type="buttton" id="MY_btnConfirmarPago" class="button" value="Pagar"/>
		</td>
		</tr>
		</tfoot>

	</table>
</div>

<script>

	var orderid = '<?php echo $order_id; ?>';


		//securityRequesKey, esta se obtiene de la respuesta del SAR
		
		var mail = "";
		var completeName = "";
		var dni = '';
		var defDniType = 'DNI'

		/************* CONFIGURACION DEL API ************************/
		function todopago_init_form(security)
		{
			if(window.TPFORMAPI!=undefined){
				window.TPFORMAPI.hybridForm.initForm({
					callbackValidationErrorFunction: 'validationCollector',
					callbackCustomSuccessFunction: 'customPaymentSuccessResponse',
					callbackCustomErrorFunction: 'customPaymentErrorResponse',
					botonPagarId: 'MY_btnConfirmarPago',
					botonPagarConBilleteraId: 'MY_btnPagarConBilletera',
					modalCssClass: 'modal-class',
					modalContentCssClass: 'modal-content',
					beforeRequest: 'initLoading',
					afterRequest: 'stopLoading'
				});
                window.TPFORMAPI.hybridForm.setItem({
					publicKey: security,
					defaultNombreApellido: completeName,
					defaultNumeroDoc: dni,
					defaultMail: mail,
					defaultTipoDoc: defDniType
				});
            }else{
				setInterval(function(){todopago_init_form(); }, 1000);

			}
		}
		
		//callbacks de respuesta del pago
		function validationCollector(parametros) {
			console.log("My validator collector");
			console.log(parametros.field + " ==> " + parametros.error);
			console.log(parametros);
		}
		function customPaymentSuccessResponse(response) {
			console.log("My custom payment success callback");
			console.log(response.ResultCode + " : " + response.ResultMessage);
			console.log(response);
			console.log("<?php echo $url_second_step ?>&Answer="+response.AuthorizationKey);

			window.location.href = "<?php echo $url_second_step ?>&Answer="+response.AuthorizationKey;
		}
		
		function customPaymentErrorResponse(response) {
			console.log("Mi custom payment error callback");
			console.log(response.ResultCode + " : " + response.ResultMessage);
			console.log(response);

			console.log("<?php echo $url_second_step ?>&Answer="+response.AuthorizationKey);

			window.location.href = "<?php echo $url_second_step ?>&Answer="+response.AuthorizationKey;
		}
		function initLoading() {
			console.log('Cargando');
		}
		function stopLoading() {
			console.log('Stop loading...');
		} 	


	</script>

	<style type="text/css">

		#tp-logo{
			background-image: url("https://portal.todopago.com.ar/app/images/logo.png");
			background-repeat: no-repeat;
			height:40px;
			width:110px;
			margin: 0 0 0 14px;
		}
	</style>