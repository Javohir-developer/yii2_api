<?php
/**
 * Created by SuperMan.
 * User: Clark Kent
 */

namespace api\modules\v1\forms;


use common\components\InputModelBehavior;
use common\models\LawFields;
use common\models\Profile;
use common\models\User;
use oks\categories\behaviors\CategoryModelBehavior;
use yii\base\Model;
use yii\behaviors\TimestampBehavior;
use yii\web\UnprocessableEntityHttpException;

class ProfileUpdateForm extends Model
{
	public $full_name;
	public $email;
	public $old_password;
	public $password;
	public $region;
	public $bio;
	public $gender;
	public $region_id;
	public $city;
	public $adress;
	public $adress_desc;
	public $category_id;
	private $_category;

	public function behaviors()
	{
		return [
			'category_model' => [
				'class' => CategoryModelBehavior::class,
				'attribute' => '_category',
				'separator' => ',',
			],
		];
	}

	public function rules()
	{
		return [
			[['full_name', 'email', 'old_password', 'password', 'region', 'bio', 'adress', 'adress_desc'], 'string'],
			[['city', 'gender', 'region_id'], 'integer'],
			['email', 'email'],
		];
	}

	public function update()
	{
		$model = User::findOne(\Yii::$app->user->identity->id);

		if (!$this->validate()) {
			\Yii::$app->response->setStatusCode('422');
			return $this->getErrors();
		}

		if ($this->old_password != null && $this->password != null) {
			if ($model->validatePassword($this->old_password)) {
				$model->setPassword($this->password);
			} else {
				throw new UnprocessableEntityHttpException('Wrong old password', 3);
			}
		}
		$progress = 0;
		$model->full_name = $this->full_name ?: $model->full_name;
		$model->email = $this->email ?: $model->email;
		$model->region = $this->region ?: $model->region;
		$model->profile->bio = $this->bio ?: $model->profile->bio;
		$model->profile->address = $this->adress ?: $model->profile->address;
		$model->profile->region_id = $this->region_id ?: $model->profile->region_id;
		$model->profile->city_id = $this->city ?: $model->profile->city_id;
		$model->profile->adress_desc = $this->adress_desc ?: $model->profile->adress_desc;

		if (isset($this->gender) && $this->gender !== null)
			$model->profile->gender = $this->gender;

		foreach ($model->profile->getAttributes(['bio', 'address', 'adress_desc', 'city_id', 'region_id', 'gender']) as $attribute) {
			if ($attribute != null) $progress++;
		}
		if ($model->profile->educations) {
			foreach ($model->profile->educations[0]->getAttributes(['name', 'faculty', 'country', 'city', 'grad_year', 'files']) as $attribute) {
				if ($attribute !== null) $progress++;
			}
		}
		$model->profile->progress = ($progress / 12) * 100;
		if ($model->profile->status == Profile::STATUS_DEACTIVE && $model->profile->progress == 100)
			$model->profile->status = Profile::STATUS_WAITING;

		$model->profile->save();
		if ($model->save()) {
			if (is_array($this->category_id)) {
				$model->profile->category = $this->category_id;
//                foreach ($this->category_id as $item) {
//                    $category = new LawFields();
//                    $category->profile_id = $model->profile->id;
//                    $category->category_id = $item;
//                    $category->save();
//
//                }
			}
			return array_merge($model->toArray(), ['profile' => $model->profile->toArray()]);
		} else {
			\Yii::$app->response->setStatusCode(422);
			return $this->getErrors();
		}
	}
}