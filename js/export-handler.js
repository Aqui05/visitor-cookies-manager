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
                alert('Please select at least one item to export.');
                return;
            }

            // Direct form submission for export
            $form.append('<input type="hidden" name="_wpnonce" value="' + vcmExport.nonce + '">');
            $form.submit();
        }
    };

    exportHandler.init();
});