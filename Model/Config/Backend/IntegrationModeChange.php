<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Model\Config\Backend;

use Magento\Config\Model\ResourceModel\Config\Data;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use MageRocket\GoCuotas\Model\GoCuotas;

class IntegrationModeChange extends Value
{
    /**
     * @var GoCuotas $goCuotas
     */
    protected GoCuotas $goCuotas;

    /**
     * @var string $_resourceName
     */
    protected $_resourceName = Data::class;

    /**
     * @param GoCuotas $goCuotas
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        GoCuotas $goCuotas,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->goCuotas = $goCuotas;
    }

    /**
     * After Save
     *
     * @return mixed
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $scopeId = $this->getScopeId() ?: null;
            $this->goCuotas->deleteAllTokens($scopeId);
        }
        return parent::afterSave();
    }
}
