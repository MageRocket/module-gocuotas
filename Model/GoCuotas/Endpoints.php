<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Model\GoCuotas;

class Endpoints
{
    public const MODAL_SUCCESS = '/checkout/iframe_success/';
    public const MODAL_FAILURE = '/checkout/iframe_fail/';
    public const API_PATH = '/api_redirect';
    public const API_VERSION = '/v1';
    public const CREATE_PAYMENT = '/checkouts';
    public const CREATE_REFUND = '/orders/%s';
    public const GET_ORDERS = '/orders?delivered_start=%s&delivered_end=%s';
    public const AUTHENTICATION = '/authentication';
}
