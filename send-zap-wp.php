<?php
/*
 * Plugin Name: Send Zap
 * Plugin URI: https://www.tadeubrasil.com.br/plugins/send-zap
 * Description: Plugin to send messages to WhatsApp
 * Version: 1.0.0
 * Author: Tadeu
 * Author URI: https://www.tadeubrasil.com.br
 * License: GPL2
 * Text Domain: send-zap
 * Domain Path: /languages
 */

if (!defined('WPINC')) {
  die;
}

/**
 * Classe principal do plugin
 */
class SendZap
{
  /**
   * Construtor da classe
   */
  public function __construct()
  {
    $this->loadDependencies();
    $this->init();

    register_deactivation_hook(__FILE__, array($this, 'cleanupOnDeactivation'));
  }

  /**
   * Carrega as dependências
   */
  private function loadDependencies()
  {
    add_action('wp_enqueue_scripts', array($this, 'enqueueAssets'));

    add_action('admin_menu', array($this, 'addPluginPage'));
    add_action('admin_init', array($this, 'initSettings'));
    add_action('wp_footer', 'sendzapwp_code');
  }

  /**
   * Inicializa o plugin
   */
  private function init()
  {
    include_once(plugin_dir_path(__FILE__) . 'inc/sendzapwp-code.php');
  }

  /**
   * Enfileira os ativos (CSS e JS)
   */
  public function enqueueAssets()
  {
    wp_enqueue_style('sendzap-style', plugins_url('assets/css/sendzapwp.css', __FILE__), '', '1.0.1');
    //wp_enqueue_script('sendzap-script', plugins_url('assets/js/sendzapwp.js', __FILE__), array('jquery'), '1.0.2', true);
  }

  /**
   * Adiciona a página de configuração no menu do painel administrativo
   */
  public function addPluginPage()
  {
    add_options_page(
      __('Sendzapwp Settings', 'send-zap'),
      __('Send Zap', 'send-zap'),
      'manage_options',
      'sendzapwp',
      array($this, 'renderAdminPage')
    );
  }

  /**
   * Renderiza a página de configuração
   */
  public function renderAdminPage()
  {
    include_once(plugin_dir_path(__FILE__) . 'admin/templates/sendzapwp-admin-page.php');
  }

  /**
   * Inicializa as configurações da página de configuração
   */
  public function initSettings()
  {
    register_setting('sendzap-settings', 'sendzap_settings', array($this, 'validateSettings'));

    add_settings_section('sendzap-section', esc_html__('Send Zap Settings', 'send-zap'), array($this, 'renderSettingsSection'), 'sendzap-settings');

    add_settings_field('sendzap-phone', esc_html__('Number to send the message.', 'send-zap'), array($this, 'renderPhoneField'), 'sendzap-settings', 'sendzap-section');
    add_settings_field('sendzap-text-message', esc_html__('Text message', 'send-zap'), array($this, 'renderTextMessageField'), 'sendzap-settings', 'sendzap-section');
    add_settings_field('sendzap-enable', esc_html__('Enable Icon', 'send-zap'), array($this, 'renderEnableField'), 'sendzap-settings', 'sendzap-section');
    add_settings_field('sendzap-icon', esc_html__('Icon', 'send-zap'), array($this, 'renderIconField'), 'sendzap-settings', 'sendzap-section');
  }

  /**
   * Renderiza a seção de configurações
   */
  public function renderSettingsSection()
  {
    echo esc_html__('Configure the message options below:', 'send-zap');
  }

  /**
   * Renderiza o campo de número de telefone
   */
  public function renderPhoneField()
  {
    $options = get_option('sendzap_settings');
    $phone = isset($options['phone']) ? $options['phone'] : '';
    echo '<input type="input" name="sendzap_settings[phone]" value="' . esc_attr($phone) . '">'; ?>
    <p class="description" id="tagline-description"><?php esc_html__('Enter the number that will receive the message, <br> with the country code first. Ex. +5583912345678', 'send-zap'); ?></p>
<?php
  }

  /**
   * Renderiza o campo de mensagem de texto
   */
  public function renderTextMessageField()
  {
    $options = get_option('sendzap_settings');
    $text_message = isset($options['text_message']) ? $options['text_message'] : 'Hi, Send zap WP';
    echo '<input type="input" name="sendzap_settings[text_message]" value="' . esc_attr($text_message) . '">';
  }

  /**
   * Renderiza o campo de ativar/desativar o ícone
   */
  public function renderEnableField()
  {
    $options = get_option('sendzap_settings');
    $enabled = isset($options['enabled']) ? $options['enabled'] : false;
    echo '<label><input type="checkbox" name="sendzap_settings[enabled]" value="1" ' . checked($enabled, true, false) . '>' . esc_html__('Enabled', 'send-zap') . '</label>';
  }
  /**
   * Renderiza o campo de ativar/desativar o ícone
   */
  public function renderIconField()
  {
    $options = get_option('sendzap_settings');
    $selected_icons = isset($options['icons']) ? $options['icons'] : array();
    $icons = array(
      'whatsapp-01' => 'whatsapp-01.svg',
      'whatsapp-02' => 'whatsapp-02.svg',
      'whatsapp-03' => 'whatsapp-03.svg',
    );

    $icons_dir = plugin_dir_url(__FILE__) . 'assets/imgs/';

    foreach ($icons as $key => $filename) {
      $checked = in_array($key, $selected_icons) ? 'checked' : '';
      $icon_url = $icons_dir . $filename;
      echo '<label><input type="radio" name="sendzap_settings[icons][0]" value="' . esc_attr($key) . '" ' . esc_attr($checked) . '><img src="' . esc_url($icon_url) . '" width="50px"></label><br>';
    }
  }
  /**
   * Validação das opções de configuração
   */
  public function validateSettings($input)
  {
    $output = array();
    if (isset($input['phone'])) {
      $output['phone'] = sanitize_text_field($input['phone']);
    }
    if (isset($input['text_message'])) {
      $output['text_message'] = sanitize_text_field($input['text_message']);
    }
    if (isset($input['enabled'])) {
      $output['enabled'] = (bool) $input['enabled'];
    }

    if (isset($input['icons']) && is_array($input['icons'])) {
      $valid_icons = array('whatsapp-01', 'whatsapp-02', 'whatsapp-03');
      $output['icons'] = array_intersect($input['icons'], $valid_icons);
    }
    return $output;
  }

  /**
   * Limpa as configurações do plugin ao desativá-lo
   */
  public function cleanupOnDeactivation()
  {
    delete_option('sendzap_settings');
  }
}

// Instancia a classe do plugin
$send_zap = new SendZap();
