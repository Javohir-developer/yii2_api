<?php

namespace api\models;

use common\models\Contact;
use Yii;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;

/**
 * ContactForm is the model behind the contact form.
 */
class ContactForm extends Model
{
    public $full_name;
    public $email;
    public $phone;
    public $message;
    public $type;
    public $created_at;
    public $updated_at;

	public function behaviors()
	{
		return [TimestampBehavior::className()];
	}

	/**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['full_name', 'phone', 'message'], 'required'],
			[['created_at', 'updated_at'], 'integer'],
            ['email', 'email'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'verifyCode' => 'Verification Code',
        ];
    }

	public function saveMessage()
	{
		$model = new Contact();

		if ($model->load($this->toArray())) {
			$model->save();
			return true;
		}
		return false;
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     *
     * @param string $email the target email address
     * @return bool whether the email was sent
     */
    public function sendEmail($email)
    {
        return Yii::$app->mailer->compose()
            ->setTo($email)
            ->setFrom([$this->email => $this->name])
            ->setSubject($this->subject)
            ->setTextBody($this->body)
            ->send();
    }
}
