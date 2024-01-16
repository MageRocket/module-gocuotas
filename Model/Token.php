<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Model;

use MageRocket\GoCuotas\Api\Data\TokenInterface;
use Magento\Framework\Model\AbstractModel;

class Token extends AbstractModel implements TokenInterface
{
    /**
     * Init Construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModel\Token::class);
    }

    /**
     * Get StoreId
     *
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set StoreId
     *
     * @param int $storeId
     * @return mixed
     */
    public function setStoreId(int $storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Get ExpiredAt
     *
     * @return mixed
     */
    public function getExpiredAt()
    {
        return $this->getData(self::EXPIRED_AT);
    }

    /**
     * Set ExpiredAt
     *
     * @param string $expiredAt
     * @return mixed
     */
    public function setExpiredAt(string $expiredAt)
    {
        return $this->setData(self::EXPIRED_AT, $expiredAt);
    }

    /**
     * Get Token
     *
     * @return mixed
     */
    public function getToken()
    {
        return $this->getData(self::TOKEN);
    }

    /**
     * Set Token
     *
     * @param string $token
     * @return mixed
     */
    public function setToken(string $token)
    {
        return $this->setData(self::TOKEN, $token);
    }

    /**
     * Get TokenId
     *
     * @return mixed
     */
    public function getTokenId()
    {
        return $this->getData(self::TOKEN_ID);
    }

    /**
     * Set TokenId
     *
     * @param int $tokenId
     * @return mixed
     */
    public function setTokenId(int $tokenId)
    {
        return $this->setData(self::TOKEN_ID, $tokenId);
    }
}
