<?php

class Agere_Form_Helper_Contact extends Mage_Core_Helper_Abstract
{
    public function handleForm($data)
    {
        $storeId = Mage::app()->getStore()->getId();
        /** @var Agere_Form_Helper_Data $dataHelper */
        $dataHelper = Mage::helper('agere_form');
        $data['passport'] = (!isset($data['passport'])) ? 'Нет' : 'Да';
        //$email = $this->getEmailFromId($data);
        $email = Mage::getStoreConfig($dataHelper::SECTION_NAME_CONTACT . '/general/recipient_email', $storeId);

        return $dataHelper->sendSimpleMail($data, $dataHelper::SECTION_NAME_CONTACT, $email);
    }
}