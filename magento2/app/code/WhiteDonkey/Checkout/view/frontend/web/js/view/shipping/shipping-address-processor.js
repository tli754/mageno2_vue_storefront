define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rate-service',
        'WhiteDonkey_Checkout/js/model/custom-shipping-rate-processor',
        'Magento_Checkout/js/model/shipping-save-processor',
        'WhiteDonkey_Checkout/js/model/custom-shipping-save-processor'
    ],
    function (
        Component,
        shippingRateService,
        customShippingRateProcessor,
        shippingSaveProcessor,
        customShippingSaveProcessor
    ) {
        'use strict';

        /** Register rate processor */
        shippingRateService.registerProcessor('customShippingProcessor', customShippingRateProcessor);

        /** Register save shipping address processor */
        shippingSaveProcessor.registerProcessor('customShippingProcessor', customShippingSaveProcessor);

        /** Add view logic here if needed */
        return Component.extend({});
    }
);
