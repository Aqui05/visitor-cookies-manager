<?php
// Vérifier que ce fichier est appelé directement
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1><?php _e('Paramètres de Visitor Cookies Manager', 'visitor-cookies-manager'); ?></h1>

    <form method="post" action="options.php">
        <?php
        // Afficher les champs de sécurité pour les options
        settings_fields('vcm_settings_group');
        do_settings_sections('vcm_settings_group');
        ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="vcm_cookie_consent_enabled">
                        <?php _e('Activer le bandeau de consentement', 'visitor-cookies-manager'); ?>
                    </label>
                </th>
                <td>
                    <input type="checkbox" 
                           id="vcm_cookie_consent_enabled" 
                           name="vcm_cookie_consent_enabled" 
                           value="1" 
                           <?php checked(1, get_option('vcm_cookie_consent_enabled'), true); ?>
                    />
                    <p class="description">
                        <?php _e('Afficher la barre de consentement des cookies sur le site.', 'visitor-cookies-manager'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="vcm_data_retention_days">
                        <?php _e('Durée de conservation des données', 'visitor-cookies-manager'); ?>
                    </label>
                </th>
                <td>
                    <input type="number" 
                           id="vcm_data_retention_days" 
                           name="vcm_data_retention_days" 
                           value="<?php echo esc_attr(get_option('vcm_data_retention_days', 30)); ?>" 
                           min="1" 
                           max="365"
                    />
                    <p class="description">
                        <?php _e('Nombre de jours pendant lesquels les données de visiteurs seront conservées.', 'visitor-cookies-manager'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="vcm_cookie_message">
                        <?php _e('Message de consentement', 'visitor-cookies-manager'); ?>
                    </label>
                </th>
                <td>
                    <textarea 
                        id="vcm_cookie_message" 
                        name="vcm_cookie_message" 
                        rows="3" 
                        class="large-text"
                    ><?php echo esc_textarea(get_option('vcm_cookie_message', __('Ce site utilise des cookies pour améliorer votre expérience de navigation.', 'visitor-cookies-manager'))); ?></textarea>
                    <p class="description">
                        <?php _e('Personnalisez le message affiché dans le bandeau de consentement.', 'visitor-cookies-manager'); ?>
                    </p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Validation dynamique
    $('input[name="vcm_data_retention_days"]').on('change', function() {
        var value = parseInt($(this).val());
        if (value < 1 || value > 365) {
            alert('<?php _e('La durée de conservation doit être entre 1 et 365 jours.', 'visitor-cookies-manager'); ?>');
            $(this).val(30);
        }
    });
});
</script>