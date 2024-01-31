<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Api\Data;

interface TokenInterface
{
    public const TOKEN_ID = 'entity_id';
    public const STORE_ID = 'store_id';
    public const EXPIRED_AT = 'expired_at';
    public const TOKEN = 'token';

    /**
     * Get TokenId
     *
     * @return mixed
     */
    public function getTokenId();

    /**
     * Set TokenId
     *
     * @param int $tokenId
     * @return mixed
     */
    public function setTokenId(int $tokenId);

    /**
     * Get StoreId
     *
     * @return mixed
     */
    public function getStoreId();

    /**
     * Set StoreId
     *
     * @param int $storeId
     * @return mixed
     */
    public function setStoreId(int $storeId);

    /**
     * Get ExpiredAt
     *
     * @return mixed
     */
    public function getExpiredAt();

    /**
     * Set ExpiredAt
     *
     * @param string $expiredAt
     * @return mixed
     */
    public function setExpiredAt(string $expiredAt);

    /**
     * Get Token
     *
     * @return mixed
     */
    public function getToken();

    /**
     * Set Token
     *
     * @param string $token
     * @return mixed
     */
    public function setToken(string $token);
}
