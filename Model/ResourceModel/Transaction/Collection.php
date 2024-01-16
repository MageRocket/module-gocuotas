<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Model\ResourceModel\Transaction;

use MageRocket\GoCuotas\Model\Transaction;
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
            Transaction::class,
            \MageRocket\GoCuotas\Model\ResourceModel\Transaction::class
        );
    }
}
