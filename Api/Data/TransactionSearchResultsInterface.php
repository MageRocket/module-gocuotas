<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface TransactionSearchResultsInterface extends SearchResultsInterface
{

    /**
     * Get Go Cuotas Transaction list.
     *
     * @return TransactionInterface[]
     */
    public function getItems();

    /**
     * Set Go Cuotas Transaction list.
     *
     * @param TransactionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
