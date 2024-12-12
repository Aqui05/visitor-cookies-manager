<?php
/*
Plugin Name: Visitor Cookies Manager
Plugin URI: http://exemple.com/
Description: Gestionnaire avancé de cookies et suivi des visiteurs
Version: 1.0.0
Author: Votre Nom
Author URI: http://exemple.com/
Text Domain: visitor-cookies-manager
*/

// Sécurité : bloquer l'accès direct au fichier [L'ajouter au débur de tous les fichiers PHP]
if (!defined('ABSPATH')) {
    exit;
}

// Constantes du plugin
define('VCM_VERSION', '1.0.0');
define('VCM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VCM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Inclure les fichiers nécessaires
require_once VCM_PLUGIN_DIR . 'includes/class-vcm-data-collector.php';
require_once VCM_PLUGIN_DIR . 'includes/class-vcm-admin.php';
require_once VCM_PLUGIN_DIR . 'includes/class-vcm-cookie-consent.php';
require_once VCM_PLUGIN_DIR . 'includes/class-vcm-export.php';

class VisitorCookiesManager {
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
        // Activation du plugin
        register_activation_hook(__FILE__, array($this, 'activate'));

        // Désactivation du plugin
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Initialisation
        add_action('init', array($this, 'load_plugin_textdomain'));
        add_action('plugins_loaded', array($this, 'init_components'));
    }

    public function activate() {
        // Créer les tables de base de données nécessaires
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE " . $wpdb->prefix . "visitor_cookies (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            ip_address varchar(45) NOT NULL,
            user_agent text NOT NULL,
            device_type varchar(20) NOT NULL,
            is_mobile tinyint(1) NOT NULL,
            visit_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Ajouter des options par défaut
        add_option('vcm_cookie_consent_enabled', true);
    }

    public function deactivate() {
        //  : supprimer les options et tables 
        // delete_option('vcm_cookie_consent_enabled');
        // $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "visitor_cookies");
    }

    public function load_plugin_textdomain() {
        load_plugin_textdomain('visitor-cookies-manager', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function init_components() {
        // Initialiser les composants du plugin
        VCM_Data_Collector::get_instance();
        VCM_Admin::get_instance();
        VCM_Cookie_Consent::get_instance();
        VCM_Export::get_instance();
    }
}

// Initialiser le plugin
VisitorCookiesManager::get_instance();