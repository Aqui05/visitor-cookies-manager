<?php
// Vérifier que ce fichier est appelé directement
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap vcm-admin-page">
    <h1><?php _e('Visitor Cookies Manager', 'visitor-cookies-manager'); ?></h1>

    <form method="get" class="vcm-filter-form">
        <input type="hidden" name="page" value="visitor-cookies-manager">
        
        <div class="vcm-filters">
            <input type="text" name="s" placeholder="<?php _e('Rechercher...', 'visitor-cookies-manager'); ?>" 
                   value="<?php echo esc_attr($search); ?>">
            
            <select name="device_type">
                <option value=""><?php _e('Tous les appareils', 'visitor-cookies-manager'); ?></option>
                <option value="desktop" <?php selected($device_type, 'desktop'); ?>><?php _e('Desktop', 'visitor-cookies-manager'); ?></option>
                <option value="mobile" <?php selected($device_type, 'mobile'); ?>><?php _e('Mobile', 'visitor-cookies-manager'); ?></option>
                <option value="tablet" <?php selected($device_type, 'tablet'); ?>><?php _e('Tablette', 'visitor-cookies-manager'); ?></option>
            </select>

            <input type="date" name="start_date" value="<?php echo esc_attr($start_date); ?>">
            <input type="date" name="end_date" value="<?php echo esc_attr($end_date); ?>">

            <input type="submit" value="<?php _e('Filtrer', 'visitor-cookies-manager'); ?>" class="button">
        </div>
    </form>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('ID', 'visitor-cookies-manager'); ?></th>
                <th><?php _e('Adresse IP', 'visitor-cookies-manager'); ?></th>
                <th><?php _e('Type d\'appareil', 'visitor-cookies-manager'); ?></th>
                <th><?php _e('Est Mobile', 'visitor-cookies-manager'); ?></th>
                <th><?php _e('Date de visite', 'visitor-cookies-manager'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($visitors_data['data'])) : ?>
                <?php foreach ($visitors_data['data'] as $visitor) : ?>
                    <tr>
                        <td><?php echo esc_html($visitor['id']); ?></td>
                        <td><?php echo esc_html($visitor['ip_address']); ?></td>
                        <td><?php echo esc_html($visitor['device_type']); ?></td>
                        <td><?php echo $visitor['is_mobile'] ? __('Oui', 'visitor-cookies-manager') : __('Non', 'visitor-cookies-manager'); ?></td>
                        <td><?php echo esc_html($visitor['visit_date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5"><?php _e('Aucune donnée trouvée.', 'visitor-cookies-manager'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="vcm-pagination">
        <?php
        $total_pages = $visitors_data['total_pages'];
        $current_page = $page;

        if ($total_pages > 1) {
            echo paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'current' => $current_page,
                'total' => $total_pages,
                'prev_text' => __('&laquo; Précédent', 'visitor-cookies-manager'),
                'next_text' => __('Suivant &raquo;', 'visitor-cookies-manager')
            ));
        }
        ?>
    </div>

    <div class="vcm-export-section">
        <button id="vcm-export-btn" class="button button-primary">
            <?php _e('Exporter en CSV', 'visitor-cookies-manager'); ?>
        </button>
    </div>
</div>