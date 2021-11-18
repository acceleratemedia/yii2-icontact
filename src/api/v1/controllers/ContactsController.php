<?php

namespace bvb\icontact\api\v1\controllers;

use bvb\icontact\common\helpers\ApiHelper;
use bvb\icontact\common\models\Contact;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;

/**
 * ContactsController is for endpoints related to iContact Contact records
 * in this data on in iContacts' system
 */
class ContactsController extends Controller
{
    /**
     * Attempts to subscribe the provided email to the provided list
     * @param boolean $createModel Whether to create a new Contact model or to
     * add them to iContact without a record in the local database
     * @param integer $listId An optional list ID to subscribe the newly created
     * contact to
     * @return mixed
     */
    public function actionAdd($createModel = false, $listId = null)
    {
        $success = false;
        $message = '';

        $email = Yii::$app->request->post('email');

        if($createModel){
            $contact = new Contact(['params' => ['email' => $email]]);
            if($contact->save()){
                $success = true;
            } else {
                $message = implode("\n", $contact->getErrorSummary(true));
            }
        } else {    
            try{
                $apiResponse = ApiHelper::getSingleton()->getInstance()->addContact($email);
                $success = true;
            } catch(\Throwable $e){
                Yii::error($e);
                $errors = ApiHelper::getSingleton()->getInstance()->getErrors();
                $message = !empty($errors) ? implode("\n", $errors) : 'There was an unknown error';
            }
        }

        $return = [
            'success' => $success,
            'message' => $message,
            'data' => [
                'apiResponse' => $apiResponse,
            ]
        ];

        if(!$listId || !$success){
            return $return;
        }

        $contactId = isset($contact) ? $contact->id : $apiResponse->contactId;
        try{
            $addToListResponse = ApiHelper::getSingleton()->getInstance()->subscribeContactToList($contactId, $listId);
        } catch(\Throwable $e){
            $success = false;
            $errors = ApiHelper::getSingleton()->getInstance()->getErrors();
            $message = !empty($errors) ? implode("\n", $errors) : 'There was an unknown error';
        }

        $return['data']['addToListResponse'] = $addToListResponse;

        return $return;
    }
}
