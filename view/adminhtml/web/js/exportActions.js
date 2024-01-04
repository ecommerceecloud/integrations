define([
    "jquery",
    "Magento_Ui/js/modal/alert",
    "mage/translate",
    "jquery/ui"
], function ($, alert, $t) {
    // "use strict";


    $.widget('ecloudintegrations.exportActions', {
        _create: function () {
            var self = this;

            this.element.click(function (e) {
                e.preventDefault();
                self._ajaxSubmit();
            });
        },

        _ajaxSubmit: function () {
            $.ajax({
                url: this.options.ajaxUrl,
                showLoader: true,
                success: function (result) {
                    alert({
                        title: result.success ? $t('Success') : $t('Error'),
                        content: result.error
                    });
                },
                error: function(error) {
                    alert({
                        title: $t('Error'),
                        content: "Error inesperado, reintente más tarde"
                    });
                }
            });
        }
    });

    return $.ecloudintegrations.exportActions;
});
