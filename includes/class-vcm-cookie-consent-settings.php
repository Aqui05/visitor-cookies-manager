<?php
class VCM_Cookie_Consent_Settings {
    private static $instance = null;
    private $option_name = 'vcm_cookie_consent_settings';

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        // Ajouter une page de paramètres dans l'administration
        add_action('admin_menu', [$this, 'add_settings_page']);
        
        // Enregistrer les paramètres
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_settings_page() {
        add_options_page(
            __('Paramètres du Gestionnaire de Cookies', 'visitor-cookies-manager'),
            __('Cookies', 'visitor-cookies-manager'),
            'manage_options',
            'vcm-cookie-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('vcm_cookie_consent_settings_group', $this->option_name);

        add_settings_section(
            'vcm_cookie_consent_section',
            __('Personnalisation de la Bannière de Cookies', 'visitor-cookies-manager'),
            [$this, 'section_callback'],
            'vcm-cookie-settings'
        );

        // Champ pour le texte de la bannière
        add_settings_field(
            'banner_text',
            __('Texte de la Bannière', 'visitor-cookies-manager'),
            [$this, 'banner_text_callback'],
            'vcm-cookie-settings',
            'vcm_cookie_consent_section'
        );

        // Champ pour la couleur du bouton Accepter
        add_settings_field(
            'accept_button_color',
            __('Couleur du Bouton Accepter', 'visitor-cookies-manager'),
            [$this, 'accept_button_color_callback'],
            'vcm-cookie-settings',
            'vcm_cookie_consent_section'
        );

        // Champ pour la couleur du bouton Refuser
        add_settings_field(
            'refuse_button_color',
            __('Couleur du Bouton Refuser', 'visitor-cookies-manager'),
            [$this, 'refuse_button_color_callback'],
            'vcm-cookie-settings',
            'vcm_cookie_consent_section'
        );
    }

    public function section_callback() {
        echo '<p>' . __('Personnalisez l\'apparence et le texte de votre bannière de consentement aux cookies.', 'visitor-cookies-manager') . '</p>';
    }

    public function banner_text_callback() {
        $options = get_option($this->option_name);
        $text = isset($options['banner_text']) ? $options['banner_text'] : __('Ce site utilise des cookies pour améliorer votre expérience de navigation.', 'visitor-cookies-manager');
        
        echo '<textarea name="' . $this->option_name . '[banner_text]" rows="4" cols="50">' . esc_textarea($text) . '</textarea>';
    }

    public function accept_button_color_callback() {
        $options = get_option($this->option_name);
        $color = isset($options['accept_button_color']) ? $options['accept_button_color'] : '#4CAF50';
        
        echo '<input type="color" name="' . $this->option_name . '[accept_button_color]" value="' . esc_attr($color) . '">';
    }

    public function refuse_button_color_callback() {
        $options = get_option($this->option_name);
        $color = isset($options['refuse_button_color']) ? $options['refuse_button_color'] : '#f44336';
        
        echo '<input type="color" name="' . $this->option_name . '[refuse_button_color]" value="' . esc_attr($color) . '">';
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Paramètres du Gestionnaire de Cookies', 'visitor-cookies-manager'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('vcm_cookie_consent_settings_group');
                do_settings_sections('vcm-cookie-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    // Méthode pour récupérer les paramètres avec des valeurs par défaut
    public function get_settings() {
        $defaults = [
            'banner_text' => __('Ce site utilise des cookies pour améliorer votre expérience de navigation.', 'visitor-cookies-manager'),
            'accept_button_color' => '#4CAF50',
            'refuse_button_color' => '#f44336'
        ];

        $options = get_option($this->option_name);
        return wp_parse_args($options, $defaults);
    }
}