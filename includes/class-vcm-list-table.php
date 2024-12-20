<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class VCM_Visitors_List_Table extends WP_List_Table {
    private $data_collector;

    public function __construct() {
        parent::__construct([
            'singular' => 'visiteur',
            'plural'   => 'visiteurs',
            'ajax'     => false
        ]);

        $this->data_collector = VCM_Data_Collector::get_instance();
    }

    public function get_columns() {
        return [
            'cb'            => '<input type="checkbox" />',
            'user_agent'    => __('User Agent', 'visitor-cookies-manager'),
            'device_type'   => __('Type de terminal', 'visitor-cookies-manager'),
            'ip_address'    => __('Adresse IP', 'visitor-cookies-manager'),
            'is_mobile'     => __('Est Mobile', 'visitor-cookies-manager'),
            'visit_date'    => __('Date de visite', 'visitor-cookies-manager')
        ];
    }

    public function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="visitors_ids[]" value="%d" />',
            intval($item['id'])
        );
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'user_agent':
                return esc_html($item['user_agent']);
            case 'device_type':
                return esc_html($item['device_type']);
            case 'ip_address':
                return esc_html($item['ip_address']);
            case 'is_mobile':
                return $item['is_mobile'] ? __('Oui', 'visitor-cookies-manager') : __('Non', 'visitor-cookies-manager');
            case 'visit_date':
                return wp_date(get_option('date_format'), strtotime($item['visit_date']));
            default:
                return esc_html(print_r($item, true));
        }
    }

    private function get_device_type($user_agent) {
        if (preg_match('/mobile/i', $user_agent)) {
            return __('Mobile', 'visitor-cookies-manager');
        } elseif (preg_match('/tablet|ipad/i', $user_agent)) {
            return __('Tablette', 'visitor-cookies-manager');
        } elseif (preg_match('/linux|windows|macintosh|x11/i', $user_agent)) {
            return __('Desktop', 'visitor-cookies-manager');
        } else {
            return __('Inconnu', 'visitor-cookies-manager');
        }
    }

    public function prepare_items() {
        $search      = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $start_date  = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
        $end_date    = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';
        $device_type = isset($_GET['device_type']) ? sanitize_text_field($_GET['device_type']) : '';

        $per_page     = 20;
        $current_page = $this->get_pagenum();

        $args = [
            'page'        => $current_page,
            'per_page'    => $per_page,
            'search'      => $search,
            'device_type' => $device_type,
            'start_date'  => $start_date,
            'end_date'    => $end_date
        ];

        $data = $this->data_collector->get_visitors_data($args);

        $this->_column_headers = [$this->get_columns(), [], []];

        $this->items = $data['data'] ?? [];

        $this->set_pagination_args([
            'total_items' => $data['total_items'] ?? 0,
            'per_page'    => $per_page,
            'total_pages' => $data['total_pages'] ?? 1
        ]);
    }

    public function get_bulk_actions() {
        return [
            'export_selected' => __('Exporter la sélection', 'visitor-cookies-manager')
        ];
    }


    /*public function process_bulk_action() {
        if ($this->current_action() === 'export_selected') {
            // Verify nonce
            //check_admin_referer('bulk-visitors');

        }
    }*/
    
}



