<?php
// Vérifier que ce fichier est appelé directement
if (!defined('ABSPATH')) {
    exit;
}

// Inclure la classe parente si ce n'est pas déjà fait
if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class VCM_Visitors_List_Table extends WP_List_Table {
    private $visitors_data;

    public function __construct() {
        parent::__construct([
            'singular' => 'visiteur',
            'plural'   => 'visiteurs',
            'ajax'     => false
        ]);
    }

    public function get_columns() {
        $columns = [
            'user_agent'    => __('User Agent', 'visitor-cookies-manager'),
            'device_type'   => __('Type de terminal', 'visitor-cookies-manager'),
            'ip_address'    => __('Adresse IP', 'visitor-cookies-manager'),
            'device_detail' => __('Type d\'appareil', 'visitor-cookies-manager'),
            'is_mobile'     => __('Est Mobile', 'visitor-cookies-manager'),
            'visit_date'    => __('Date de visite', 'visitor-cookies-manager')
        ];
        return $columns;
    }

    public function get_sortable_columns() {
        $sortable_columns = [
            'visit_date'  => ['visit_date', true],
            'ip_address'  => ['ip_address', false],
            'device_type' => ['device_type', false]
        ];
        return $sortable_columns;
    }

    public function get_device_type($user_agent) {
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

    public function prepare_items() {
        // Paramètres de pagination
        $per_page = $this->get_items_per_page('visitors_per_page', 20);
        $current_page = $this->get_pagenum();

        // Récupération des données filtrées et paginées
        $args = [
            'per_page' => $per_page,
            'page'     => $current_page,
            'search'   => isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '',
            'device_type' => isset($_GET['device_type']) ? sanitize_text_field($_GET['device_type']) : '',
            'start_date'  => isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '',
            'end_date'    => isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : ''
        ];

        $this->visitors_data = $this->get_visitors_data($args);

        // Configuration de la pagination
        $this->set_pagination_args([
            'total_items' => $this->visitors_data['total_count'],
            'per_page'    => $per_page,
            'total_pages' => ceil($this->visitors_data['total_count'] / $per_page)
        ]);

        $this->_column_headers = [
            $this->get_columns(), 
            [], 
            $this->get_sortable_columns()
        ];

        $this->items = $this->visitors_data['data'];
    }

    public function get_visitors_data($args) {
        global $wpdb;
        
        // Votre logique de requête SQL ici
        // Cette méthode devrait retourner un tableau avec 'data' et 'total_count'
        // Exemple simplifié, à personnaliser selon votre structure de base de données
        $query = "SELECT * FROM wp_visitor_cookies WHERE 1=1 ";
        
        // Ajoutez les filtres de recherche ici
        if (!empty($args['search'])) {
            $query .= $wpdb->prepare(" AND (user_agent LIKE %s OR ip_address LIKE %s)", 
                '%' . $wpdb->esc_like($args['search']) . '%',
                '%' . $wpdb->esc_like($args['search']) . '%'
            );
        }

        // Ajoutez d'autres filtres si nécessaire

        $query .= " LIMIT " . $args['per_page'] . " OFFSET " . (($args['page'] - 1) * $args['per_page']);

        $results = $wpdb->get_results($query, ARRAY_A);

        return [
            'data' => $results,
            'total_count' => $wpdb->get_var("SELECT COUNT(*) FROM wp_visitor_cookies")
        ];
    }

    public function column_default($item, $column_name) {
        switch($column_name) {
            case 'user_agent':
                return esc_html($item['user_agent']);
            case 'device_type':
                return esc_html($this->get_device_type($item['user_agent']));
            case 'ip_address':
                return esc_html($item['ip_address']);
            case 'device_detail':
                return esc_html($item['device_type']);
            case 'is_mobile':
                return $item['is_mobile'] ? __('Oui', 'visitor-cookies-manager') : __('Non', 'visitor-cookies-manager');
            case 'visit_date':
                return esc_html($item['visit_date']);
            default:
                return print_r($item, true);
        }
    }

    public function no_items() {
        _e('Aucune donnée trouvée.', 'visitor-cookies-manager');
    }
}

// Fonction pour rendre la page d'administration
function vcm_render_visitors_page() {
    // Créer une instance de la table
    $wp_list_table = new VCM_Visitors_List_Table();
    
    // Traiter les actions si nécessaire
    $wp_list_table->prepare_items();
    
    // Afficher la page ?>
    <div class="wrap">
        <h1><?php _e('Visitor Cookies Manager', 'visitor-cookies-manager'); ?></h1>
        
        <form method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>" />
            <?php 
            $wp_list_table->search_box(__('Rechercher', 'visitor-cookies-manager'), 'search_visitors'); 
            $wp_list_table->display(); 
            ?>
        </form>
    </div>
    <?php
}



