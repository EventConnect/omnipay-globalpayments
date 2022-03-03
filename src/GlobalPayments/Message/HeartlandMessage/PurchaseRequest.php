<?php

namespace Omnipay\GlobalPayments\Message\HeartlandMessage;

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
            $chargeMe->withCommercialRequest(true)
                ->withCommercialData($this->gpCommercialObj);
        }

        return $chargeMe->execute();
    }
}
