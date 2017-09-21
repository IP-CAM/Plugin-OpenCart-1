<link href="catalog/view/theme/default/template/todopago/todopago_form.css" rel="stylesheet">
<?php if (is_null($rta_server)) { ?>
<script>window.location.href = "<?php echo $url_error; ?>"</script>
<?php } elseif  ($rta_server->StatusCode != -1) { ?>
<script>window.location.href = "<?php echo $url_second_step.'&Order='.$order_id.'&Error=' ?>" + "Falló la carga del formulario."</script>
<?php } ?>
<!-- inicio formulario -->
<div class="progress">
    <div class="progress-bar progress-bar-striped active" id="loading-hibrid">
    </div>
</div>
<div class="formuHibrido container-fluid" id="tpForm">
    <!-- row 0 -->
    <div class="row">
        <div class="bloque bloque-medium">
            <img src="https://portal.todopago.com.ar/app/images/logo.png" alt="todopago" id="todopago_logo">
        </div>
    </div>

    <!-- row 1 -->
    <div class="row">
        <div class="left-col">
            <div class="bloque bloque-medium float-left ">
                <select id="formaPagoCbx" class="input select"></select>
            </div>
            <div class="loaded-form bloque bloque-medium float-left">
                <input id="numeroTarjetaTxt" class="input">
                <label id="numeroTarjetaLbl" for="numeroTarjetaTxt" class="advertencia"></label>
            </div>
        </div>
        <div class="right-col float-right pei loaded-form">
            <input id="nombreTxt" class="input bloque bloque-medium float-right loaded-form">
        </div>
    </div>

    <!-- row 2 -->
    <div class="loaded-form">
        <div class="row" id="row-pei">
            <div class="left-col float-left" id="pei-col">
                <input id="peiCbx" class=" bloque bloque-medium float-right pei-input"><label id="peiLbl"
                                                                                                    for="peiCbx"></label>
            </div>
        </div>
        <div class="row">
            <div class="left-col">
                <div class="bloque bloque-medium float-left">
                    <select id="medioPagoCbx" class="input "></select>
                </div>
                <div class="bloque bloque-medium float-left">
                    <select id="bancoCbx" class="input "></select>
                </div>
            </div>
            <div class="right-col">
                <div class="bloque bloque-small float-right">
                    <select id="tipoDocCbx" class="input "></select>
                </div>
                <div class="bloque bloque-big float-right">
                    <input id="nroDocTxt" class="input button-big ">
                </div>
            </div>
        </div>
        <!-- row 3 -->
        <div class="row">
            <div class="left-col">
                <div class="bloque bloque-small float-left">
                    <select id="mesCbx" class="input "></select>
                </div>
                <div class="bloque bloque-small float-left">
                    <select id="anioCbx" class="input "></select>
                    <label id="fechaLbl" class="advertencia "></label>
                </div>
                <div class="bloque bloque-small float-left">
                    <input id="codigoSeguridadTxt" class="input ">
                    <label id="codigoSeguridadLbl" for="codigoSeguridadTxt" class="advertencia "></label>
                </div>
            </div>
            <div class="right-col">
                <div class="bloque bloque-full float-right">
                    <input id="emailTxt" class="input ">
                </div>
            </div>
        </div>
        <!-- row 4 -->
        <div class="row">
            <div class="left-col">
                <div class="bloque bloque-big float-left">
                    <select id="promosCbx" class="input"></select>
                </div>
            </div>
            <div class="right-col">
                <div class="bloque bloque-small float-right">
                    <label id="promosLbl" for="promosCbx" class=""></label>
                </div>

                <div class="bloque bloque-small float-right">
                    <input id="tokenPeiTxt" class="input">
                    <label id="tokenPeiLbl" for="tokenPeiTxt" class="advertencia "></label>
                </div>
            </div>
        </div>
    </div>
    <!-- row 5 -->
    <div class="row" id="row-buttons">
        <button id="MY_buttonPagarConBilletera"
                class="button button-payment-method button-primary float-right"></button>
        <button id="MY_buttonConfirmarPago"
                class="button button-payment-method button-primary float-right  loaded-form"></button>
    </div>
</div>


<script type="text/javascript">
    /************* CONFIGURACION DEL API *********************/

    function loadScript(url, callback) {
        var script = document.createElement("script");
        script.type = "text/javascript";
        if (script.readyState) {  //IE
            script.onreadystatechange = function () {
                if (script.readyState === "loaded" || script.readyState === "complete") {
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {  //et al.
            script.onload = function () {
                callback();
            };
            script.onerror = function () {
                window.location.href = "<?php echo $url_second_step.'&Order='.$order_id.'&Error=' ?>" + "Falló la carga del formulario.";
            }
        }
        script.src = url;
        document.getElementsByTagName("head")[0].appendChild(script);
    }

    loadScript('<?php echo $validacionJS ?>', function () {
        loader();
    });

    function loader() {
        $("#loading-hibrid").css("width", "50%");
        setTimeout(function () {
            ignite();
        }, 100);
        setTimeout(function () {
            $("#loading-hibrid").css("width", "100%");
        }, 1000);
        setTimeout(function () {
            $(".progress").hide('fast');
        }, 1500);
        setTimeout(function () {
            $("#tpForm").show('fast');
        }, 1600);
    }

    var formaDePago = document.getElementById("formaPagoCbx");

    $("#formaPagoCbx").click(function() {
        if (formaDePago.value === "1") {
            $(".loaded-form").show('fast');
        }
    });

    function ignite() {
        window.TPFORMAPI.hybridForm.initForm({
            callbackValidationErrorFunction: 'validationCollector',
            callbackBilleteraFunction: 'billeteraPaymentResponse',
            callbackCustomSuccessFunction: 'customPaymentSuccessResponse',
            callbackCustomErrorFunction: 'customPaymentErrorResponse',
            botonPagarId: 'MY_buttonConfirmarPago',
            botonPagarConBilleteraId: 'MY_buttonPagarConBilletera',
            modalCssClass: 'modal-class',
            modalContentCssClass: 'modal-content',
            beforeRequest: 'initLoading',
            afterRequest: 'stopLoading'
        });
        /************* SETEO UN ITEM PARA COMPRAR ******************/
        window.TPFORMAPI.hybridForm.setItem({
            publicKey: '<?php echo $rta_server->PublicRequestKey; ?>',
            defaultNombreApellido: '<?php echo $completeName; ?>',
            defaultNumeroDoc: '',
            defaultMail: '<?php echo $mail; ?>',
            defaultTipoDoc: 'DNI'
        });
    }

    /************ FUNCIONES CALLBACKS ************/

    function validationCollector(parametros) {
        console.log("Validando los datos");
        console.log(parametros.field + " -> " + parametros.error);
        var input = parametros.field;
        if (input.search("Txt") !== -1) {
            label = input.replace("Txt", "Lbl");
        } else {
            label = input.replace("Cbx", "Lbl");
        }
        if (document.getElementById(label) !== null) {
            document.getElementById(label).innerHTML = parametros.error;
        }
    }

    function billeteraPaymentResponse(response) {
        console.log("Iniciando billetera");
        console.log(response.ResultCode + " -> " + response.ResultMessage);
        if (response.AuthorizationKey) {
            window.location.href = "<?php echo $url_second_step.'&Order='.$order_id.'&Answer=' ?>" + response.AuthorizationKey;
        } else {
            window.location.href = "<?php echo $url_second_step.'&Order='.$order_id.'&Error=' ?>" + response.ResultMessage;
        }
    }

    function customPaymentSuccessResponse(response) {
        console.log("Success");
        console.log(response.ResultCode + " -> " + response.ResultMessage);
        window.location.href = "<?php echo $url_second_step.'&Order='.$order_id.'&Answer=' ?>" + response.AuthorizationKey;
    }

    function customPaymentErrorResponse(response) {
        console.log(response.ResultCode + " -> " + response.ResultMessage);
        if (response.AuthorizationKey) {
            window.location.href = "<?php echo $url_second_step.'&Order='.$order_id.'&Answer=' ?>" + response.AuthorizationKey;
        } else {
            window.location.href = "<?php echo $url_second_step.'&Order='.$order_id.'&Error=' ?>" + response.ResultMessage;
        }
    }

    function initLoading() {
        console.log('Loading...');
    }

    function stopLoading() {
        console.log('Stop loading...');
        var peiCbx = $("#peiCbx");
        var rowPei = $("#row-pei");
        if (peiCbx.css('display') !== 'none') {
            rowPei.show('fast');
        } else {
            rowPei.css("display", "none");
        }
    }


</script>
