<?php

namespace api\modules\v1\controllers;

use api\components\ApiController;
use Codeception\PHPUnit\Constraint\Page;
use Yii;
use common\models\Pages;
use common\models\PagesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PagesController implements the CRUD actions for Pages model.
 */
/**
 * @api {get} /pages/:slug Информация ответы
 * @apiName GetPages
 * @apiGroup Pages
 *
 * @apiParam {String} slug Pages unique slug.
 * @apiSuccess {Number} id ID Страницу.
 * @apiSuccess {String} title Названия.
 * @apiSuccess {String} body Текст.
 * @apiSuccess {Number} view  Сколка посматрения.
 * @apiSuccess {Number} status  Статус 1 актив 0 неактив
 * @apiSuccess {Number} created_at  Время создания ответа.
 * @apiSuccess {Number} updated_at  Время  изменения моделя.
 * @apiSuccess {String} lang_hash Lang hash  для перовода система
 * @apiSuccess {Number} lang Язык страницу  1 - Uzbek; 2 - Узбек; 3 - Русский; 4 - English.
 */



class PagesController extends ApiController
{

    public $modelClass = Pages::class;
    public $searchModelClass = PagesSearch::class;

    public function actions()
    {
        $action = parent::actions();

        unset($action['view']);
        return $action;

    }

    public function actionView($slug)
    {
        $model = PagesSearch::find()->slug($slug);
        if ($model->count() == 0) {
            throw new NotFoundHttpException('Post not found');
        }

        $post = $model->one();
        $post->updateCounters(['view' => 1]);

        return $post;
    }
}
