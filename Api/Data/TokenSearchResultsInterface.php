<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface TokenSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Items
     *
     * @return mixed
     */
    public function getItems();

    /**
     * Set Items
     *
     * @param array $items
     * @return mixed
     */
    public function setItems(array $items);
}
