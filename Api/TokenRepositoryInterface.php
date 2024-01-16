<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Api;

use MageRocket\GoCuotas\Api\Data\TokenInterface;
use MageRocket\GoCuotas\Api\Data\TokenSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface TokenRepositoryInterface
{
    /**
     * Save
     *
     * @param TokenInterface $token
     * @return mixed
     */
    public function save(TokenInterface $token);

    /**
     * Get
     *
     * @param int $id
     * @return TokenInterface
     * @throws NoSuchEntityException
     */
    public function get($id);

    /**
     * Get By Store
     *
     * @param int $storeId
     * @return TokenInterface
     * @throws NoSuchEntityException
     */
    public function getByStore($storeId);

    /**
     * Get List
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return TokenSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete
     *
     * @param TokenInterface $token
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(TokenInterface $token);

    /**
     * DeleteById
     *
     * @param int $tokenId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($tokenId);
}
