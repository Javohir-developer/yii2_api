<?php

namespace api\modules\v1\controllers;

use api\components\ApiController;
use common\models\Answer;
use common\models\AnswerSearch;
use Yii;

/**
 * @api {get} /answer/:id Информация ответы
 * @apiName GetAnswer
 * @apiGroup Answer
 *
 * @apiParam {Number} id Answer unique ID.
 *
 * @apiSuccess {String} text Ответ.
 * @apiSuccess {Number} user_id Кто ответиль ID user.
 * @apiSuccess {Number} question_id  Вопроса ID.
 * @apiSuccess {String} files  Файл  ответа.
 * @apiSuccess {Number} created_at  Время создания ответа.
 * @apiSuccess {Number} updated_at  Время  изменения моделя.
 * @apiSuccess {Number} status  Статус ответа.
 */


class AnswerController extends ApiController
{
	public $modelClass = Answer::class;
	public $searchModelClass = AnswerSearch::class;

	public function actionUpdate($id)
	{
		$model = Answer::find()->where(["id" => $id])->one();
		$user_id = Yii::$app->user->identity->id;
		if ($user_id != $model->user_id) {
			Yii::$app->response->setStatusCode(401);
			return ['status' => 'error', 'message' => 'You don\'t have permission to change this answer'];
		}
		$model->load(Yii::$app->request->post(), '');
		$model->user_id = $user_id;
		if ($model->save()) {
			return ['status' => 'success', 'message' => 'Question updated successfully'];
		} else {
			Yii::$app->response->setStatusCode(422);
			return $model->getErrors();
		}
	}

}