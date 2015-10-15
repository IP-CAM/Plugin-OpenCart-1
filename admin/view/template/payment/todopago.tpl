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
              <td><select name="todopago_status">
                <?php if ($todopago_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
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
              <td><input type="number" name="todopago_deadline" min=0 value="<?php echo $deadline; ?>"/></td>
              <td><em>d&iacute;as m&aacute;ximos para la entrega</em></td>
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
          </table> 
        </div>
        <!-- END TAB GENERAL-->

        <!-- TAB AMBIENTE TEST -->
        <div id="tab-test">
          <table class="form">
            <tr>
              <td>Authorization HTTP</td>
              <td><input type="text" name="todopago_authorizationHTTPtest" value="<?php echo $todopago_authorizationHTTPtest; ?>" size="25" /></td>
              <td><em>se deben para datos en formato json. ejemplo: { "Authorization":"PRISMA 912EC803B2CE49E4A541068D495AB570"}</em></td>
            </tr>
            <tr>
              <td>Id Site Todo Pago</td>
              <td><input type="text" name="todopago_idsitetest" value="<?php echo $todopago_idsitetest; ?>" /></td>
              <td><em>Número de Comercio provisto por Todo Pago</em></td>
            </tr>
            <tr>
              <td>Security code</td>
              <td><input type="text" name="todopago_securitytest" value="<?php echo $todopago_securitytest; ?>" /></td>
              <td><em>C&oacute;digo provisto por Todo Pago</em></td>
            </tr>
          </table>
        </div>
        <!-- END TAB AMBIENTE TEST -->
        
        <!-- TAB AMBIENTE PRODUCCION -->
        <div id="tab-produccion">
          <table class="form">
            <tr>
              <td>Authorization HTTP</td>
              <td><input type="text" name="todopago_authorizationHTTPproduccion" value="<?php echo $todopago_authorizationHTTPproduccion; ?>" size="25" /></td>
              <td><em>se deben para datos en formato json. ejemplo: { "Authorization":"PRISMA 912EC803B2CE49E4A541068D495AB570"}</em></td>
            </tr>
            <tr>
              <td>Id Site Todo Pago</td>
              <td><input type="text" name="todopago_idsiteproduccion" value="<?php echo $todopago_idsiteproduccion; ?>" /></td>
              <td><em>Número de Comercio provisto por Todo Pago</em></td>
            </tr>
            <tr>
              <td>Security code</td>
              <td><input type="text" name="todopago_securityproduccion" value="<?php echo $todopago_securityproduccion; ?>" /></td>
              <td><em>Código provisto por Todo Pago</em></td>
            </tr>
          </table>
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
            //var_dump($orders_array);
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
                alert(datos);
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

    <script type="text/javascript"><!--
      $('#htabs a').tabs();
      //--></script> 
      <?php echo $footer; ?>
