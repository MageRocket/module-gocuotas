<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Model;

use MageRocket\GoCuotas\Api\Data\TokenInterface;
use MageRocket\GoCuotas\Api\Data\TokenInterfaceFactory;
use MageRocket\GoCuotas\Api\Data\TokenSearchResultsInterface;
use MageRocket\GoCuotas\Api\Data\TokenSearchResultsInterfaceFactory;
use MageRocket\GoCuotas\Api\TokenRepositoryInterface;
use MageRocket\GoCuotas\Model\ResourceModel\Token as TokenResourceModel;
use MageRocket\GoCuotas\Model\ResourceModel\Token\CollectionFactory as TokenCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class TokenRepository implements TokenRepositoryInterface
{

    /**
     * @var TokenResourceModel
     */
    protected $tokenModel;

    /**
     * @var TokenInterfaceFactory
     */
    protected $tokenFactory;

    /**
     * @var TokenCollectionFactory
     */
    protected $tokenCollectionFactory;

    /**
     * @var Token
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @param TokenResourceModel $tokenModel
     * @param TokenInterfaceFactory $tokenFactory
     * @param TokenCollectionFactory $tokenCollectionFactory
     * @param TokenSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        TokenResourceModel $tokenModel,
        TokenInterfaceFactory $tokenFactory,
        TokenCollectionFactory $tokenCollectionFactory,
        TokenSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->tokenModel = $tokenModel;
        $this->tokenFactory = $tokenFactory;
        $this->tokenCollectionFactory = $tokenCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * Save
     *
     * @param TokenInterface $transaction
     * @return TokenInterface
     * @throws CouldNotSaveException
     */
    public function save(TokenInterface $transaction)
    {
        try {
            $this->tokenModel->save($transaction);
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
     * @param int|string $id
     * @return TokenInterface
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        $transaction = $this->tokenFactory->create();
        $this->tokenModel->load($transaction, $id, TokenInterface::TOKEN_ID);
        if (!$transaction->getId()) {
            throw new NoSuchEntityException(__('Token with id "%1" does not exist.', $id));
        }
        return $transaction;
    }

    /**
     * Get List
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return TokenSearchResultsInterface
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ) {
        $collection = $this->tokenCollectionFactory->create();
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
     * @param TokenInterface $token
     * @return true
     * @throws CouldNotDeleteException
     */
    public function delete(TokenInterface $token)
    {
        try {
            $tokenModel = $this->tokenFactory->create();
            $this->tokenModel->load($tokenModel, $token->getTokenId());
            $this->tokenModel->delete($tokenModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete Token: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * Delete by TokenId
     *
     * @param int|string $tokenId
     * @return true
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     */
    public function deleteById($tokenId)
    {
        return $this->delete($this->get($tokenId));
    }

    /**
     * Get By StoreId
     *
     * @param int|string $storeId
     * @return false|TokenInterface
     */
    public function getByStore($storeId)
    {
        $transaction = $this->tokenFactory->create();
        $this->tokenModel->load($transaction, $storeId, TokenInterface::STORE_ID);
        if (!$transaction->getId()) {
            return false;
        }
        return $transaction;
    }
}
