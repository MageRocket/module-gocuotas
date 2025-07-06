/**
 * @author MageRocket
 * @copyright Copyright (c) 2025 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

window.addEventListener('message', (event) => {
    'use strict';
    if(event.data === "successIframe"){
        window.location.href = '/checkout/onepage/success';
    }

    if (event.data === "failIframe")
    {
        window.location.href = '/checkout/onepage/failure';
    }
});
