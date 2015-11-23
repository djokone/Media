<!DOCTYPE html>
<html>
    <head>
        <title>Uploader</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <?php echo $this->Html->css('/Media/css/style.css'); ?>
        <?php echo $this->fetch('css'); ?>
    </head>
    <body>

	   <?php echo $this->Flash->render(); ?>

       <?php echo $this->fetch('content'); ?>

        <!-- jQuery AND jQueryUI -->

        <?php echo $this->Html->script('jquery/jquery.js'); ?>
        <?php echo $this->Html->script('jquery/jqueryui.js'); ?>
        <?php echo $this->fetch('script'); ?>

    </body>
</html>
<?php// echo die(); ?>