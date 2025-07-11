/**
 * @author MageRocket
 * @copyright Copyright (c) 2025 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

define([
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Ui/js/modal/modal',
    'jquery',
    'observerModal'
], function (
    Component,
    quote,
    additionalValidators,
    placeOrderAction,
    fullScreenLoader,
    modal,
    $
) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'MageRocket_GoCuotas/payment/method',
            code: 'gocuotas',
            active: false
        },

        getCode: function () {
            return this.code;
        },

        getTitle: function () {
            return window.checkoutConfig.payment[this.getCode()].payment_title;
        },

        getDescription: function () {
            return window.checkoutConfig.payment[this.getCode()].payment_description;
        },

        getShowCheckoutBanner: function () {
            return window.checkoutConfig.payment[this.getCode()].payment_showBanner;
        },

        getBanner: function () {
            return window.checkoutConfig.payment[this.getCode()].payment_banner;
        },

        getLogo: function () {
            return window.checkoutConfig.payment[this.getCode()].payment_logo;
        },

        getInstructions: function () {
            return window.checkoutConfig.payment[this.getCode()].payment_instructions;
        },

        getInstructionIcon: function () {
            return window.checkoutConfig.payment[this.getCode()].payment_icon;
        },

        getPaymentMode: function () {
            return window.checkoutConfig.payment[this.getCode()].payment_mode;
        },

        getFailureURL: function () {
            return window.checkoutConfig.payment[this.getCode()].payment_checkout_failure;
        },

        getSuccessURL: function () {
            return window.checkoutConfig.payment[this.getCode()].payment_checkout_success;
        },


        afterPlaceOrder: function () {
            fullScreenLoader.startLoader();
            let self = this;
            let url = window.checkoutConfig.payment[this.getCode()].payment_url;
            self.createGoCuotasTransaction(url);
        },

        placeOrder: function (data, event) {
            let self = this;
            if (event) {
                event.preventDefault();
            }
            if (this.validate() && additionalValidators.validate()) {
                this.isPlaceOrderActionAllowed(false);
                this.getPlaceOrderDeferredObject()
                    .fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    })
                    .done(function () {
                        self.afterPlaceOrder();
                    })
                    .always(function () {
                        self.isPlaceOrderActionAllowed(true);
                    });
                return true;
            }
            return false;
        },

        getPlaceOrderDeferredObject: function () {
            $('button.checkout').attr('disabled', 'disabled');
            return $.when(
                placeOrderAction(this.getData(), this.messageContainer)
            );
        },

        /**
         * createGoCuotasTransaction
         *
         * @param serviceUrl
         */
        createGoCuotasTransaction: function (serviceUrl) {
            let self = this;
            var transactionAjaxConfig = {
                url: serviceUrl,
                contentType: 'application/json',
                type: 'GET'
            }
            let forceRedirect = self.detectBrowser() === 'sa';
            if(forceRedirect){
                transactionAjaxConfig.data = {
                    forceRedirect: forceRedirect
                };
            }
            $.ajax(transactionAjaxConfig).done(function (response) {
                if (response.length <= 0) {
                    window.location.href = '/checkout/onepage/failure';
                    return;
                }
                
                if (response.error) {
                    window.location.href = response.failure_url;
                    return;
                }

                // Force Redirect Mode on Mobile / Tablet
                if (self.getPaymentMode() && !self.detectDevice() && !forceRedirect) {
                    // Modal
                    self.createModal(response);
                } else {
                    // Redirect
                    window.location.href = response.init_url;
                }
            }).fail(function (error) {
                window.location.href = '/checkout/onepage/failure';
                return;
            });
        },

        /**
         * createModal
         *
         * Open GoCuotas Modal
         * @param response
         */
        createModal: function (response) {
            let goCuotas_url = response.init_url;
            let goCuotas_cancel = response.cancel_url;
            // Append Content
            let modalContent = `<div id="gocuotas-content"><iframe src="${goCuotas_url}" frameborder="0"></iframe></div>`;
            $('body').append(modalContent);
            // Modal Options
            let options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                clickableOverlay: false,
                modalClass: 'gocuotas-modal',
                buttons: [{
                    text: $.mage.__('Cancel Payment'),
                    class: 'close-popup btn-gocuotas btn-cancel go-bold',
                    click: function () {
                        window.location.href = goCuotas_cancel;
                    }
                }],
                keyEventHandlers: {escapeKey: function () {
                    return;
                }}
            };
            // Instance Modal and Open
            let goCuotasModal = modal(options, $('#gocuotas-content'));
            $('#gocuotas-content').modal('openModal');
            fullScreenLoader.stopLoader();

            // After X minutes, redirect to the cancellation page
            let paymentTimeOutMinutes = 28;
            setTimeout(function () {
                window.location.href = goCuotas_cancel + 'timeout/t';
            }, paymentTimeOutMinutes * 60 * 1000);
        },

        /**
         * Detect Device
         * @returns {boolean}
         */
        detectDevice: function () {
            var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
            var breakpoints = {
                desktop: 1024,
                tablet: 768,
                mobile: 0
            };
            return screenWidth < breakpoints.desktop;
        },

        /**
         * Detect Browser Agent
         * @returns {string}
         */
        detectBrowser: function () {
            let userAgent = navigator.userAgent;
            let userAgentName = "";
            if (userAgent.match(/chrome|chromium|crios/i)) {
                userAgentName = 'gc';
            } else if (userAgent.match(/firefox|fxios/i)) {
                userAgentName = 'mf';
            } else if (userAgent.match(/safari/i)) {
                userAgentName = 'sa';
            } else if (userAgent.match(/opr\//i)) {
                userAgentName = 'op';
            } else if (userAgent.match(/edg/i)) {
                userAgentName = 'ed';
            } else if (userAgent.match(/trident/i)) {
                userAgentName = 'ie';
            } else {
                userAgentName = 'ot';
            }
            return userAgentName;
        }
    });
});
