jQuery(document).ready(function($) {
    var $consentBar = $('#vcm-cookie-consent-bar');

    // Bouton Accepter
    $('#vcm-accept-cookies').on('click', function() {
        sendCookieConsent('accepted');
    });

    // Bouton Refuser
    $('#vcm-refuse-cookies').on('click', function() {
        sendCookieConsent('refused');
    });

    function sendCookieConsent(consent) {
        $.ajax({
            url: vcmCookieData.ajaxurl,
            type: 'POST',
            data: {
                action: 'vcm_set_cookie_consent',
                consent: consent,
                security: vcmCookieData.nonce
            },
            success: function(response) {
                if (response.success) {
                    $consentBar.fadeOut(300);
                }
            }
        });
    }
});