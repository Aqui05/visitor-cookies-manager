<?php
class VCM_Export {
    private static $instance;

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
        add_action('wp_ajax_vcm_export_selected_cookies', array($this, 'export_selected_cookies_to_csv'));
    }

    public function export_selected_cookies_to_csv() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Accès non autorisé', 'visitor-cookies-manager'));
        }

        // Vérifier le nonce
        check_ajax_referer('vcm-export-nonce', 'nonce');

        // Récupérer les IDs sélectionnés
        $selected_ids = isset($_POST['visitors_ids']) ? array_map('intval', $_POST['visitors_ids']) : [];

        if (empty($selected_ids)) {
            wp_send_json_error(__('Aucun visiteur sélectionné', 'visitor-cookies-manager'));
        }

        // Récupérer les données des visiteurs sélectionnés
        $data_collector = VCM_Data_Collector::get_instance();
        $result = $data_collector->get_visitors_data_by_ids($selected_ids);

        // Générer et envoyer le CSV
        ob_start();
        $this->generer_csv($result);
        $csv_content = ob_get_clean();

        wp_send_json_success([
            'csv_content' => base64_encode($csv_content),
            'filename' => 'visitor_cookies_export_selected_' . date('Y-m-d_H-i-s') . '.csv'
        ]);
    }

    private function generer_csv($data) {
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



    public function generate_csv($data, $export_type = 'visitors') {
        // En-têtes pour forcer le téléchargement
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="visitor_cookies_export_' . $export_type . '_' . date('Y-m-d_H-i-s') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
    
        // Ouvrir un flux de sortie
        $output = fopen('php://output', 'w');
    
        // Définir une fin de ligne personnalisée
        $eol = "\n";
    
        // Écrire les en-têtes du fichier CSV
        fputcsv($output, [
            'ID',
            'Adresse IP',
            'User Agent',
            'Type d\'appareil',
            'Est Mobile',
            'Date de visite',
        ], ',', '"');
    
        // Écrire chaque ligne de données
        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'] ?? '',
                $row['ip_address'] ?? '',
                $row['user_agent'] ?? '',
                $this->get_device_type($row['user_agent'] ?? ''),
                $row['is_mobile'] ? 'Oui' : 'Non',
                $row['visit_date'] ?? '',
            ], ',', '"');
        }
    
        fclose($output);
        //exit;
    }

    private function get_device_type($user_agent) {
        if (preg_match('/mobile/i', $user_agent)) {
            return 'Mobile';
        } elseif (preg_match('/tablet|ipad/i', $user_agent)) {
            return 'Tablette';
        } elseif (preg_match('/linux|windows|macintosh|x11/i', $user_agent)) {
            return 'Desktop';
        } else {
            return 'Inconnu';
        }
    }
}
