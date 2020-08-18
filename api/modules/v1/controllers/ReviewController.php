<?php

namespace api\modules\v1\controllers;

use api\components\ApiController;
use common\models\User;
use common\models\Profile;
use common\models\Review;
use common\models\ReviewSearch;

/**
 * @api {post} /review/create Дабавить отзывы
 * @apiName CreateReview
 * @apiGroup Review
 *
 * @apiParam {Number} rating Бал 1 до 5.
 * @apiParam {String} text  Текст отзыва.
 * @apiParam {Number} question_id  ID вопроса.
 * @apiParam {Number} type  Тип 0 - отрацителний, 1 - положительний.
 * @apiParam {Number} user_id Автор ответа(User для которого пишется отзыв).
 *
 *
 * @apiSuccess {Object} model Отзывь
 *
 */
class ReviewController extends ApiController
{
	public $modelClass = Review::class;
	public $searchModelClass = ReviewSearch::class;

	public function actionCreate()
	{
		$model = new Review();
		$model->load(\Yii::$app->request->post(), '');
		$user = User::authorize();
		$model->from_user_id = $user->id;
		if ($model->save()) {
			$profile = Profile::find()->where(['user_id' => $model->user_id])->one();
			if ($model->type)
				$profile->rating += $model->rating;
			else {
				$profile->rating -= round($profile->rating * 0.05);
			}
			$profile->save();
			return $model;
		} else {
			\Yii::$app->response->setStatusCode(422);
			return $model->getErrors();
		}
	}

}