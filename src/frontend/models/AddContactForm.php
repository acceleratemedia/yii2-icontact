<?php

namespace bvb\icontact\frontend\models;

use Yii;
use yii\base\Model;

/**
 * AddContactForm is the model representing the intake of a user's email
 * address to add them as a contact
 */
class AddContactForm extends Model
{
    /**
     * Email address to subscribe to the list
     * @var string
     */
    public $email;

    /**
     * Set the email as the one for the logged in user if there is one
     * {@inheritdoc}
     */
    public function init()
    {
        if(!Yii::$app->user->isGuest){
            $this->email = Yii::$app->user->identity->email;
        }
        return parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email'], 'required'],
            [['email'], 'email'],
        ];
    }
}