<?php
// Sécurité : bloquer l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

// Créer une instance de la liste
$visitors_list_table = new VCM_Visitors_List_Table();
?>

<div class="wrap vcm-admin-page">
    <h1><?php _e('Visitor Cookies Manager', 'visitor-cookies-manager'); ?></h1>

    <form method="get">
        <input type="hidden" name="page" value="visitor-cookies-manager">
        
        <div class="vcm-filters">
            <input type="text" name="s" placeholder="<?php _e('Rechercher...', 'visitor-cookies-manager'); ?>"
                   value="<?php echo esc_attr(isset($_GET['s']) ? $_GET['s'] : ''); ?>">
            
            <select name="device_type">
                <option value=""><?php _e('Tous les appareils', 'visitor-cookies-manager'); ?></option>
                <option value="desktop" <?php selected(isset($_GET['device_type']) ? $_GET['device_type'] : '', 'desktop'); ?>><?php _e('Desktop', 'visitor-cookies-manager'); ?></option>
                <option value="mobile" <?php selected(isset($_GET['device_type']) ? $_GET['device_type'] : '', 'mobile'); ?>><?php _e('Mobile', 'visitor-cookies-manager'); ?></option>
                <option value="tablet" <?php selected(isset($_GET['device_type']) ? $_GET['device_type'] : '', 'tablet'); ?>><?php _e('Tablette', 'visitor-cookies-manager'); ?></option>
            </select>

            <input type="date" name="start_date" value="<?php echo esc_attr(isset($_GET['start_date']) ? $_GET['start_date'] : ''); ?>">
            <input type="date" name="end_date" value="<?php echo esc_attr(isset($_GET['end_date']) ? $_GET['end_date'] : ''); ?>">

            <input type="submit" value="<?php _e('Filtrer', 'visitor-cookies-manager'); ?>" class="button">
        </div>
    </form>


    <form method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
        <input type="hidden" name="action" value="vcm_export_cookies">
        <?php 
            $visitors_list_table->prepare_items();
            $visitors_list_table->display(); 
        ?>
        <input type="submit" class="button button-primary" value="<?php _e('Exporter les sélectionnés', 'visitor-cookies-manager'); ?>">
    </form>

<!--
        <div class="vcm-export-section">
            <button id="vcm-export-btn" class="button button-primary">
                <?php _e('Exporter en CSV', 'visitor-cookies-manager'); ?>
            </button>
        </div>
-->
</div>