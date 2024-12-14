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

// Sécurité : bloquer l'accès direct
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
        // Activation et désactivation du plugin
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        // Initialisation
        add_action('init', [$this, 'load_plugin_textdomain']);
        add_action('plugins_loaded', [$this, 'init_components']);
        // Dans le constructeur de votre classe ou dans le fichier principal du plugin

        add_action('wp_ajax_vcm_export_csv', [$this, 'vcm_ajax_export_csv']);
    }

    public function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE " . $wpdb->prefix . "visitor_cookies (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            ip_address varchar(45) NOT NULL,
            user_agent text NOT NULL,
            device_type varchar(20) NOT NULL,
            is_mobile tinyint(1) NOT NULL,
            visit_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        add_option('vcm_cookie_consent_enabled', true);
    }

    public function deactivate() {
        // Suppression des données si nécessaire
        // global $wpdb;
        // delete_option('vcm_cookie_consent_enabled');
        // $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "visitor_cookies");
    }

    public function load_plugin_textdomain() {
        load_plugin_textdomain('visitor-cookies-manager', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }






    public function vcm_ajax_export_csv() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas la permission d\'accéder à cette action.', 'visitor-cookies-manager'));
        }

        // Vérifier la sélection d'IDs
        $selected_ids = isset($_POST['visitors_ids']) ? array_map('intval', $_POST['visitors_ids']) : [];
        if (empty($selected_ids)) {
            wp_die(__('Aucun élément sélectionné pour l\'exportation.', 'visitor-cookies-manager'));
        }

        // Récupérer les données
        $data_collector = VCM_Data_Collector::get_instance();
        $data = $data_collector->get_visitors_data_by_ids($selected_ids);

        if (empty($data['data'])) {
            wp_die(__('Aucune donnée à exporter.', 'visitor-cookies-manager'));
        }

        // Générer le CSV et envoyer directement
        $exporter = VCM_Export::get_instance();
        $exporter->generate_csv($data['data']);
    }


    private function log_error($message) {
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('Visitor Cookies Manager: ' . $message);
        }
    }



    public function init_components() {
        // Initialiser les composants du plugin
        VCM_Data_Collector::get_instance();
        VCM_Admin::get_instance();
        VCM_Cookie_Consent::get_instance();
        VCM_Export::get_instance();

        // Ajouter une action personnalisée pour la collecte de données
        add_action('vcm_trigger_data_collection', [VCM_Data_Collector::get_instance(), 'collect_visitor_data']);
    }
}

// Initialiser le plugin
VisitorCookiesManager::get_instance();