<?php
class VCM_Data_Collector {
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
        // Hook pour collecter les données lors du chargement de la page
        add_action('wp', array($this, 'collect_visitor_data'));
    }

    public function collect_visitor_data() {
        // Ne pas collecter pour les pages admin ou les requêtes AJAX
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
    
        // Vérifier si le consentement est donné
        $cookie_consent = VCM_Cookie_Consent::get_instance();
        if (!$cookie_consent->is_consent_accepted()) {
            return;
        }
    
        global $wpdb;
        $table_name = $wpdb->prefix . 'visitor_cookies';
    
        $ip_address = $this->get_ip_address();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $device_type = $this->detect_device_type($user_agent);
        $is_mobile = wp_is_mobile() ? 1 : 0;
    
        $wpdb->insert(
            $table_name,
            array(
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'device_type' => $device_type,
                'is_mobile' => $is_mobile,
                'visit_date' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%s')
        );
    }

    /*public function collect_visitor_data() {
        // Vérifier si le consentement est donné
        $cookie_consent = new VCM_Cookie_Consent();
        if (!$cookie_consent->is_consent_accepted()) {
            return;
        }
    
        // Ne pas collecter pour les pages admin ou les requêtes AJAX
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
    
        global $wpdb;
        $table_name = $wpdb->prefix . 'visitor_cookies';
    
        $ip_address = $this->get_ip_address();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $device_type = $this->detect_device_type($user_agent);
        $is_mobile = wp_is_mobile() ? 1 : 0;
    
        $wpdb->insert(
            $table_name,
            array(
                'ip_address' => $ip_address,
                'user_agent' => $user_agent,
                'device_type' => $device_type,
                'is_mobile' => $is_mobile,
                'visit_date' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%d', '%s')
        );
    }*/

    private function get_ip_address() {
        // Liste des clés potentielles pour l'adresse IP
        $ip_keys = array(
            'HTTP_CLIENT_IP', 
            'HTTP_X_FORWARDED_FOR', 
            'HTTP_X_FORWARDED', 
            'HTTP_X_CLUSTER_CLIENT_IP', 
            'HTTP_FORWARDED_FOR', 
            'HTTP_FORWARDED', 
            'REMOTE_ADDR'
        );
        
        // Vérifier les en-têtes de proxy et de forwarding
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    // Validation avancée de l'IP
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        // Essayer de récupérer l'adresse IP réelle en local
        if (isset($_SERVER['SERVER_ADDR']) && $_SERVER['SERVER_ADDR'] !== '127.0.0.1') {
            return $_SERVER['SERVER_ADDR'];
        }
        
        // Dernière option : utiliser des méthodes alternatives
        try {
            // Essayer de récupérer l'IP via des commandes système
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Commande Windows
                $output = shell_exec('ipconfig | findstr /I "IPv4 Address"');
                preg_match('/\d+\.\d+\.\d+\.\d+/', $output, $matches);
                if (!empty($matches[0])) {
                    return $matches[0];
                }
            } else {
                // Commande Unix/Linux
                $output = shell_exec("hostname -I | awk '{print $1}'");
                if (filter_var(trim($output), FILTER_VALIDATE_IP)) {
                    return trim($output);
                }
            }
        } catch (Exception $e) {
            // Gestion silencieuse des erreurs
        }
        
        return 'Unknown';
    }

    private function detect_device_type($user_agent) {
        $user_agent = strtolower($user_agent);
    
        $device_type = 'Desktop'; // Valeur par défaut
        $os = 'Unknown';
        $browser = 'Unknown';
    
        // Détection du type d'appareil
        if (strpos($user_agent, 'mobile') !== false || strpos($user_agent, 'android') !== false || strpos($user_agent, 'iphone') !== false) {
            $device_type = 'Mobile';
        } elseif (strpos($user_agent, 'tablet') !== false || strpos($user_agent, 'ipad') !== false) {
            $device_type = 'Tablet';
        }
    
        // Détection du système d'exploitation
        if (strpos($user_agent, 'windows nt') !== false) {
            $os = 'Windows';
        } elseif (strpos($user_agent, 'mac os') !== false || strpos($user_agent, 'macintosh') !== false) {
            $os = 'Mac OS';
        } elseif (strpos($user_agent, 'linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($user_agent, 'android') !== false) {
            $os = 'Android';
        } elseif (strpos($user_agent, 'iphone') !== false || strpos($user_agent, 'ipad') !== false || strpos($user_agent, 'ipod') !== false) {
            $os = 'iOS';
        } elseif (strpos($user_agent, 'blackberry') !== false) {
            $os = 'BlackBerry';
        } elseif (strpos($user_agent, 'windows phone') !== false) {
            $os = 'Windows Phone';
        }
    
        // Détection du navigateur
        if (strpos($user_agent, 'edg') !== false) {
            $browser = 'Microsoft Edge';
        } elseif (strpos($user_agent, 'chrome') !== false) {
            $browser = 'Google Chrome';
        } elseif (strpos($user_agent, 'safari') !== false && strpos($user_agent, 'chrome') === false) {
            $browser = 'Safari';
        } elseif (strpos($user_agent, 'firefox') !== false) {
            $browser = 'Mozilla Firefox';
        } elseif (strpos($user_agent, 'opera') !== false || strpos($user_agent, 'opr') !== false) {
            $browser = 'Opera';
        } elseif (strpos($user_agent, 'msie') !== false || strpos($user_agent, 'trident') !== false) {
            $browser = 'Internet Explorer';
        }
    
        // Concaténation des informations en une chaîne
        return sprintf('%s | %s | %s', $device_type, $os, $browser);
    }
    
    


    //Sélectionner les visiteurs avec leur IDs

    public function get_visitors_by_ids($ids) {
        global $wpdb;
    
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id IN ($placeholders)",
            $ids
        );
    
        return $wpdb->get_results($query, ARRAY_A);
    }

    

    /*public function get_visitors_data_by_ids($ids) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'visitor_cookies';

        // Préparer la requête SQL avec des paramètres sécurisés
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id IN ($placeholders)",
            $ids
        );

        return $wpdb->get_results($query, ARRAY_A);
    }*/
    

    public function get_visitors_data_by_ids($ids) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'visitor_cookies';
    
        // Préparer les IDs pour la requête SQL
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
    
        // Construire la requête SQL
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id IN ($placeholders)",
            $ids
        );
    
        // Récupérer les données correspondant aux IDs
        $results = $wpdb->get_results($query, ARRAY_A);
    
        // Compter le nombre total d'éléments
        $total_items = count($results);
    
        return array(
            'data' => $results,
            'total_items' => $total_items
        );
    }
    



    // Méthode pour récupérer les données avec filtrage et pagination
    public function get_visitors_data($args = array()) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'visitor_cookies';

        $defaults = array(
            'per_page' => 20,
            'page' => 1,
            'search' => '',
            'start_date' => '',
            'end_date' => '',
            'device_type' => '',
            'order_by' => 'visit_date',
            'order' => 'DESC'
        );

        $params = wp_parse_args($args, $defaults);
        $offset = ($params['page'] - 1) * $params['per_page'];

        $where_clauses = array('1=1');
        $where_values = array();

        // Filtres
        if (!empty($params['search'])) {
            $where_clauses[] = "(ip_address LIKE %s OR user_agent LIKE %s)";
            $where_values[] = '%' . $wpdb->esc_like($params['search']) . '%';
            $where_values[] = '%' . $wpdb->esc_like($params['search']) . '%';
        }

        if (!empty($params['start_date'])) {
            $where_clauses[] = "visit_date >= %s";
            $where_values[] = $params['start_date'];
        }

        if (!empty($params['end_date'])) {
            $where_clauses[] = "visit_date <= %s";
            $where_values[] = $params['end_date'];
        }

        if (!empty($params['device_type'])) {
            $where_clauses[] = "device_type = %s";
            $where_values[] = $params['device_type'];
        }

        $where_sql = implode(' AND ', $where_clauses);

        // Récupération des données
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name 
            WHERE $where_sql 
            ORDER BY {$params['order_by']} {$params['order']} 
            LIMIT %d OFFSET %d",
            array_merge($where_values, array($params['per_page'], $offset))
        );

        $results = $wpdb->get_results($query, ARRAY_A);

        // Récupération du nombre total de résultats
        $count_query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE $where_sql",
            $where_values
        );
        $total_items = $wpdb->get_var($count_query);

        return array(
            'data' => $results,
            'total_items' => $total_items,
            'total_pages' => ceil($total_items / $params['per_page'])
        );
    }


    public function get_all_visitors_data() {
        global $wpdb;
    
        $table_name = $wpdb->prefix . 'visitor_cookies';
        $query = "SELECT * FROM {$table_name}";
        $results = $wpdb->get_results($query, ARRAY_A);
    
        return [
            'data' => $results,
        ];
    }
    
}