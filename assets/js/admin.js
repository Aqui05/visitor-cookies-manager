jQuery(document).ready(function($) {
    // Bouton d'exportation
    $('#vcm-export-btn').on('click', function() {
        var searchParam = $('input[name="s"]').val();
        var deviceTypeParam = $('select[name="device_type"]').val();
        var startDateParam = $('input[name="start_date"]').val();
        var endDateParam = $('input[name="end_date"]').val();

        var exportUrl = vcmAdminData.ajaxurl + 
            '?action=vcm_export_cookies' + 
            '&search=' + encodeURIComponent(searchParam) +
            '&device_type=' + encodeURIComponent(deviceTypeParam) +
            '&start_date=' + encodeURIComponent(startDateParam) +
            '&end_date=' + encodeURIComponent(endDateParam) +
            '&_wpnonce=' + vcmAdminData.nonce;

        window.location.href = exportUrl;
    });
});