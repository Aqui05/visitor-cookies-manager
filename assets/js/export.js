(function ($) {
    const vcmExport = {
        init: function () {
            $.ajax({
                url: vcmExport.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'vcm_export_selected_cookies',
                    security: vcmExport.nonce,
                    selected_ids: vcmExport.selectedIds,
                },
                success: function (response) {
                    if (response.success) {
                        // Trigger file download
                        const a = document.createElement('a');
                        a.href = 'data:text/csv;base64,' + response.data.csv_content;
                        a.download = response.data.filename;
                        a.click();
                    } else {
                        alert(response.data.message || 'Une erreur est survenue.');
                    }
                },
                error: function () {
                    alert('Une erreur de communication est survenue.');
                },
            });
        },
    };

    window.vcmExport = vcmExport;
})(jQuery);
