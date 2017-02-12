<?php

class Agere_Form_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_CALLBACK_RECIPIENT = '/general/recipient_email';

    const XML_PATH_CALLBACK_SENDER = '/general/sender_email_identity';

    const XML_PATH_CALLBACK_TEMPLATE = '/general/email_template';

    const XML_PATH_SIMPLE_ORDER_TEMPLATE = '/general/email_template';

    const XML_PATH_ENABLED = '/general/enabled';

    const GOOGLE_CAPTCHA_SECRET = '6LetIBQUAAAAAD8H9cCc37TeSiKOZH8bliBZrge1';

    const GOOGLE_CAPTCHA_URL = 'https://www.google.com/recaptcha/api/siteverify';

    const SECTION_NAME_JOB = 'agere_form_job';

    const SECTION_NAME_CONTACT = 'agere_form_contact';

    /**
     * @param array $data
     * @param string $sectionName
     */
    public function sendSimpleMail(array $data, $sectionName, $emailCustomer = null)
    {
        $storeId = Mage::app()->getStore()->getId();
        $mailTemplate = Mage::getModel('core/email_template');
        /** @var Mage_Core_Model_Email_Template $mailTemplate */
        $mailTemplate->setDesignConfig(['area' => 'frontend']);
        if ($emailCustomer) {
            $email = $emailCustomer;
        } else {
            $email = Mage::getStoreConfig($sectionName . self::XML_PATH_CALLBACK_RECIPIENT, $storeId);
        }
        if ($data['filename']) {
            $mailTemplate->getMail()
                ->createAttachment(
                    file_get_contents(Mage::getBaseDir() . Mage::getStoreConfig('agere_form/general/path_to_temp', $storeId) . DS . $data['filename']),
                    Zend_Mime::TYPE_OCTETSTREAM,
                    Zend_Mime::DISPOSITION_ATTACHMENT,
                    Zend_Mime::ENCODING_BASE64,
                    basename($data['filename'])
                )
            ;
        }
       // Zend_Debug::dump([$email, $data]); die(__METHOD__);

        $mailTemplate->sendTransactional(
            Mage::getStoreConfig($sectionName . self::XML_PATH_SIMPLE_ORDER_TEMPLATE . '_' . $data['contact_id'], $storeId),
            Mage::getStoreConfig($sectionName . self::XML_PATH_CALLBACK_SENDER, $storeId),
            explode(";", $email),
            null,
            $data,
            $storeId
        );
        if ($mailTemplate->getSentSuccess()) {
            //Mage::getSingleton('core/session')->addSuccess('Письмо успешно отправлено');
            return true;
        }
    }

    /**
     * Check captcha
     */
    public function checkCaptcha($post)
    {
        if (isset($post['g-recaptcha-response']) && !empty($post['g-recaptcha-response'])) {
            //get verify response data
            $verifyResponse = file_get_contents(
                self::GOOGLE_CAPTCHA_URL
                . '?secret='
                . self::GOOGLE_CAPTCHA_SECRET
                . '&response='
                . $post['g-recaptcha-response']
            );
            $responseData = json_decode($verifyResponse);
            if (!$responseData->success) {
                return false;
            }

            return true;
        } else {
            return false;
        }
    }

    public function fileDownload()
    {
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != '') {
            try {
                $storeId = Mage::app()->getStore()->getId();
                /* Starting upload */
                $uploader = new Varien_File_Uploader('file');
                $extension = array_map(
                    'trim', explode(',', Mage::getStoreConfig('agere_form/general/file_extension', $storeId))
                );
                $uploader->setAllowedExtensions($extension);
                $path = Mage::getBaseDir() . Mage::getStoreConfig('agere_form/general/path_to_temp', $storeId);
                $_FILES['file']['name'] = time() . '_' . $this->translit($_FILES['file']['name']);
                $uploader->save($path, $_FILES['file']['name']);
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError(
                    Mage::helper('agere_form')->__('Unable to upload your file. Use the specified extensions only')
                )
                ;
                throw new Exception();
            }

            return $_FILES['file']['name'];
        }
    }

    public function translit($s)
    {
        $s = (string) $s; // преобразуем в строковое значение
        $s = strip_tags($s); // убираем HTML-теги
        $s = str_replace(["\n", "\r"], " ", $s); // убираем перевод каретки
        $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
        $s = trim($s); // убираем пробелы в начале и конце строки
        $s = function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s, 'UTF-8'); // переводим строку в нижний регистр (иногда надо задать локаль)
        $s = strtr(
            $s, [
                'а' => 'a',
                'б' => 'b',
                'в' => 'v',
                'г' => 'g',
                'д' => 'd',
                'е' => 'e',
                'ё' => 'e',
                'ж' => 'j',
                'з' => 'z',
                'и' => 'i',
                'й' => 'y',
                'к' => 'k',
                'л' => 'l',
                'м' => 'm',
                'н' => 'n',
                'о' => 'o',
                'п' => 'p',
                'р' => 'r',
                'с' => 's',
                'т' => 't',
                'у' => 'u',
                'ф' => 'f',
                'х' => 'h',
                'ц' => 'c',
                'ч' => 'ch',
                'ш' => 'sh',
                'щ' => 'shch',
                'ы' => 'y',
                'э' => 'e',
                'ю' => 'yu',
                'я' => 'ya',
                'ъ' => '',
                'ь' => '',
            ]
        );
        //$s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
        $s = str_replace(" ", "-", $s); // заменяем пробелы знаком минус
        return $s; // возвращаем результат
    }
}