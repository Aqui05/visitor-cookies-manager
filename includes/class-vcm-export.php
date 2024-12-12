<?php
class VCM_Export {
    private static $instance;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
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
