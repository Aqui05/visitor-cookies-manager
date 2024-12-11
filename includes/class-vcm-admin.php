<?php
class VCM_Admin {
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
        // Ajouter un menu d'administration
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Enregistrer les scripts et styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

        // Actions AJAX
        add_action('wp_ajax_vcm_export_data', array($this, 'ajax_export_data'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'Visitor Cookies Manager',
            'Cookies',
            'manage_options',
            'visitor-cookies-manager',
            array($this, 'render_admin_page'),
            'dashicons-welcome-view-site',
            30
        );
    }

    public function render_admin_page() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.', 'visitor-cookies-manager'));
        }

        // Récupérer les données
        $data_collector = VCM_Data_Collector::get_instance();
        
        $page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $device_type = isset($_GET['device_type']) ? sanitize_text_field($_GET['device_type']) : '';
        $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
        $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';

        $visitors_data = $data_collector->get_visitors_data(array(
            'page' => $page,
            'search' => $search,
            'device_type' => $device_type,
            'start_date' => $start_date,
            'end_date' => $end_date
        ));

        // Charger la vue
        include VCM_PLUGIN_DIR . 'admin/views/cookies-list-page.php';
    }

    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'toplevel_page_visitor-cookies-manager') {
            return;
        }

        wp_enqueue_script(
            'vcm-admin-script', 
            VCM_PLUGIN_URL . 'assets/js/admin.js', 
            array('jquery'), 
            VCM_VERSION, 
            true
        );

        wp_enqueue_style(
            'vcm-admin-style', 
            VCM_PLUGIN_URL . 'assets/css/admin.css', 
            array(), 
            VCM_VERSION
        );

        // Localiser le script avec des données
        wp_localize_script('vcm-admin-script', 'vcmAdminData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vcm-export-nonce')
        ));
    }

    public function ajax_export_data() {
    }
}   