<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Model\ResourceModel\Token;

use MageRocket\GoCuotas\Model\Token;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Init Construct
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            Token::class,
            \MageRocket\GoCuotas\Model\ResourceModel\Token::class
        );
    }
}
