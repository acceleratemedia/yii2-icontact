<?php

namespace bvb\icontact\console\controllers;

use bvb\icontact\common\helpers\ApiHelper;
use bvb\icontact\common\models\Contact;
use DateTime;
use Yii;
use yii\console\Controller;
use yii\db\Expression;

/**
 * ContactController is for console commands related to Contact models and their
 * related database records and also their objects in the iContact API
 */
class ContactController extends Controller
{
    /**
     * Makes sure data on the iContact API matches what we have locally
     * @param string $lastSyncTime String that can be interpreted by PHP to 
     * modify a DateTime class used in the query for all contacts whose last
     * sync time was after this value
     * @param integer $limit How many to query for to sync
     * @return integer
     */
    public function actionSync($lastSyncTime = '-1 months', $limit = 1000)
    {
        Yii::$app->reporting->startReport(['title' => 'Syncing iContact Contacts']);
        $dateTime = new DateTime($lastSyncTime);
        
        $contactsNeedingSyncing = Contact::find()->where(['<=', 'last_sync_time', $dateTime->format('Y-m-d H:i:s')])
            ->limit($limit)
            ->all();

        $data = $contactIdsBeingUpdated = [];
        foreach($contactsNeedingSyncing as $contact){
            $data[] = $contact->populateParams();
            $contactIdsBeingUpdated[] = $contact->contact_id;
        }

        Yii::$app->reporting->addSummary('Found '.count($data).' contacts not synced since '.$dateTime->format('Y-m-d H:i:s').' that will be updated.');

        try{
            $results = ApiHelper::getSingleton()->getInstance()->makeCall(
                '/a/'.ApiHelper::getSingleton()->getInstance()->setAccountId().'/c/'.ApiHelper::getSingleton()->getInstance()->setClientFolderId().'/contacts/',
                'POST',
                $data
            );
        } catch(Exception $e){
            Yii::$app->reporting->addError($e->getMessage());
            if(is_array(ApiHelper::getSingleton()->getInstance()->getErrors())){
                foreach(ApiHelper::getSingleton()->getInstance()->getErrors() as $error){
                    Yii::$app->reporting->addError($error);
                }
            }
        }

        Yii::$app->reporting->addInfo('Last sync time being updated for the contacts...');
        $numRecordsSynced = Yii::$app->db
            ->createCommand()
            ->update(
                Contact::tableName(),
                ['last_sync_time' => new Expression('NOW()')],
                ['IN', 'contact_id', $contactIdsBeingUpdated]
            )
            ->execute();
        Yii::$app->reporting->addInfo($numRecordsSynced.' records updated');

        $numErrors = 0;
        if(isset($results->warnings) && !empty($results->warnings)){
            foreach($results->warnings as $possibleIndex => $message){
                $numErrors++;
                Yii::$app->reporting->addError('The possible index of '.$possibleIndex.' had the error message: '.$message);        
            }
        }
    
        Yii::$app->reporting->addSummary('Sync completed with '.$numErrors.' "warnings" returned by their API');
    }
}