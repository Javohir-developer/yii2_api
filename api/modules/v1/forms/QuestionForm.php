<?php

namespace api\modules\v1\forms;

use common\components\InputModelBehavior;
use common\models\Question;
use common\models\QuestionCategory;
use oks\filemanager\models\Files;
use Yii;
use yii\base\Model;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;


class QuestionForm extends Model
{

	public $description;
	public $text;
	public $category_id;
	public $files;
	public $file_src;

	public function behaviors()
	{
		return [
			'file_manager_model' => [
				'class' => InputModelBehavior::class,
				'delimitr' => ','
			],
		];
	}

	public function rules()
	{
		return [
			[['description', 'text'], 'required'],
			['files', 'string'],
            ['category_id', 'each', 'rule' => ['integer']],
		];
	}


	public function attributeLabels()
	{
		return [
			'description' => Yii::t('main', 'Question'),
			'text' => Yii::t('main', 'text'),
			'files' => Yii::t('main', 'files'),
			'category_id' => Yii::t('main', 'Category'),
		];
	}

	public function question()
	{
		$user = \Yii::$app->user->identity;

		$question = new Question();
		$question->description = $this->description;
		$question->text = $this->text;
		$question->user_id = $user->id;
		$question->status = Question::STATUS_PENDING;
		$keys = array_keys($_FILES);
		$response = [];
		foreach ($keys as $key) {
			$files = UploadedFile::getInstancesByName($key);
			if (count($files)) {
				foreach ($files as $file):
					$model = new Files();
					$model->file_data = $file;
					if ($model->save()) {
						$question->files .= $model->file_id . ",";
					}
					if ($model->hasErrors()):
						$response['errors'] = $model->getErrors();
						return ['error' => 'error 1'];//$response;
					endif;
				endforeach;
			}
		}

		$res = $question->save(false);
        if(is_array($this->category_id)){
            foreach ($this->category_id as $item) {
                $category = new QuestionCategory();
                $category->question_id = $question->id;
                $category->category_id = $item;
                $category->save(false);

            }
        }
        if (!$res) {
        	return $question->getErrors();
		}
		return $question;


	}

}