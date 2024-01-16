<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Api;

use MageRocket\GoCuotas\Api\Data\TransactionInterface;
use MageRocket\GoCuotas\Api\Data\TransactionSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface TransactionRepositoryInterface
{

    /**
     * Save Transaction
     *
     * @param TransactionInterface $transaction
     * @return TransactionInterface
     * @throws LocalizedException
     */
    public function save(TransactionInterface $transaction);

    /**
     * Retrieve Transaction
     *
     * @param string $entityId
     * @return TransactionInterface
     * @throws LocalizedException
     */
    public function get($entityId);

    /**
     * Retrieve Transaction
     *
     * @param int $transactionId
     * @return TransactionInterface | false
     * @throws LocalizedException
     */
    public function getByTransactionId($transactionId);

    /**
     * Retrieve Transaction
     *
     * @param int $orderId
     * @return TransactionInterface | false
     * @throws LocalizedException
     */
    public function getByOrderId($orderId);

    /**
     * Retrieve Transaction
     *
     * @param int $incrementId
     * @return TransactionInterface | false
     * @throws LocalizedException
     */
    public function getByOrderIncrementId($incrementId);

    /**
     * Retrieve Transaction matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return TransactionSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Transaction
     *
     * @param TransactionInterface $entity
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(TransactionInterface $entity);

    /**
     * DeleteById
     *
     * @param string $entityId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($entityId);
}
