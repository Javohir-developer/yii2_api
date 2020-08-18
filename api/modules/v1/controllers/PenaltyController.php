<?php

namespace api\modules\v1\controllers;

use api\components\ApiController;
use common\models\Penalty;
use common\models\PenaltySearch;
use common\models\Profile;

class PenaltyController extends ApiController
{
	public $modelClass = Penalty::class;
	public $searchModelClass = PenaltySearch::class;

	public function actionCreate()
	{
		$model = new Penalty();
		$model->load(\Yii::$app->request->post(), '');
		if ($model->validate()) {
			$model->status = Penalty::STATUS_PENDING;
			$model->save();
			return $model;
		}
		else {
			\Yii::$app->response->setStatusCode(422);
			return $model->getErrors();
		}
	}

	public function actionUpdate($id) {
		$model = Penalty::findOne(['id' => $id]);
		$model->load(\Yii::$app->request->post(),'');
		if ($model->validate()) {
			$model->save();
			return $model;
		}
		else {
			\Yii::$app->response->setStatusCode(422);
			return $model->getErrors();
		}
	}
}