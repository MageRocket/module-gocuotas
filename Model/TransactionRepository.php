<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Model;

use MageRocket\GoCuotas\Api\Data\TransactionInterface;
use MageRocket\GoCuotas\Api\Data\TransactionInterfaceFactory;
use MageRocket\GoCuotas\Api\Data\TransactionSearchResultsInterface;
use MageRocket\GoCuotas\Api\Data\TransactionSearchResultsInterfaceFactory;
use MageRocket\GoCuotas\Api\TransactionRepositoryInterface;
use MageRocket\GoCuotas\Model\ResourceModel\Transaction as TransactionResourceModel;
use MageRocket\GoCuotas\Model\ResourceModel\Transaction\CollectionFactory as TransactionCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class TransactionRepository implements TransactionRepositoryInterface
{

    /**
     * @var TransactionResourceModel
     */
    protected $transactionModel;

    /**
     * @var TransactionInterfaceFactory
     */
    protected $transactionFactory;

    /**
     * @var TransactionCollectionFactory
     */
    protected $transactionCollectionFactory;

    /**
     * @var Transaction
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @param TransactionResourceModel $transactionModel
     * @param TransactionInterfaceFactory $transactionFactory
     * @param TransactionCollectionFactory $transactionCollectionFactory
     * @param TransactionSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        TransactionResourceModel $transactionModel,
        TransactionInterfaceFactory $transactionFactory,
        TransactionCollectionFactory $transactionCollectionFactory,
        TransactionSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->transactionModel = $transactionModel;
        $this->transactionFactory = $transactionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save
     *
     * @param TransactionInterface $transaction
     * @return TransactionInterface
     * @throws CouldNotSaveException
     */
    public function save(TransactionInterface $transaction)
    {
        try {
            $this->transactionModel->save($transaction);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the transaction: %1',
                $exception->getMessage()
            ));
        }
        return $transaction;
    }

    /**
     * Get by Id
     *
     * @param int|string $entityId
     * @return TransactionInterface
     * @throws NoSuchEntityException
     */
    public function get($entityId)
    {
        $transaction = $this->transactionFactory->create();
        $this->transactionModel->load($transaction, $entityId, TransactionInterface::ENTITY_ID);
        if (!$transaction->getId()) {
            throw new NoSuchEntityException(__('Transaction with id "%1" does not exist.', $entityId));
        }
        return $transaction;
    }

    /**
     * Get by TransactionId
     *
     * @param int|string $transactionId
     * @return TransactionInterface
     * @throws NoSuchEntityException
     */
    public function getByTransactionId($transactionId)
    {
        $transaction = $this->transactionFactory->create();
        $this->transactionModel->load($transaction, $transactionId, TransactionInterface::TRANSACTION_ID);
        if (!$transaction->getId()) {
            throw new NoSuchEntityException(__('Transaction with id "%1" does not exist.', $transactionId));
        }
        return $transaction;
    }

    /**
     * Get by OrderId
     *
     * @param int|string $orderId
     * @return TransactionInterface|false
     */
    public function getByOrderId($orderId)
    {
        $transaction = $this->transactionFactory->create();
        $this->transactionModel->load($transaction, $orderId, TransactionInterface::ORDER_ID);
        if (!$transaction->getId()) {
            return false;
        }
        return $transaction;
    }

    /**
     * Get by Order Increment
     *
     * @param int $incrementId
     * @return TransactionInterface
     * @throws LocalizedException
     */
    public function getByOrderIncrementId($incrementId)
    {
        $transaction = $this->transactionFactory->create();
        $this->transactionModel->load($transaction, $incrementId, TransactionInterface::INCREMENT_ID);
        if (!$transaction->getId()) {
            throw new NoSuchEntityException(
                __('Transaction with External Reference "%1" does not exist.', $incrementId)
            );
        }
        return $transaction;
    }

    /**
     * Get List
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return TransactionSearchResultsInterface
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ) {
        $collection = $this->transactionCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * Delete
     *
     * @param TransactionInterface $entity
     * @return true
     * @throws CouldNotDeleteException
     */
    public function delete(TransactionInterface $entity)
    {
        try {
            $transactionModel = $this->transactionFactory->create();
            $this->transactionModel->load($transactionModel, $entity->getTransactionId());
            $this->transactionModel->delete($transactionModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Transaction: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * Delete by Id
     *
     * @param int|string $entityId
     * @return true
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     */
    public function deleteById($entityId)
    {
        return $this->delete($this->get($entityId));
    }
}
