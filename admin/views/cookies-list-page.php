<?php
// Sécurité : bloquer l'accès direct au fichier
if (!defined('ABSPATH')) {
    exit;
}

// Créer une instance de la liste
$visitors_list_table = new VCM_Visitors_List_Table();
$visitors_list_table->process_bulk_action();

// Add nonce field for security
wp_nonce_field('bulk-visitors');


$visitors_list_table->process_bulk_action();
?>

<div class="wrap vcm-admin-page">
        <h1><?php _e('Visitor Cookies Manager', 'visitor-cookies-manager'); ?></h1>

        <form method="get">
            <input type="hidden" name="page" value="visitor-cookies-manager">
            
            <div class="vcm-filters">
                <input type="date" name="start_date" value="<?php echo esc_attr(isset($_GET['start_date']) ? $_GET['start_date'] : ''); ?>">
                <input type="date" name="end_date" value="<?php echo esc_attr(isset($_GET['end_date']) ? $_GET['end_date'] : ''); ?>">
                <input type="submit" value="<?php _e('Filtrer', 'visitor-cookies-manager'); ?>" class="button">
            </div>

            <p class="search-box">
                <input type="text" name="s" placeholder="<?php esc_attr_e('Rechercher...', 'visitor-cookies-manager'); ?>" value="<?php echo esc_attr($_GET['s'] ?? ''); ?>">
                <input type="submit" class="button" value="<?php esc_attr_e('Rechercher', 'visitor-cookies-manager'); ?>">
            </p>
        </form>

        <form method="get">
            <input type="hidden" name="page" value="visitor-cookies-manager">
            <?php 
                $visitors_list_table->prepare_items();
                $visitors_list_table->display(); 
            ?>
        </form>
    </div>



?>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Gérer le clic sur le bouton "Exporter la sélection"
    $('form').on('submit', function(e) {
        if ($('select[name="action"]').val() === 'export_selected') {
            e.preventDefault();

            // Récupérer les IDs sélectionnés
            let selectedIds = [];
            $('input[name="visitors_ids[]"]:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                alert('Veuillez sélectionner des éléments à exporter.');
                return;
            }

            // Envoyer la requête AJAX
            $.ajax({
                url: ajaxurl, // WordPress AJAX URL
                method: 'POST',
                data: {
                    action: 'vcm_export_csv',
                    visitors_ids: selectedIds,
                },
                xhrFields: {
                    responseType: 'blob' // Important pour gérer le téléchargement
                },
                success: function(response, status, xhr) {
                    // Créer un fichier temporaire pour le téléchargement
                    const blob = new Blob([response], { type: 'text/csv' });
                    const downloadUrl = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = 'visitor_cookies_export_' + new Date().toISOString() + '.csv';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                },
                error: function() {
                    alert('Une erreur s\'est produite lors de l\'exportation des données.');
                }
            });
        }
    });
});
</script>

