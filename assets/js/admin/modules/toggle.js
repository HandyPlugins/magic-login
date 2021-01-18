/* global jQuery, MagicLogin  */
/* eslint-disable */

(function ($) {
    // Use strict mode

    // Define global MagicLogin object if it does not exist
    if (typeof window.MagicLogin !== 'object') {
        window.MagicLogin = {};
    }

    MagicLogin.pageToggles = function (page) {
        const body = $('body');
        if (!body.hasClass('settings_page_magic-login')) {
            return;
        }

        function showSettings(element) {
            const settings = $(`#${element.attr('aria-controls')}`);

            element.on('change', function () {
                if (element.is(':checked')) {
                    settings.show();
                } else {
                    settings.hide();
                }
            });
        }

        function init() {
            const toggles = $('.sui-toggle input[type="checkbox"]');

            toggles.each(function () {
                const toggle = $(this);

                if (undefined !== toggle.attr('aria-controls')) {
                    showSettings(toggle);
                }
            });
        }

        init();

        return this;
    };

    $('body').ready(function () {
        MagicLogin.pageToggles('toggles');
    });
})(jQuery);

/* eslint-enable */
