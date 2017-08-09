<script>
!function(t,o){"function"==typeof define&&define.amd?define(o):"object"==typeof exports?module.exports=o():t.tingle=o()}(this,function(){function t(t){var o={onClose:null,onOpen:null,beforeClose:null,stickyFooter:!1,footer:!1,cssClass:[],closeLabel:"Close",closeMethods:["overlay","button","escape"]};this.opts=h({},o,t),this.init()}function o(){this.modal.classList.contains("tingle-modal--visible")&&(this.isOverflow()?this.modal.classList.add("tingle-modal--overflow"):this.modal.classList.remove("tingle-modal--overflow"),!this.isOverflow()&&this.opts.stickyFooter?this.setStickyFooter(!1):this.isOverflow()&&this.opts.stickyFooter&&(e.call(this),this.setStickyFooter(!0)))}function e(){this.modalBoxFooter&&(this.modalBoxFooter.style.width=this.modalBox.clientWidth+"px",this.modalBoxFooter.style.left=this.modalBox.offsetLeft+"px")}function s(){this.modal=document.createElement("div"),this.modal.classList.add("tingle-modal"),0!==this.opts.closeMethods.length&&this.opts.closeMethods.indexOf("overlay")!==-1||this.modal.classList.add("tingle-modal--noOverlayClose"),this.modal.style.display="none",this.opts.cssClass.forEach(function(t){"string"==typeof t&&this.modal.classList.add(t)},this),this.opts.closeMethods.indexOf("button")!==-1&&(this.modalCloseBtn=document.createElement("button"),this.modalCloseBtn.classList.add("tingle-modal__close"),this.modalCloseBtnIcon=document.createElement("span"),this.modalCloseBtnIcon.classList.add("tingle-modal__closeIcon"),this.modalCloseBtnIcon.innerHTML="×",this.modalCloseBtnLabel=document.createElement("span"),this.modalCloseBtnLabel.classList.add("tingle-modal__closeLabel"),this.modalCloseBtnLabel.innerHTML=this.opts.closeLabel,this.modalCloseBtn.appendChild(this.modalCloseBtnIcon),this.modalCloseBtn.appendChild(this.modalCloseBtnLabel)),this.modalBox=document.createElement("div"),this.modalBox.classList.add("tingle-modal-box"),this.modalBoxContent=document.createElement("div"),this.modalBoxContent.classList.add("tingle-modal-box__content"),this.modalBox.appendChild(this.modalBoxContent),this.opts.closeMethods.indexOf("button")!==-1&&this.modal.appendChild(this.modalCloseBtn),this.modal.appendChild(this.modalBox)}function i(){this.modalBoxFooter=document.createElement("div"),this.modalBoxFooter.classList.add("tingle-modal-box__footer"),this.modalBox.appendChild(this.modalBoxFooter)}function n(){this._events={clickCloseBtn:this.close.bind(this),clickOverlay:d.bind(this),resize:o.bind(this),keyboardNav:l.bind(this)},this.opts.closeMethods.indexOf("button")!==-1&&this.modalCloseBtn.addEventListener("click",this._events.clickCloseBtn),this.modal.addEventListener("mousedown",this._events.clickOverlay),window.addEventListener("resize",this._events.resize),document.addEventListener("keydown",this._events.keyboardNav)}function l(t){this.opts.closeMethods.indexOf("escape")!==-1&&27===t.which&&this.isOpen()&&this.close()}function d(t){this.opts.closeMethods.indexOf("overlay")!==-1&&!a(t.target,"tingle-modal")&&t.clientX<this.modal.clientWidth&&this.close()}function a(t,o){for(;(t=t.parentElement)&&!t.classList.contains(o););return t}function r(){this.opts.closeMethods.indexOf("button")!==-1&&this.modalCloseBtn.removeEventListener("click",this._events.clickCloseBtn),this.modal.removeEventListener("mousedown",this._events.clickOverlay),window.removeEventListener("resize",this._events.resize),document.removeEventListener("keydown",this._events.keyboardNav)}function h(){for(var t=1;t<arguments.length;t++)for(var o in arguments[t])arguments[t].hasOwnProperty(o)&&(arguments[0][o]=arguments[t][o]);return arguments[0]}function c(){var t,o=document.createElement("tingle-test-transition"),e={transition:"transitionend",OTransition:"oTransitionEnd",MozTransition:"transitionend",WebkitTransition:"webkitTransitionEnd"};for(t in e)if(void 0!==o.style[t])return e[t]}var m=c();return t.prototype.init=function(){this.modal||(s.call(this),n.call(this),document.body.insertBefore(this.modal,document.body.firstChild),this.opts.footer&&this.addFooter())},t.prototype.destroy=function(){null!==this.modal&&(r.call(this),this.modal.parentNode.removeChild(this.modal),this.modal=null)},t.prototype.open=function(){this.modal.style.removeProperty?this.modal.style.removeProperty("display"):this.modal.style.removeAttribute("display"),document.body.classList.add("tingle-enabled"),this.setStickyFooter(this.opts.stickyFooter),this.modal.classList.add("tingle-modal--visible");var t=this;m?this.modal.addEventListener(m,function o(){"function"==typeof t.opts.onOpen&&t.opts.onOpen.call(t),t.modal.removeEventListener(m,o,!1)},!1):"function"==typeof t.opts.onOpen&&t.opts.onOpen.call(t),o.call(this)},t.prototype.isOpen=function(){return!!this.modal.classList.contains("tingle-modal--visible")},t.prototype.close=function(){if("function"==typeof this.opts.beforeClose){var t=this.opts.beforeClose.call(this);if(!t)return}document.body.classList.remove("tingle-enabled"),this.modal.classList.remove("tingle-modal--visible");var o=this;m?this.modal.addEventListener(m,function t(){o.modal.removeEventListener(m,t,!1),o.modal.style.display="none","function"==typeof o.opts.onClose&&o.opts.onClose.call(this)},!1):(o.modal.style.display="none","function"==typeof o.opts.onClose&&o.opts.onClose.call(this))},t.prototype.setContent=function(t){"string"==typeof t?this.modalBoxContent.innerHTML=t:(this.modalBoxContent.innerHTML="",this.modalBoxContent.appendChild(t))},t.prototype.getContent=function(){return this.modalBoxContent},t.prototype.addFooter=function(){i.call(this)},t.prototype.setFooterContent=function(t){this.modalBoxFooter.innerHTML=t},t.prototype.getFooterContent=function(){return this.modalBoxFooter},t.prototype.setStickyFooter=function(t){this.isOverflow()||(t=!1),t?this.modalBox.contains(this.modalBoxFooter)&&(this.modalBox.removeChild(this.modalBoxFooter),this.modal.appendChild(this.modalBoxFooter),this.modalBoxFooter.classList.add("tingle-modal-box__footer--sticky"),e.call(this),this.modalBoxContent.style["padding-bottom"]=this.modalBoxFooter.clientHeight+20+"px"):this.modalBoxFooter&&(this.modalBox.contains(this.modalBoxFooter)||(this.modal.removeChild(this.modalBoxFooter),this.modalBox.appendChild(this.modalBoxFooter),this.modalBoxFooter.style.width="auto",this.modalBoxFooter.style.left="",this.modalBoxContent.style["padding-bottom"]="",this.modalBoxFooter.classList.remove("tingle-modal-box__footer--sticky")))},t.prototype.addFooterBtn=function(t,o,e){var s=document.createElement("button");return s.innerHTML=t,s.addEventListener("click",e),"string"==typeof o&&o.length&&o.split(" ").forEach(function(t){s.classList.add(t)}),this.modalBoxFooter.appendChild(s),s},t.prototype.resize=function(){console.warn("Resize is deprecated and will be removed in version 1.0")},t.prototype.isOverflow=function(){var t=window.innerHeight,o=this.modalBox.clientHeight;return o>=t},{modal:t}});
</script>
<style>
.tingle-modal *{box-sizing:border-box}.tingle-modal{position:fixed;top:0;right:0;bottom:0;left:0;z-index:1000;display:-webkit-box;display:-ms-flexbox;display:flex;visibility:hidden;-webkit-box-orient:vertical;-webkit-box-direction:normal;-ms-flex-direction:column;flex-direction:column;-webkit-box-align:center;-ms-flex-align:center;align-items:center;overflow:hidden;background:rgba(0,0,0,.8);opacity:0;cursor:pointer;-webkit-transition:-webkit-transform .2s ease;transition:-webkit-transform .2s ease;transition:transform .2s ease;transition:transform .2s ease,-webkit-transform .2s ease}.tingle-modal--noClose .tingle-modal__close,.tingle-modal__closeLabel{display:none}.tingle-modal--confirm .tingle-modal-box{text-align:center}.tingle-modal--noOverlayClose{cursor:default}.tingle-modal__close{position:fixed;top:10px;right:28px;z-index:1000;padding:0;width:5rem;height:5rem;border:none;background-color:transparent;color:#f0f0f0;font-size:6rem;font-family:monospace;line-height:1;cursor:pointer;-webkit-transition:color .3s ease;transition:color .3s ease}.tingle-modal__close:hover{color:#fff}.tingle-modal-box{position:relative;-ms-flex-negative:0;flex-shrink:0;margin-top:auto;margin-bottom:auto;width:60%;border-radius:4px;background:#fff;opacity:1;cursor:auto;-webkit-transition:-webkit-transform .3s cubic-bezier(.175,.885,.32,1.275);transition:-webkit-transform .3s cubic-bezier(.175,.885,.32,1.275);transition:transform .3s cubic-bezier(.175,.885,.32,1.275);transition:transform .3s cubic-bezier(.175,.885,.32,1.275),-webkit-transform .3s cubic-bezier(.175,.885,.32,1.275);-webkit-transform:scale(.8);-ms-transform:scale(.8);transform:scale(.8)}.tingle-modal-box__content{padding:3rem}.tingle-modal-box__footer{padding:1.5rem 2rem;width:auto;border-bottom-right-radius:4px;border-bottom-left-radius:4px;background-color:#f5f5f5;cursor:auto}.tingle-modal-box__footer::after{display:table;clear:both;content:""}.tingle-modal-box__footer--sticky{position:fixed;bottom:-200px;z-index:10001;opacity:1;-webkit-transition:bottom .3s ease-in-out .3s;transition:bottom .3s ease-in-out .3s}.tingle-enabled{overflow:hidden;height:100%}.tingle-modal--visible .tingle-modal-box__footer{bottom:0}.tingle-enabled .tingle-content-wrapper{-webkit-filter:blur(15px);filter:blur(15px)}.tingle-modal--visible{visibility:visible;opacity:1}.tingle-modal--visible .tingle-modal-box{-webkit-transform:scale(1);-ms-transform:scale(1);transform:scale(1)}.tingle-modal--overflow{overflow-y:scroll;padding-top:8vh}.tingle-btn{display:inline-block;margin:0 .5rem;padding:1rem 2rem;border:none;background-color:grey;box-shadow:none;color:#fff;vertical-align:middle;text-decoration:none;font-size:inherit;font-family:inherit;line-height:normal;cursor:pointer;-webkit-transition:background-color .4s ease;transition:background-color .4s ease}.tingle-btn--primary{background-color:#3498db}.tingle-btn--danger{background-color:#e74c3c}.tingle-btn--default{background-color:#34495e}.tingle-btn--pull-left{float:left}.tingle-btn--pull-right{float:right}@media (max-width :540px){.tingle-modal-box{width:auto;border-radius:0}.tingle-modal{top:60px;display:block;width:100%}.tingle-modal--noClose{top:0}.tingle-modal--overflow{padding:0}.tingle-modal-box__footer .tingle-btn{display:block;float:none;margin-bottom:1rem;width:100%}.tingle-modal__close{top:0;right:0;left:0;display:block;width:100%;height:60px;border:none;background-color:#2c3e50;box-shadow:none;color:#fff;line-height:55px}.tingle-modal__closeLabel{display:inline-block;vertical-align:middle;font-size:1.5rem;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen,Ubuntu,Cantarell,"Fira Sans","Droid Sans","Helvetica Neue",sans-serif}.tingle-modal__closeIcon{display:inline-block;margin-right:.5rem;vertical-align:middle;font-size:4rem}}
</style>

<?php echo $header; ?>
<div id="content">
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/payment.png" alt="" />Todo Pago (<?php echo $todopago_version; ?>)</h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
     <?php if ($need_upgrade) { ?>
     <p class="important-message">Usted ha subido una nueva versión del m&oacute;dulo, para su correcto funcionamiento debe actualizarlo haciendo click en el botón "Upgrade"</p>
     <?php } ?>
     <div id="htabs" class="htabs">
      <a href="#tab-general">GENERAL</a>
      <a href="#tab-test">AMBIENTE DEVELOPERS</a>
      <a href="#tab-produccion">AMBIENTE PRODUCCI&Oacute;N</a>
      <a href="#tab-estadosdelpedido">ESTADOS DEL PEDIDO</a>
      <a href="#tab-status">Status de las Operaci&oacute;n</a>
    </div>
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
     <input type="hidden" name="upgrade" value="<?php echo $need_upgrade ?>">
     <input type="hidden" name="todopago_version" value="<?php echo $installed_version; ?>">
     <!-- TAB GENERAL -->
     <div id="tab-general">
      <table class="form">
        <tr>         
      <td>Enabled</td>
          <td>            <select class="form-control" name="todopago_status" id="todopago_status">
                                        <?php if ($todopago_status == 1) { ?>
                                        <option value="1" selected="selected">
                                            <?php echo $text_enabled; ?>
                                        </option>
                                        <option >
                                            <?php echo $text_disabled; ?>
                                        </option>
                                        <?php } else { ?>
                                        <option value="1">
                                            <?php echo $text_enabled; ?>
                                        </option>
                                        <option value="0" selected="selected">
                                            <?php echo $text_disabled; ?>
                                        </option>
                                        <?php } ?>
                                    </select></td><td><em>Activa y desactiva el módulo de pago</em></td>
        </tr>
        <tr>
          <td>Segmento del Comercio</td>
          <td>
            <select name="todopago_segmentodelcomercio">
              <option value="Retail" <?php if ($todopago_segmentodelcomercio=="Retail") echo "selected"?> >Retail</option>
                  <!--<option value="Ticketing" <?php if ($todopago_segmentodelcomercio=="Ticketing") echo "selected"?> >Ticketing</option>
                  <option value="Services" <?php if ($todopago_segmentodelcomercio=="Services") echo "selected"?> >Service</option>
                  <option value="Digital Goods" <?php if ($todopago_segmentodelcomercio=="Digital Goods") echo "selected"?> >Digital Goods</option>
                </select>-->
              </td>
              <td><em>La elección del segmento determina los tipos de datos a enviar</em></td>
            </tr>
            <!--<tr>
              <td>Canal de Ingreso del Pedido</td>
              <td>
                <select name="todopago_canaldeingresodelpedido">
                  <option value="Web" <?php /* if ($canaldeingresodelpedido=="Web") echo "selected" +
                  ?>>Web</option>
                  <option value="Mobile" <?php if ($todopago_canaldeingresodelpedido=="Mobile") echo "selected" ?>>Mobile</option>
                  <option value="Telefonica" <?php if ($todopago_canaldeingresodelpedido=="Telefonica") echo "selected" */?>>Telefonica</option>
                </select>
              </td>
              <td><em></em></td>
            </tr>-->
            <tr>
              <td>Dead Line</td>
              <td><input type="number" name="todopago_deadline" min=0 value="<?php echo $todopago_deadline; ?>"/></td>
              <td><em>d&iacute;as m&aacute;ximos para la entrega</em></td>
            </tr>
            <tr>
              <td>Tipo de Formulario</td>
              <td>
                <select name="todopago_form">
                  <option value="hibrid" <?php if ($todopago_form=="hibrid") echo "selected" ?>>Hibrido</option>
                  <option value="redirect" <?php if ($todopago_form=="redirect") echo "selected" ?>>Redirección</option>
                </select>
              </td>
              <td><em>Tipo de Formulario de Pagos</em></td>
            </tr>  
            <tr>
              <td>Modo Developers o Producci&oacute;n</td>
              <td>
                <select name="todopago_modotestproduccion">
                  <option value="Test" <?php if ($todopago_modotestproduccion=="Test") echo "selected" ?>>Developers</option>
                  <option value="Produccion" <?php if ($todopago_modotestproduccion=="Produccion") echo "selected" ?>>producci&oacute;n</option>
                </select>
              </td>
              <td><em>Debe ser cofigurado en CONFIGURACI&Oacute;N - AMBIENTE DEVELOPERS / PRODUCCION</em></td>
            </tr>  
                    <tr> 
                <?php
                $checked = $this->config->get('todopago_maxinstallments')
                ?>
  
                         <td>Máximo de cuotas
                       <?php    if (isset($checked)){ ?>
                             
                                   <label><input type="checkbox" id="habilitar" value="" checked="checked"> Habilitar</label>
                             <?php }else {?>
                              <label><input type="checkbox" id="habilitar" value=""> Habilitar</label>
                              <?php } ?>
                    </td>
                                <td class="field col-sm-4">
                                    <select name="todopago_maxinstallments" id="todopago_maxinstallments" disabled>
                        <?php  
                    for ($i = 0; $i <= 12; $i++) {
              
                                 ?>       <option value="<?php echo $i ?>"><?php echo $i ?></option> <?php

                                    if ($i == $this->config->get('todopago_maxinstallments')) {
?><script> $("select option[value=<?php echo $i ?>]").attr("selected","selected"); </script><?php
                                            }


                   }
                    ?>                   
                                    </select>
                                </td>
                                <td class="info-field col-sm-5"><em>* Seleccione la cantidad máxmia de cuotas</em>
                                </td>
                            </tr>

			<tr>
			
				
				<td>Tiempo de expiración del formulario</td>
				<td>
					<select name="todopago_expiracion_formulario">
	                  <option <?php if($todopago_expiracion_formulario=="si"){ echo "selected"; } ?> value="si">SI</option>
	                  <option <?php if($todopago_expiracion_formulario=="no"){ echo "selected"; } ?> value="no">NO</option>
	                </select>
                </td>
				<td>Configurar tiempo de expiración del formulario de pago personalizado</td>
				
			
			</tr>
			
			<tr>
			
				
				<td>Tiempo de expiración del formulario de pago</td>
				<td>
					<input type="number" min="300000" max="1800000" name="todopago_tiempo_expiracion_formulario" value="<?php echo $todopago_tiempo_expiracion_formulario; ?>" />
  <?php if (!empty($error_code)) { ?>
  <div class="error"><?php echo $error_code; ?></div>
  <?php } ?>

                </td>
				<td>Tiempo maximo en el que se puede realizar el pago en el
				 formulario en milisegundos. Por defecto si no se envia el valor es 
				 de 1800000 (30 minutos)
				</td>
				
			
			</tr>
			
			<!----------------         Vaciar carrito        ---------------------->
			<tr>
				
				<td>Vaciar carrito de compras</td>
				<td>
					<select class="form-control" name="todopago_cart" id="todopago_cart">
	                    <?php if ($todopago_cart) { ?>
	                        <option value="1" selected="selected">
	                            <?php echo $text_enabled; ?>
	                        </option>
	                        <option value="0">
	                            <?php echo $text_disabled; ?>
	                        </option>
	                    <?php } else { ?>
	                        <option value="1">
	                            <?php echo $text_enabled; ?>
	                        </option>
	                        <option value="0" selected="selected">
	                            <?php echo $text_disabled; ?>
	                        </option>
	                    <?php } ?>
                    </select>
                </td>
				<td>¿Desea vaciar el carrito de compras luego de una operación fallida?</td>
	
			</tr>


          </table> 
        </div>

        <!-- END TAB GENERAL-->

        <!-- TAB AMBIENTE TEST -->
        <div id="tab-test">
          <table class="form">
            <tr>
              <td>Authorization HTTP</td>
              <td><input type="text" id="header_test" name="todopago_authorizationHTTPtest" value="<?php echo $todopago_authorizationHTTPtest; ?>" size="25" /></td>
              <td><em>Authorization o Api Key para el hearder. Ejemplo: <b>PRISMA 912EC803B2CE49E4A541068D12345678</b></em></td>
            </tr>
            <tr>
              <td>Id Site Todo Pago</td>
              <td><input type="text" id="site_id_test" name="todopago_idsitetest" value="<?php echo $todopago_idsitetest; ?>" /></td>
              <td><em>Número de Comercio provisto por Todo Pago</em></td>
            </tr>
            <tr>
              <td>Security code</td>
              <td><input type="text" id="security_test" name="todopago_securitytest" value="<?php echo $todopago_securitytest; ?>" /></td>
              <td><em>C&oacute;digo provisto por Todo Pago</em></td>
            </tr>
          </table>
          <div class="form-group required">
                            <div class="col-sm-2"></div>
                            <div class="field col-sm-4">
                                <button type="button" id="open" class="btn btn-primary">Requerir datos</button>
                         
                            </div>
                        </div>
        </div>
             <div id="popup" style="display: none;">
                            <div class="content-popup">

                                <div>
                                    <h2>Obtener credenciales <img src="http://www.todopago.com.ar/sites/todopago.com.ar/files/logo.png"></h2>

                                    <br />
                                    <label id="mail-label" class="control-label">E-mail</label>
                                    <input id="mail" class="form-control" name="mail" type="email" value="" placeholder="E-mail" />
                                    <br/>
                                    <br/>
                                    <label class="control-label">Contrase&ntilde;a</label>
                                    <input id="pass" class="form-control" name="pass" type="password" value="" placeholder="Contrase&ntilde;a" /> </br>
                                    <button id="confirm_test" style="margin-top:20%;" class="btn-config-credentials btn btn-primary">Acceder</button>
                                    <button id="cancel-test" style="margin-left:10%;" class="btn-config-credentials btn btn-danger" >Cancelar</button>
                                </div>
                            </div>
                        </div>
        <!-- END TAB AMBIENTE TEST -->
        
        <!-- TAB AMBIENTE PRODUCCION -->
        <div id="tab-produccion">
          <table class="form">
            <tr>
              <td>Authorization HTTP</td>
              <td><input type="text" id="header_prod" name="todopago_authorizationHTTPproduccion" value="<?php echo $todopago_authorizationHTTPproduccion; ?>" size="25" /></td>
              <td><em>Authorization o Api Key para el hearder. Ejemplo: <b>PRISMA 912EC803B2CE49E4A541068D12345678</b></em></td>
            </tr>
            <tr>
              <td>Id Site Todo Pago</td>
              <td><input type="text" id="site_id_prod" name="todopago_idsiteproduccion" value="<?php echo $todopago_idsiteproduccion; ?>" /></td>
              <td><em>Número de Comercio provisto por Todo Pago</em></td>
            </tr>
            <tr>
              <td>Security code</td>
              <td><input type="text" id="security_prod" name="todopago_securityproduccion" value="<?php echo $todopago_securityproduccion; ?>" /></td>
              <td><em>Código provisto por Todo Pago</em></td>
            </tr>
          </table>
          <div class="form-group required">
                            <div class="col-sm-2"></div>
                            <div class="field col-sm-4">
                                <button type="button" id="open_prod" class="btn btn-primary">Requerir datos</button>
                           
                            </div>
                        </div>
        </div>

           <div id="popup_prod" style="display: none;">
                            <div class="content-popup">

                                <div>
                                    <h2>Obtener credenciales <img src="http://www.todopago.com.ar/sites/todopago.com.ar/files/logo.png"></h2>

                                    <br />
                                    <label id="mail-label_prod" class="control-label">E-mail</label>
                                    <input id="mail_prod" class="form-control" name="mail" type="email" value="" placeholder="E-mail" />
                                    <br/>
                                    <br/>
                                    <label class="control-label">Contrase&ntilde;a</label>
                                    <input id="pass_prod" class="form-control" name="pass" type="password" value="" placeholder="Contrase&ntilde;a" />
                                    </br>
                                    <button id="confirm_prod" style="margin-top:20%;" class="btn-config-credentials btn btn-primary">Acceder</button>
                                    <button id="cancel-prod" style="margin:10%;" class="btn-config-credentials btn btn-danger">Cancelar</button>
                                </div>
                            </div>
                        </div>
        <!--END TAB AMBIENTE PRODUCCION -->
        
        <!-- TAB ESTADO DEL PEDIDO -->
        <div id="tab-estadosdelpedido">
          <table class="form">

            <tr>
              <td>Estado cuando la transacci&oacute;n ha sido iniciada</td>
              <td><select name="todopago_order_status_id_pro">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $todopago_order_status_id_pro) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            </tr>
            <tr>
              <td>Estado cuando la transacci&oacute;n ha sido aprobada</td>
              <td><select name="todopago_order_status_id_aprov">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $todopago_order_status_id_aprov) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            </tr>
            <tr>
              <td>Estado cuando la transacci&oacute;n ha sido Rechazada</td>
              <td><select name="todopago_order_status_id_rech">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $todopago_order_status_id_rech) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            </tr>
            <tr>
              <td>Estado cuando la transacci&oacute;n ha sido Offline</td>
              <td><select name="todopago_order_status_id_off">
                <?php foreach ($order_statuses as $order_status) { ?>
                <?php if ($order_status['order_status_id'] == $todopago_order_status_id_off) { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            </tr>
          </table>

        </div>
        <!-- END TAB ESTADO DEL PEDIDO -->

        <!-- TAB STATUS-->
        <div id="tab-status">
          <table class="form" border="1">

            <?php 
            $this->load->model('payment/todopago');
            $orders_array = $this->model_payment_todopago->get_orders();
            $orders_array = json_encode($orders_array->rows);
            ?>
            <script type="text/javascript">
              $(document).ready(function() {
                var valore = '<?php echo $orders_array ?>';
                console.log(valore);
                var tabla_db = '';
                valore_json = jQuery.parseJSON(valore);
                console.log(valore_json);
                jQuery.each(valore_json, function(key, value){
                  console.log(value);
                  tabla_db += "<tr>";
                  tabla_db +="<th><a onclick='verstatus("+value.order_id+")'>"+value.order_id+"</a></th>";
                  tabla_db +="<th>"+value.date_added+"</th>";
                  tabla_db +="<th>"+value.firstname+"</th>";
                  tabla_db +="<th>"+value.lastname+"</th>";
                  tabla_db +="<th>"+value.store_name+"</th>";
                  tabla_db +="<th>$"+value.total+"</th>";
                  tabla_db += "<th><a onclick='devolver(" + value.order_id + ")' style='cursor:pointer'>Devolver</a></th>";
                  tabla_db +="</tr>";
                });



                $("#tabla_db").prepend(tabla_db);

                $('#tabla').dataTable();
                
              } );

              function verstatus (order){
                $('#content').css('cursor', 'progress');
                url_get_status = '<?php echo $this->url->link("payment/todopago/get_status&token=".$this->session->data["token"]); ?>';
                $.get(url_get_status,{order_id:order},llegadaDatos); 
                return false;                                           
              }

              function llegadaDatos(datos)
              {
                $('#content').css('cursor', 'auto');
var modal = new tingle.modal({
    footer: true,
    stickyFooter: false,
    closeMethods: ['overlay', 'button', 'escape'],
    closeLabel: "Close",
});
console.log(datos);
modal.setContent(datos);
modal.open();

              }  
            </script>
            <table id="tabla" class="display" cellspacing="0" width="100%">

              <thead>
                <tr>
                  <th>Nro</th>
                  <th>Fecha</th>
                  <th>Nombre</th>
                  <th>Apellido</th>
                  <th>Tienda</th>
                  <th>Total</th>
                  <th>Devolución</th>
                </tr>
              </thead>

              <tfoot>
                <tr>
                  <th>Nro</th>
                  <th>Fecha</th>
                  <th>Nombre</th>
                  <th>Apellido</th>
                  <th>Tienda</th>
                  <th>Total</th>
                  <th>Devolución</th>
                </tr>
              </tfoot>

              <tbody id="tabla_db">   
              </tbody>
            </table>
          </div>
          <!-- END TAB STATUS-->

        </form>
      </div>
    </div>

          <?php echo $footer; ?>

    <script type="text/javascript">
        $('#htabs a').tabs();

        function devolver(order_id) {
            var monto = prompt("Monto parcial a devolver (valor real del producto, sin el costo adicional) o vacío para devolución total (ej: 1.23): ", "");

            if (monto !== null) {
              url_devolver = '<?php echo $this->url->link("payment/todopago/get_devolver&token=".$this->session->data["token"]); ?>';
              $('#content').css('cursor', 'progress');
              $.post(url_devolver, {
                  order_id: order_id,
                  monto: monto
              }, llegadaDatosDevolucion);
            }
            return false;
        }

        function llegadaDatosDevolucion(datos) {
            $('#content').css('cursor', 'auto');
            alert(datos);
        }
    </script>
    <script type="text/javascript" src="jquery.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#open').click(function() {

                $('#popup').fadeIn('slow');
                $('.popup-overlay').fadeIn('slow');
                $('.popup-overlay').height($(window).height());
                //return false;
            });

            $('#open_prod').click(function() {
               
                $('#popup_prod').fadeIn('slow');
                $('.popup-overlay').fadeIn('slow');
                $('.popup-overlay').height($(window).height());
                //return false;
            });

            $('#confirm_test').click(function() {
                $.post('view/template/payment/todopago_credentials.php', {
                    mail: $("#mail").val(),
                    pass: $("#pass").val(),
                    tab: "test"
                }, function(data) {
                  console.log(data);
                    json_data = JSON.parse(data);
                  console.log(json_data);
                  $("#header_test").val(json_data.Credentials.APIKey);
                  $("#site_id_test").val(json_data.Credentials.merchantId);
		  var a = json_data.Credentials.APIKey;
                  $("#security_test").val(a.replace("TODOPAGO",""));

                });

                $('#popup').fadeOut('slow');
                $('.popup-overlay').fadeOut('slow');
                return false;
            });
            $("#cancel-test").click(function(){    
                $('#popup').fadeOut('slow');
                $('.popup-overlay').fadeOut('slow');
                return false;
            });         


            $('#confirm_prod').click(function() {

                $.post("view/template/payment/todopago_credentials.php", {
                    mail: $("#mail_prod").val(),
                    pass: $("#pass_prod").val(),
                    tab: "prod"
                }, function(data) {
                  json_data = JSON.parse(data);
                  console.log(json_data);
                  $("#header_prod").val(json_data.Credentials.APIKey);
                  $("#site_id_prod").val(json_data.Credentials.merchantId);
		  var a = json_data.Credentials.APIKey;
                  $("#security_prod").val(a.replace("TODOPAGO",""));

                });

                $('#popup_prod').fadeOut('slow');
                $('.popup-overlay').fadeOut('slow');
                return false;
            });

            $("#cancel-prod").click(function(){    
                $('#popup_prod').fadeOut('slow');
                $('.popup-overlay').fadeOut('slow');
                return false;
            });

        });
    </script>

 
<style type="text/css">
    #popup {
        left: 0;
        position: absolute;
        top: 0;
        width: 100%;
        z-index: 1001;
    }
    
    #popup_prod {
        left: 0;
        position: absolute;
        top: 0;
        width: 100%;
        z-index: 1001;
    }
    
    .content-popup {
        margin: 0px auto;
        margin-top: 130px;
        position: relative;
        padding: 10px;
        width: 300px;
        height: 200px;
        border-radius: 4px;
        background-color: #FFFFFF;
        box-shadow: 0 2px 5px #666666;
    }
    
    .content-popup h2 {
        color: #48484B;
        border-bottom: 1px solid #48484B;
        margin-top: 0;
        padding-bottom: 4px;
    }
    
    .popup-overlay {
        left: 0;
        position: absolute;
        top: 0;
        width: 100%;
        z-index: 999;
        display: none;
        background-color: #777777;
        cursor: pointer;
        opacity: 0.7;
    }
    
    .close {
        positio#logon: absolute;
        right: 15px;
    }
    
    .content-popup img {
        position: relative;
        align-self: right;
    }
    
    .content-popup button {
        position: relative;
        left: 70px;
        bottom: 50px;
    }
     .content-popup #mail {
        position: relative;
        left: 30px;
        
    }
     .content-popup #mail-label {
        position: relative;
        left: 15px;
        
    }
       
     .content-popup #mail_prod {
        position: relative;
        left: 30px;
        
    }
     .content-popup #mail-label_prod {
        position: relative;
        left: 15px;
        
    }
    .content-popup label {}
    
    .content-popup input {}
</style>
    
        <script>

$(document).ready(function(){
        
    
      if ($('#habilitar').attr('checked')) {
            $("#todopago_maxinstallments").removeAttr("disabled");
                                 }else{
                                  $("#todopago_maxinstallments").val('0');
                                 }
    $("#habilitar").click(function() {  

if ($('#habilitar').prop('checked')) {

  $("#todopago_maxinstallments").removeAttr("disabled");


}else
    {
             $("#todopago_maxinstallments").prop('disabled', true);  
             $("#todopago_maxinstallments").val('0');
    
    }
     
    });        
    });        
            </script>
