<?php  if ( ! defined( 'ABSPATH' ) ) exit;  ?>
<div class="wrap">
  <form method="post" action="options.php">
    <?php
    // Adiciona os campos de configuração
    settings_fields('sendzap-settings');
    do_settings_sections('sendzap-settings');
    submit_button();
    ?>
  </form>
</div>