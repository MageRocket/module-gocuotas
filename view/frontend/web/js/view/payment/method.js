/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        let type = 'gocuotas';
        if(window.checkoutConfig.payment[type].payment_active) {
            rendererList.push(
                {
                    type: type,
                    component: 'MageRocket_GoCuotas/js/view/payment/method-renderer/method'
                }
            );
        }
        return Component.extend({});
    }
);
