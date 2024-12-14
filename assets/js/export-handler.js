jQuery(document).ready(function($) {
    const exportHandler = {
        init: function() {
            $('.tablenav-top .button#doaction, .tablenav-bottom .button#doaction').on('click', this.handleExport);
        },

        handleExport: function(e) {
            const $form = $(this).closest('form');
            const action = $form.find('select[name="action"]').val();
            
            if (action !== 'export_selected') return;

            e.preventDefault();
            
            const checkedBoxes = $('input[name="visitors_ids[]"]:checked');
            if (checkedBoxes.length === 0) {
                alert('Veuillez sélectionner au moins un élément à exporter.');
                return;
            }

            const selectedIds = checkedBoxes.map(function() {
                return this.value;
            }).get();

            exportHandler.initiateExport(selectedIds);
        },

        initiateExport: function(selectedIds) {
            $.ajax({
                url: vcmExport.ajax_url,
                type: 'POST',
                data: {
                    action: 'vcm_generate_export', 
                    security: vcmExport.nonce,
                    selected_ids: selectedIds
                },
                success: function(response) {
                    if (response.success && response.data.download_url) {
                        window.location.href = response.data.download_url;
                    } else {
                        alert(response.data);
                    }
                },
                error: function() {
                    alert('Erreur lors de l\'exportation.');
                }
            });
        }
    };

    exportHandler.init();
});