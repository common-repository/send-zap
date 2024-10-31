<?php
function sendzapwp_code(){

  $options = get_option('sendzap_settings');
  $enabled = isset($options['enabled']) ? $options['enabled'] : false;
  $phone = isset($options['phone']) ? $options['phone'] : false;
  $msg = isset($options['text_message']) ? $options['text_message'] : false;
  $icon = isset($options['icons']['0']) ? $options['icons']['0'] : false;

  if (!$enabled || !$phone || !$msg || !$icon) {
    return; // Verifica se as opções necessárias estão definidas
  }

  $phone = preg_replace('/\s+/', '', $phone);
  $phone = sanitize_text_field($phone);
  $msg = sanitize_text_field($msg);

  $icons_dir = esc_url(WP_PLUGIN_URL . '/send-zap/assets/imgs/'); // Escape de URL
?>
  <style>
    #sendzapwp {
      background-image: url("<?php echo esc_url($icons_dir . $icon); ?>.svg"); // Escape de URL
    }
  </style>
  <a id="sendzapwp" href="<?php echo esc_url("https://api.whatsapp.com/send?phone={$phone}&text={$msg}"); ?>" target="_blank"></a> <!-- Escape de URL -->
<?php
}
