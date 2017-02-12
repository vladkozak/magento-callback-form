<?php

class Agere_Form_ContactController extends Mage_Core_Controller_Front_Action
{
    public function getFormAction()
    {
        $content = $this->getLayout()->createBlock('core/template')->setTemplate('agere/form/contact.phtml')->toHtml();
        $this->getResponse()->setBody($content);
    }

    public function createAction()
    {
        if ($post = $this->getRequest()->getPost()) {
            /** @var Agere_Form_Helper_Data $helperData */
            $helperData = Mage::helper('agere_form');
            if ($helperData->checkCaptcha($post)) {
                try {
                    $filename = $helperData->fileDownload();
                } catch (Exception $e) {
                    Mage::getSingleton('core/session')->addError('Ви завантажили невірний файл');
                    $this->_redirect('');

                    return;
                }
                $post['filename'] = $filename;
                Mage::getSingleton('core/session')->setPostDataCall($post);
                /** @var Agere_Form_Helper_Contact $helperJob */
                $helperJob = Mage::helper('agere_form/contact');
                if ($helperJob->handleForm($post)) {
                    Mage::getSingleton('core/session')->addSuccess(
                        'Повідомлення відправлено! Запит буде опрацьовано найближчим часом'
                    )
                    ;
                    Mage::getSingleton('core/session')->unsPostDataCall();
                }
                $this->_redirect('');
            } else {
                Mage::getSingleton('core/session')->addError('Ви ввели невірно каптчу!');
                $this->_redirect('');
            }
        }
    }
}