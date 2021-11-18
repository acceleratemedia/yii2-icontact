<?php

namespace bvb\icontact\common\helpers;

use bvb\icontact\common\helpers\ApiHelper;
use bvb\icontact\common\models\Contact;

/**
 * 
 */
class ContactHelper extends BaseObject
{
    /**
     * Gets a Contact model based on the supplied email. First, it will query
     * the database via the User model's table for an email field. If not, it
     * will check for a contact on the API with the supplied email. If one is
     * found, it will check the database for an existing record with the same
     * ID. If that record is not found, it will create a new Contact record
     * @var string $email
     * @var string $defaults Default values to use when instantiating the 
     * Contact model
     * @return boolean|bvb\icontact\common\models\Contact
     */
    static function getByEmail($email, $defaults = [])
    {
        try{
            return Contact::find()
                ->with(['user'])
                ->where(['email' => $email])
                ->one();
        } catch(\Throwable $e){
            // --- it's fine
        }

        
    }

    /**
     * Will create and return a new contact model by 
     * @var string $email
     * @var string $defaults Default values to use when instantiating the 
     * Contact model
     * @return boolean|bvb\icontact\common\models\Contact
     */
    static function addFromApi($email, $defaults = [])
    {
        $apiResponse = ApiHelper::getSingleton()
            ->getInstance()
            ->addCustomQueryField('email', $email)
            ->makeCall('/a/'.ApiHelper::getSingleton()->getInstance()->setAccountId().'/c/'.ApiHelper::getSingleton()->getInstance()->setClientFolderId().'/contacts');

        $contact = new Contact($defaults);
        if(!$contact->save()){
            Yii::error('Error saving contact after pulling data from API: '.print_r($contact->getErrors(), true), __METHOD__);
            return false;
        }

        return $contact;
    }
}