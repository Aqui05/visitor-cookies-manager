

<?php

        // Check if the export action is triggered

            $selected_ids = isset($_GET['visitors_ids']) ? array_map('intval', $_GET['visitors_ids']) : [];
        
            if (empty($selected_ids)) {
                wp_die(__('Aucun élément sélectionné pour l\'exportation.', 'visitor-cookies-manager'));
            }
        
            // Récupérer les données
            $data_collector = VCM_Data_Collector::get_instance();
            $result = $data_collector->get_visitors_data_by_ids($selected_ids);
        


            $data = $result['data'];


            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename=visitor_cookies_export_' . date('Y-m-d_H-i-s') . '.csv');

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