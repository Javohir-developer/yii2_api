<?php

namespace api\modules\v1\forms;

use common\models\Tokens;
use common\models\User;
use Yii;
use yii\base\Model;

/**
 * Signin form
 */
class SigninForm extends Model
{
    public $login;
    public $password;
    public $rememberMe = true;

    private $_user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['login', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     * @throws \yii\base\Exception
     */
    public function signin()
    {
        if ($this->validate()) {
            $token = new Tokens(['scenario' => Tokens::SCENARIO_CREATE]);
            $token->type = User::TOKEN_LOGIN;
            $token->user_id = $this->user->id;
            $token->save();

            //   return  $this->getUser()->updateAttributes(['token' => $this->getUser()->generateToken()]);
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }

        return false;
    }
    /**
     *
     */
    public function validateNumer($attributeNames)
    {
        return is_numeric($attributeNames);
    }
    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        $phone = $this->login;

        $phone = str_replace(' ', '',$phone);
        $phone = str_replace('+', '',$phone);
        $phone = str_replace('-', '',$phone);
        if(!$this->validateNumer($phone)){
            \Yii::$app->response->setStatusCode(422);
            return false;
        }


        if ($this->_user === null) {
            $this->_user = User::find()->Where("phone = '{$phone}' OR email = '{$this->login}'")->one();
        }

        return $this->_user;
    }
}
