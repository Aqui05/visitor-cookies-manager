<?php
class VCM_Cookie_Consent {
    private static $instance = null;

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
        // Ajouter la barre de consentement
        add_action('wp_footer', array($this, 'render_cookie_consent_bar'));
        
        // Enqueue scripts et styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_consent_scripts'));
        
        // Gérer l'AJAX pour le choix des cookies
        add_action('wp_ajax_vcm_set_cookie_consent', array($this, 'ajax_set_cookie_consent'));
        add_action('wp_ajax_nopriv_vcm_set_cookie_consent', array($this, 'ajax_set_cookie_consent'));
    }

    public function render_cookie_consent_bar() {
        // Ne pas afficher si le consentement a déjà été donné
        if ($this->is_consent_given()) {
            return;
        }
        ?>
        <div id="vcm-cookie-consent-bar" class="vcm-cookie-consent-bar">
            <div class="vcm-cookie-content">
                <p>
                    <?php _e('Ce site utilise des cookies pour améliorer votre expérience de navigation.', 'visitor-cookies-manager'); ?>
                </p>
                <div class="vcm-cookie-buttons">
                    <button id="vcm-accept-cookies" class="vcm-button vcm-accept">
                        <?php _e('Accepter', 'visitor-cookies-manager'); ?>
                    </button>
                    <button id="vcm-refuse-cookies" class="vcm-button vcm-refuse">
                        <?php _e('Refuser', 'visitor-cookies-manager'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    public function enqueue_consent_scripts() {
        wp_enqueue_script(
            'vcm-cookie-consent', 
            VCM_PLUGIN_URL . 'public/js/cookie-consent.js', 
            array('jquery'), 
            VCM_VERSION, 
            true
        );

        wp_enqueue_style(
            'vcm-cookie-consent-style', 
            VCM_PLUGIN_URL . 'assets/css/cookie-consent.css', 
            array(), 
            VCM_VERSION
        );

        wp_localize_script('vcm-cookie-consent', 'vcmCookieData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vcm-cookie-nonce')
        ));
    }

    /*public function ajax_set_cookie_consent() {
        // Vérifier le nonce
        check_ajax_referer('vcm-cookie-nonce', 'security');

        $consent = isset($_POST['consent']) ? sanitize_text_field($_POST['consent']) : 'refused';

        // Définir un cookie de consentement pour un an
        setcookie(
            'vcm_cookie_consent', 
            $consent, 
            time() + (365 * DAY_IN_SECONDS), 
            COOKIEPATH, 
            COOKIE_DOMAIN,
            true,  // Secure
            true   // HttpOnly
        );

        wp_send_json_success(array(
            'message' => __('Votre choix a été enregistré.', 'visitor-cookies-manager')
        ));
    }*/

    public function ajax_set_cookie_consent() {
        // Vérifier le nonce
        check_ajax_referer('vcm-cookie-nonce', 'security');
    
        $consent = isset($_POST['consent']) ? sanitize_text_field($_POST['consent']) : 'refused';
    
        // Définir un cookie de consentement pour un an
        setcookie(
            'vcm_cookie_consent', 
            $consent, 
            time() + (365 * DAY_IN_SECONDS), 
            COOKIEPATH, 
            COOKIE_DOMAIN,
            true,  // Secure
            true   // HttpOnly
        );
    
        // Si le consentement est accepté, déclencher la collecte des données
        if ($consent === 'accepted') {
            do_action('vcm_trigger_data_collection');
        }
    
        wp_send_json_success(array(
            'message' => __('Votre choix a été enregistré.', 'visitor-cookies-manager')
        ));
    }

    private function is_consent_given() {
        // Vérifier si le cookie de consentement existe
        return isset($_COOKIE['vcm_cookie_consent']);
    }

    public function is_consent_accepted() {
        return isset($_COOKIE['vcm_cookie_consent']) && 
               $_COOKIE['vcm_cookie_consent'] === 'accepted';
    }
}