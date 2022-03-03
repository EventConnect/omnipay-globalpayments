<?php

namespace Omnipay\GlobalPayments\Message;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\CommercialData;
use GlobalPayments\Api\Entities\CommercialLineItem;
use GlobalPayments\Api\Entities\Enum\CommercialIndicator;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;
use GlobalPayments\Api\Entities\Enums\TaxType;
use GlobalPayments\Api\Entities\StoredCredential;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use Omnipay\GlobalPayments\CreditCard;

abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    protected $gpBillingAddyObj;
    protected $gpCardObj;
    protected $gpStoredCredObj;
    protected $gpCommercialObj;

    protected abstract function runTrans();
    protected abstract function setServicesConfig();

    /**
     * Overrides parent class method to create a Omnipay\GlobalPayments\CreditCard.
     *
     * @param CreditCard $value
     * @return $this
     */
    public function setCard($value)
    {
        if ($value) {
            if ($value instanceof \Omnipay\Common\CreditCard) {
                $value = new CreditCard($value->getParameters());
            } else if (!$value instanceof CreditCard) {
                $value = new CreditCard($value);
            }
        }

        return $this->setParameter('card', $value);
    }

    protected function getGpCardObj()
    {
        $gpCardObj = new CreditCardData();

        if ($this->getCard()) {
            $omnipayCardObj = $this->getCard();
            
            $gpCardObj->number = $omnipayCardObj->getNumber();
            $gpCardObj->expMonth = $omnipayCardObj->getExpiryMonth();
            $gpCardObj->expYear = $omnipayCardObj->getExpiryYear();
            $gpCardObj->cvn = $omnipayCardObj->getCvv();
            $gpCardObj->cardHolderName = sprintf('%s %s', $omnipayCardObj->getFirstName(), $omnipayCardObj->getLastName());
            $gpCardObj->cardType = $omnipayCardObj->getType();
        }

        if (!empty($this->getToken())) {
            $gpCardObj->token = $this->getToken();
        } elseif (!empty($this->getCardReference())) {
            $gpCardObj->token = $this->getCardReference();
        }

        return $gpCardObj;
    }

    protected function getGpBillingAddyObj()
    {
        $gpAddyObj = new Address();

        if ($this->getCard()) {
            $omnipayCardObj = $this->getCard();

            $gpAddyObj->streetAddress1 = $omnipayCardObj->getBillingAddress1();
            $gpAddyObj->streetAddress2 = $omnipayCardObj->getBillingAddress2();
            $gpAddyObj->city = $omnipayCardObj->getBillingCity();
            $gpAddyObj->postalCode = str_replace(' ', '', $omnipayCardObj->getBillingPostcode());
            $gpAddyObj->state = $omnipayCardObj->getBillingState();
            $gpAddyObj->country = $omnipayCardObj->getBillingCountry();
        }

        return $gpAddyObj;
    }

    protected function getGpStoredCredObj()
    {
        $gpStoredCredObj = new StoredCredential();

        if ($this->getCard()) {
            $omnipayCardObj = $this->getCard();
            
            if (!empty($omnipayCardObj->getCardBrandTransId())) {
                $gpStoredCredObj->cardBrandTransactionId = $omnipayCardObj->getCardBrandTransId();
            }

            if (!empty($omnipayCardObj->getStoredCredInitiator())) {
                $gpStoredCredObj->initiator = $omnipayCardObj->getStoredCredInitiator();
            }
        }

        return $gpStoredCredObj;
    }

    protected function getGpCommercialObj()
    {
        if (! $this->getItems()) {
            return null;
        }

        $commercialIndicator = $this->getCommercialIndicator();

        if (empty($commercialIndicator)) {
            $commercialIndicator = CommercialIndicator::LEVEL_II;
        }

        $gpCommercialObj = new CommercialData(TaxType::NOT_USED, $commercialIndicator);

        foreach($this->getItems() as $omnipayItemObj) {
            $gpCommercialItemObj = new CommercialLineItem();
            $gpCommercialItemObj->name = $omnipayItemObj->getName();
            $gpCommercialItemObj->description = $omnipayItemObj->getDescription();
            $gpCommercialItemObj->quantity = $omnipayItemObj->getQuantity();
            $gpCommercialItemObj->unitCost = $omnipayItemObj->getPrice();

            $gpCommercialObj->addLineItems($gpCommercialItemObj);
        }

        return $gpCommercialObj;
    }

    public function getData()
    {
        $this->gpBillingAddyObj = $this->getGpBillingAddyObj();
        $this->gpCardObj = $this->getGpCardObj();
        $this->gpStoredCredObj = $this->getGpStoredCredObj();
        $this->gpCommercialObj = $this->getGpCommercialObj();
    }

    public function sendData($data)
    {
        $this->setServicesConfig();
    }

    public function getDeviceId()
    {
        return $this->getParameter('deviceId');
    }

    public function setDeviceId($value)
    {
        return $this->setParameter('deviceId', $value);
    }

    public function getUsername()
    {
        return $this->getParameter('username');
    }

    public function setUsername($value)
    {
        return $this->setParameter('username', $value);
    }

    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    public function getDeveloperId()
    {
        return $this->getParameter('developerId');
    }

    public function setDeveloperId($value)
    {
        return $this->setParameter('developerId', $value);
    }

    public function getGoodReponseCodes()
    {
        return $this->getParameter('goodResponseCodes');
    }

    public function setGoodResponseCodes($value)
    {
        return $this->setParameter('goodResponseCodes', $value);
    }

    public function setCommercialIndicator($value)
    {
        return $this->setParameter('commercialIndicator', $value);
    }

    public function getCommercialIndicator()
    {
        return $this->getParameter('commercialIndicator');
    }
}
