<?php
echo $header;
echo $column_left;
echo $column_right; ?>
<div id="content">
    <h2> <?php echo $errorTitulo ?> </h2>
    <hr>
    <h3 style="color: orange;">Orden #<?php echo $order_id ?></h3>
    <p><?php echo $errorTexto ?> </p>
    <a href="<?php echo $this->url->link('common/home')?>">Click aquí para ir a la página principal.</a>
</div>
<?php echo $footer; ?>