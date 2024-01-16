<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Transaction extends AbstractDb
{
    /**
     * Init Construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('gocuotas_transaction', 'entity_id');
    }
}
