<?php

namespace Omnipay\GlobalPayments\Message\HeartlandMessage;

use GlobalPayments\Api\Entities\Enums\CommercialIndicator;

class PurchaseRequest extends AbstractHeartlandRequest
{
    public function runTrans()
    {
        $this->setGoodResponseCodes(array('00', '10'));

        $chargeMe = $this->gpCardObj;

        $chargeMe = $chargeMe->charge($this->getAmount())
            ->withAddress($this->gpBillingAddyObj)
            ->withCurrency($this->getCurrency())
            ->withDescription($this->getDescription())
            ->withClientTransactionId($this->getTransactionId())
            ->withStoredCredential($this->gpStoredCredObj);

        if ($this->gpCommercialObj) {
            $chargeMe->withCommercialRequest($this->getCommercialIndicator() == CommercialIndicator::LEVEL_II)
                ->withCommercialData($this->gpCommercialObj);
        }

        return $chargeMe->execute();
    }
}
