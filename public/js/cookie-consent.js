jQuery(document).ready(function($) {
    var $consentBar = $('#vcm-cookie-consent-bar');
    console.log("Barre de consentement initialisée.");

    // Vérifier si le consentement a déjà été donné
    function checkConsent() {
        // Vérifier d'abord si un cookie de consentement existe
        if (document.cookie.indexOf('vcm_cookie_consent=') !== -1) {
            $consentBar.hide();
            return;
        }

        // Si aucun consentement n'est trouvé, afficher la bannière
        $consentBar.show();
    }

    // Appeler checkConsent au chargement de la page
    checkConsent();

    // Bouton Accepter
    $('#vcm-accept-cookies').on('click', function() {
        console.log("Bouton accepter cliqué.");
        sendCookieConsent('accepted');
    });

    // Bouton Refuser
    $('#vcm-refuse-cookies').on('click', function() {
        console.log("Bouton refuser cliqué.");
        sendCookieConsent('refused');
    });

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
                    console.log("Fermeture de la barre de consentement");
                    
                    // Masquer immédiatement la bannière
                    $consentBar.hide();
                    
                    // Enregistrer le choix dans le localStorage
                    localStorage.setItem('vcm_cookie_consent', consent);
                } else {
                    console.log("Erreur dans la réponse: ", response);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log("Erreur Ajax: ", textStatus, errorThrown);
            }
        });
    }
});