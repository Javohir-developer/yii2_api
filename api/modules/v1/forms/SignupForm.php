<?php

namespace api\modules\v1\forms;

use common\models\Company;
use common\models\Profile;
use common\models\Tokens;
use common\modules\playmobile\models\PhoneConfirmation;
use Yii;
use yii\base\Model;
use common\models\User;

/**
 * Signup form
 */
class SignupForm extends Model
{
	public $type;
	public $full_name;
	public $email;
	public $password;
	public $password_repeat;
	public $company;
	public $region_id;
	public $city;
	public $phone;


	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['full_name', 'password', 'type', 'phone', 'region_id'], 'required'],
			[['type', 'region_id', 'city'], 'integer'],
			[['full_name', 'email', 'phone', 'company'], 'string'],
			[['email', 'phone'], 'my_required'],
			['email', 'trim'],
			['email', 'email'],
			['email', 'unique', 'targetClass' => User::class, 'message' => __('This email address has already registered.')],
			['phone', 'unique', 'targetClass' => User::class, 'message' => __('This phone has already registered.')],
			['password', 'string', 'min' => 6],
//			['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => __("Passwords don't match")],
		];
	}

	public function my_required($attribute_name, $params)
	{
		if (empty($this->phone) && empty($this->email)) {
			$this->addError($attribute_name, Yii::t('main', 'At least 1 of the field must be filled up properly'));
			return false;
		}
		return true;
	}

    public function validateNumer($attributeNames)
    {
        return is_numeric($attributeNames);
    }

    public function attributeLabels()
	{
		return [
			'type' => Yii::t('main', 'Тип аккаунта'),
			'full_name' => Yii::t('main', 'Ф. И. О.'),
			'email' => Yii::t('main', 'Электронная почта'),
			'phone' => Yii::t('main', 'Телефон'),
			'password' => Yii::t('main', 'Пароль'),
//			'password_repeat' => Yii::t('main', 'Повторите пароль'),
			'region' => Yii::t('main', 'Регино или Город'),
		];
	}

	/**
	 * Signs user up.
	 *
	 * @return User|array the saved model or null if saving fails
	 * @throws \yii\base\Exception
	 */
	public function signup()
	{
        $phone = $this->phone;

        $phone = str_replace(' ', '',$phone);
        $phone = str_replace('+', '',$phone);
        $phone = str_replace('-', '',$phone);
        if(!$this->validateNumer($phone)){
            \Yii::$app->response->setStatusCode(422);
            return false;
        }
        $this->phone = $phone;

		if (!$this->validate()) {
            Yii::$app->response->setStatusCode(422);
			return $this->getErrors();
		}

		$user = new User();
		$user->type = $this->type;
		$user->full_name = $this->full_name;
		$user->phone = $phone;
		$user->email = $this->email;
		$user->status = User::STATUS_UNCONFIRMED;
		$user->setPassword($this->password);
		$user->generateAuthKey();

		if ($user->save()) {
            $code = rand(1000, 9999);

            $message = \Yii::t("main", "Confirmation code on legans.uz: {code}", ['code' => $code]);
            $confirmation = new PhoneConfirmation();
            $confirmation->phone = $user->phone;
            $confirmation->status = PhoneConfirmation::STATUS_UNCONFIRMED;
            $confirmation->code = (string) $code;
            $confirmation->save();
            \Yii::$app->playmobile->sendSms($user->phone, $message);
            $token = new Tokens(['scenario' => Tokens::SCENARIO_CREATE]);
            $token->type = User::TOKEN_LOGIN;
            $token->user_id = $user->id;
            $token->save();
            $profile = new Profile();
            $profile->user_id = $user->id;
            $profile->region_id = $this->region_id;
            $profile->city_id = $this->city;
            if($user->type >=1){
                $profile->status = Profile::STATUS_DEACTIVE;
            }
            $profile->save();
            if ($this->company != null) {
            	$comp = new Company();
            	$comp->owner_id = $user->id;
            	$comp->type = $this->type;
            	$comp->name = $this->company;
            	$comp->save();
            	$profile->company_id = $comp->id;
            	$profile->save();
			}
			return array_merge($user->toArray(), ['type' => $this->type, 'token' => $user->token->token]);
		} else {
			Yii::$app->response->setStatusCode(422);
			return $user->getErrors();
		}
	}
}
