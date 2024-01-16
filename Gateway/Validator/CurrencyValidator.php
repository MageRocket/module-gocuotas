<?php
/**
 * @author MageRocket
 * @copyright Copyright (c) 2024 MageRocket (https://magerocket.com/)
 * @link https://magerocket.com/
 */

namespace MageRocket\GoCuotas\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class CurrencyValidator extends AbstractValidator
{
    // Currencies allowed
    protected const CURRENCIES = ['ARS','MXN'];

    /**
     * Validate
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $isValid = true;
        $fails = [];
        if (!in_array($validationSubject['currency'], self::CURRENCIES)) {
            $isValid = false;
            $fails[] = __("Currency doesn't match.");
        }
        return $this->createResult($isValid, $fails);
    }
}
