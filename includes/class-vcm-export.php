<?php
class VCM_Export {
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
        add_action('wp_ajax_vcm_export_cookies', array($this, 'export_cookies_to_csv'));
    }

    public function export_cookies_to_csv() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Accès non autorisé', 'visitor-cookies-manager'));
        }

        // Récupérer les paramètres de filtrage
        $params = array(
            'search' => isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '',
            'start_date' => isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '',
            'end_date' => isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '',
            'device_type' => isset($_GET['device_type']) ? sanitize_text_field($_GET['device_type']) : ''
        );

        // Récupérer toutes les données sans pagination
        $data_collector = VCM_Data_Collector::get_instance();
        $result = $data_collector->get_visitors_data(array_merge($params, [
            'per_page' => 10000,  // Grande limite pour tout exporter
            'page' => 1
        ]));

        // Générer le CSV
        $this->generate_csv($result['data']);
        exit;
    }

    private function generate_csv($data) {
        // En-têtes pour le téléchargement
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=visitor_cookies_export_' . date('Y-m-d_H-i-s') . '.csv');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Créer un fichier CSV en mémoire
        $output = fopen('php://output', 'w');

        // En-têtes du CSV
        fputcsv($output, array(
            'ID', 
            'Adresse IP', 
            'User Agent', 
            'Type d\'appareil', 
            'Est Mobile', 
            'Date de visite'
        ));

        // Écrire les données
        foreach ($data as $row) {
            fputcsv($output, array(
                $row['id'],
                $row['ip_address'],
                $row['user_agent'],
                $row['device_type'],
                $row['is_mobile'] ? 'Oui' : 'Non',
                $row['visit_date']
            ));
        }

        fclose($output);
    }
}