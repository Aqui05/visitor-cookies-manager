jQuery(document).ready(function($) {
    var $consentBar = $('.vcm-cookie-consent-bar');
    console.log("Barre de consentement initialisée.");

    // Fonction pour récupérer un cookie
    function getCookie(name) {
        var cookie = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
        return cookie ? cookie[2] : null;
    }

    // Fonction pour vérifier le consentement
    function checkConsent() {
        var cookieConsent = getCookie('vcm_cookie_consent') || localStorage.getItem('vcm_cookie_consent');
        console.log("Valeur du consentement :", cookieConsent);

        if (cookieConsent === 'accepted') {
            $consentBar.hide(); // Masquer la barre si accepté
        } else if (cookieConsent === 'refused' || !cookieConsent) {
            $consentBar.show(); // Afficher si refusé ou aucune valeur
        }
    }

    // Appeler checkConsent au chargement de la page
    checkConsent();

    // Gestion du bouton Accepter
    $('#vcm-accept-cookies').on('click', function() {
        sendCookieConsent('accepted');
    });

    // Gestion du bouton Refuser
    $('#vcm-refuse-cookies').on('click', function() {
        sendCookieConsent('refused');
    });

    // Fonction pour envoyer le consentement
    function sendCookieConsent(consent) {
        console.log("Envoi du consentement: " + consent);

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
                    console.log("Consentement enregistré avec succès.");

                    // Masquer la barre de consentement
                    $consentBar.hide();

                    // Mettre à jour localStorage
                    localStorage.setItem('vcm_cookie_consent', consent);
                    console.log(consent)

                    // Recharger si le consentement est accepté
                    if (consent === 'accepted') {
                        location.reload();
                    }
                } else {
                    console.error("Erreur dans la réponse: ", response);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("Erreur Ajax: ", textStatus, errorThrown);
            }
        });
    }
});
