<?php

namespace bvb\icontact\common\models;

use bvb\icontact\common\helpers\ApiHelper;
use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "icontact_contact".
 *
 * @property integer $user_id
 * @property integer $contact_id
 * @property string $last_sync_time
 * @property string $create_time
 * @property string $update_time
 *
 * @property User $user
 */
class Contact extends \yii\db\ActiveRecord
{
    /**
     * List of additional params that may apply to a contact in iContact like
     * name or custom fields like if a user is an active subscriber. These will
     * be used in calls to the API when creating or updating a contact
     * @var array
     */
    public $params = [];

    /**
     * Name of the class for the related user model
     * @see [[getUserClass()]]
     * @var string
     */
    private $_userClass;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'icontact_contact';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'user_id'], 'unique'],
            [['id', 'user_id'], 'integer'],
            [['last_sync_time'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => $this->getUserClass(), 'targetAttribute' => ['user_id' => $this->getUserClass()::instance()->primaryKey()[0]]]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne($this->getUserClass(), ['id' => 'user_id']);
    }

    /**
     * Connect to the API to add the contact
     * {@inheritdoc}
     */
    public function beforeValidate()
    {
        $this->populateParams();

        if($this->isNewRecord && empty($this->id)){
            try{
                $contacts = ApiHelper::getSingleton()->getInstance()->makeCall(
                    '/a/'.ApiHelper::getSingleton()->getInstance()->setAccountId().'/c/'.ApiHelper::getSingleton()->getInstance()->setClientFolderId().'/contacts/',
                    'POST',
                    [$this->params],
                    'contacts'
                );
                $this->id = $contacts[0]->contactId;
            } catch(Exception $e){
                foreach(ApiHelper::getSingleton()->getInstance()->getErrors() as $error){
                    $this->addError('id', $error);
                }                
            }
        } elseif(!$this->isNewRecord) {
            try{
                $contact = ApiHelper::getSingleton()->getInstance()->makeCall(
                    '/a/'.ApiHelper::getSingleton()->getInstance()->setAccountId().'/c/'.ApiHelper::getSingleton()->getInstance()->setClientFolderId().'/contacts/'.$this->id,
                    'POST',
                    $this->params,
                    'contact'
                );
            } catch(Exception $e){
                \Yii::warning($e->getMessage());
                foreach(ApiHelper::getSingleton()->getInstance()->getErrors() as $error){
                    $this->addError('id', $error);
                }
            }
        }
        return parent::beforeValidate();
    }

    /**
     * Gets the user class based on the user identity class of the app or the
     * one set via an application paramter [iContact][userClass]
     * @return string
     */
    private function getUserClass()
    {
        if(empty($this->_userClass)){
            if(isset(Yii::$app->user->identityClass)){
                $this->_userClass = Yii::$app->user->identityClass;
            }
            if(isset(Yii::$app->params['iContact']['userClass'])){
                $this->_userClass = Yii::$app->params['iContact']['userClass'];
            }
            if(!$this->_userClass){
                throw new InvalidConfigException('There is no user component identityClass set and no application parameter for [iContact][userClass] so the Contact model cannot determine the related user class');
            }            
        }
        return $this->_userClass;
    }

    /**
     * Uses the application parameter ['iContact']['contactPropertyMap'] to
     * populate any values in [[$params]] that haven't already been set.
     * @return array The params on the model
     */
    public function populateParams()
    {
        // --- We will allow for a custom mapping of properties via an application
        // --- param and in and the [[$params]] variable on this will override those
        // --- values to determine what to send to the API
        $map = [
            'email' => 'user.email',
        ];
        if(!empty($this->contactId)){
            $map['id'] = $this->contactId;
        }
        if(isset(Yii::$app->params['iContact']['contactPropertyMap'])){
            $map = ArrayHelper::merge($map, Yii::$app->params['iContact']['contactPropertyMap']);
        }
        foreach($map as $iContactPropertyName => $modelPropertyName){
            if(!isset($this->params[$iContactPropertyName])){
                $this->params[$iContactPropertyName] = ArrayHelper::getValue($this, $modelPropertyName);
            }
        }

        return $this->params;
    }
}
