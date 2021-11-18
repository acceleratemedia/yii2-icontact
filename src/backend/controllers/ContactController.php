<?php

namespace bvb\icontact\backend\controllers;

use bvb\icontact\common\models\Contact;
use bvb\user\backend\controllers\traits\AdminAccess;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ContactController is for routes related to Contact models and their related
 * database records and also their objects in the iContact API
 */
class ContactController extends Controller
{
    /**
     * Implement AccessControl that requires admin role to access actions
     */
    use AdminAccess;

    /**
     * Syncs contact data by just using validate() on the model to trigger
     * beforeValidate() where it all happens
     * @param string $userId ID of the contact in iContact
     * @param string $redirectUrl URL to redirect the uesr to
     * @return yii\web\Response
     */
    public function actionSync($userId, $redirectUrl)
    {
        $contact = Contact::findOne($userId);
        if(!$contact){
            throw new NotFoundHttpException('Contact not found');
        }
        if($contact->validate()){
            Yii::$app->session->addFlash('success', 'Contact updated in iContact');
        } else {
            $message = 'There was an error updating the contact in iContact';
            Yii::error($message.': '.print_r($contact->getErrors(), true));
            Yii::$app->session->addFlash('error', $message);
        }
        return $this->redirect($redirectUrl);
    }
}