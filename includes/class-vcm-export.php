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



    public function generate_csv($data, $export_type = 'visitors', $custom_dir = null) {
        // Désactiver toute sortie précédente et la mise en tampon
        if (ob_get_level()) {
            ob_end_clean();
        }
    
        // Choisir le répertoire de stockage
        $upload_dir = $custom_dir ?? sys_get_temp_dir() . '/csv/';
        
        // Créer le répertoire s'il n'existe pas
        if (!file_exists($upload_dir)) {
            // Utiliser le mode 0755 pour plus de sécurité
            if (!mkdir($upload_dir, 0755, true)) {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Impossible de créer le répertoire CSV',
                    'error_details' => error_get_last()
                ]);
                exit();
            }
        }
    
        // Vérifier les permissions d'écriture du répertoire
        if (!is_writable($upload_dir)) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Le répertoire n\'est pas accessible en écriture',
                'directory' => $upload_dir
            ]);
            exit();
        }
    
        // Nom de fichier unique
        $filename = $export_type . '_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = $upload_dir . $filename;
    
        // Tenter d'ouvrir le fichier en écriture
        $fp = @fopen($filepath, 'w');
        
        // Vérification de l'ouverture du fichier
        if ($fp === false) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Impossible de créer le fichier CSV',
                'error_details' => [
                    'last_error' => error_get_last(),
                    'filepath' => $filepath,
                    'temp_dir' => sys_get_temp_dir()
                ]
            ]);
            exit();
        }
    
        // Vérifier si on peut écrire
        if (fwrite($fp, '') === false) {
            fclose($fp);
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Impossible d\'écrire dans le fichier CSV'
            ]);
            exit();
        }
    
        // Écrire les en-têtes
        fputcsv($fp, [
            'ID', 
            'Adresse IP', 
            'User Agent', 
            'Type d\'appareil', 
            'Est Mobile', 
            'Date de visite'
        ]);
        
        // Écrire les données
        foreach ($data as $row) {
            fputcsv($fp, [
                $row['id'] ?? '', 
                $row['ip_address'] ?? '', 
                $row['user_agent'] ?? '', 
                $this->get_device_type($row['user_agent'] ?? ''), 
                $row['is_mobile'] ? 'Oui' : 'Non', 
                $row['visit_date'] ?? ''
            ]);
        }
        
        // Fermer le fichier
        fclose($fp);
    
        // Répondre avec les informations du fichier en JSON
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'relative_path' => '/csv/' . $filename
        ];
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
