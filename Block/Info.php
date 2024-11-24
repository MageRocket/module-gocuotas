<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Block;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class Info extends \Magento\Payment\Block\Info
{
    /**
     * Fields to Show in Order
     */
    protected const GOCUOTAS_FIELDS = [
        ['field' => 'id', 'title' => 'Transaction ID'],
        ['field' => 'external_reference', 'title' => 'External Reference'],
        ['field' => 'installments', 'title' => 'Installments'],
        ['field' => 'method', 'title' => 'Payment Method'],
        ['field' => 'status', 'title' => 'Payment Status'],
        ['field' => 'reason', 'title' => 'Reason'],
        ['field' => 'card_number', 'title' => 'Card Number'],
        ['field' => 'card_name', 'title' => 'Card Name']
    ];

    /**
     * Prepare Specific Information
     *
     * @param null|DataObject|array $transport
     * @return DataObject|null
     * @throws LocalizedException
     */
    protected function _prepareSpecificInformation($transport = null): ?DataObject
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $data = [];
        $info = $this->getInfo();
        foreach (self::GOCUOTAS_FIELDS as $field) {
            if (!empty($info->getAdditionalInformation($field['field']))) {
                $text = __($field['title']);
                $additionalInformation = $info->getAdditionalInformation($field['field']) ?: '-';
                $data[$text->getText()] = ucfirst($additionalInformation);
            }
        }
        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
