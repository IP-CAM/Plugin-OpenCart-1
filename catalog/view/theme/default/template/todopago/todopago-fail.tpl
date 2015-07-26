<?php 
echo $header;
echo $column_left;
echo $column_right; ?>
<div id="content">
    <h2>No se pudo completar el Pago</h2>
    <hr>
    <h3 style="color: orange;">Orden #<?php echo $order_id ?></h3><br />
    <a href="<?php echo $this->url->link('common/home')?>">Click aca para ir a la pagina principal.</a>
    
</div>
<?php echo $footer; ?>
