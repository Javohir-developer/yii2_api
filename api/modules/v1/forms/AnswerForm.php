<?php

namespace api\modules\v1\forms;

use backend\models\User;
use common\models\Answer;
use common\models\Question;
use oks\filemanager\models\Files;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;


class AnswerForm extends Model
{

	public $text;
	public $file;
	public $question_id;

	public function rules()
	{

		return [
			[['text', 'question_id'], 'required'],
			[['images'], 'safe'],
			[['question_id'], 'integer']
		];
	}


	public function attributeLabels()
	{
		return [
			'text' => Yii::t('main', 'Answer'),
			'file' => Yii::t('main', 'File'),
			'question_id' => Yii::t('main', 'Question_id'),

		];
	}

	public function answer()
	{
		$user = \Yii::$app->user->identity;
		if (!$this->validate()) {
			Yii::$app->response->setStatusCode(422);
			return $this->getErrors();
		}
		if ($user->type > 0) {
			$answer = new Answer();
			$answer->text = $this->text;
			$answer->question_id = $this->question_id;
			$answer->user_id = $user->id;
			$answer->status = 1;
			$keys = array_keys($_FILES);
			$response = [];
			foreach ($keys as $key) {
				$files = UploadedFile::getInstancesByName($key);
				if (count($files)) {
					foreach ($files as $file):
						$model = new Files();
						$model->file_data = $file;
						if ($model->save()) {
							$answer->files .= $model->file_id . ",";
						}
						if ($model->hasErrors()):
							$response['errors'] = $model->getErrors();
							return $response;
						endif;
					endforeach;
				}
			}

			$answer->save();
			return $answer;
		} else {
			return false;
		}
	}
}