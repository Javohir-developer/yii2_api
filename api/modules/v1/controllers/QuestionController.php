<?php

namespace api\modules\v1\controllers;

use api\components\ApiController;
use api\modules\v1\forms\AnswerForm;
use api\modules\v1\forms\QuestionForm;
use backend\models\User;
use common\models\Answer;
use common\models\Profile;
use common\models\Question;
use common\models\QuestionSearch;
use common\components\Categories;
use common\modules\langs\components\Lang;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * @api {get} /question/:id Информация вопроса
 * @apiName GetQuestion
 * @apiGroup Question
 *
 * @apiParam {Number} id Question unique ID.
 * @apiParam {string="answers"} [include] Дополнительные поля запроса
 *
 * @apiSuccess {Number} user_id Идентификатор Ползовителя
 * @apiSuccess {String} description Названия Вопроса.
 * @apiSuccess {Number} text Текст вопроса.
 * @apiSuccess {Number} question_id  Вопроса ID.
 * @apiSuccess {String} files  Файл  вопроса: документы и картинки.
 * @apiSuccess {Number} to_user_id  ID Ползовитель что даль эму вопрос или ответил этот вопрос, Эсли NULL  нет ответа.
 * @apiSuccess {Number} view  Счётчик просмотров.
 * @apiSuccess {Object} penalties  Наказания.
 * @apiSuccess {Number} answer_count  Счетчик ответ.
 * @apiSuccess {Object} categories  Категория вопроса.
 * @apiSuccess {Number} created_at  Время создания вопроса.
 * @apiSuccess {Number} updated_at  Время  изменения моделя.
 * @apiSuccess {Number} status  Статус вопроса 1 - актив, 0 - неактив.
 * @apiSuccess {Object} user Ползователь задавший вопрос.
 * @apiSuccess {Number} id  ID Ползователь задавший вопрос
 * @apiSuccess {String} full_name  ИМЯ Ползователь задавший вопрос
 * @apiSuccess {String} phone  Телефон Ползователь задавший вопрос
 * @apiSuccess {String} email  Email Ползователь задавший вопрос
 * @apiSuccess {String} image Картинки icon - маленкий w = 50, h =50; small - w=320, h=320; low - w= 40, h=640; normal -
 *  w=1024, h=1024
 * @apiSuccess {null} to_user Вопрос не адресован никому.
 * @apiSuccess {Object} to_user Ползователь ответяший вопрос.
 * @apiSuccess {Number} id  ID Ползователь ответяший вопрос
 * @apiSuccess {String} full_name  ИМЯ Ползователь ответяший вопрос
 * @apiSuccess {String} phone  Телефон Ползователь ответяший вопрос
 * @apiSuccess {String} email  Email Ползователь ответяший вопрос
 * @apiSuccess {String} image Картинки icon - маленкий w = 50, h =50; small - w=320, h=320; low - w= 40, h=640; normal -
 *  w=1024, h=1024
 */

/**
 * @api {get} /question/add Дабавить вопроса
 * @apiName AddQuestion
 * @apiGroup Question
 *
 * @apiParam {String} description Названия вопроса.
 * @apiParam {String} text  Текст вопроса.
 * @apiParam {String} files  Файл.
 * @apiParam {Array} category_id  Категория вопроса category_id[].
 * @apiSuccess {String} status Статус  true  - ОК
 * @apiSuccess {String} message Собшения "Question successfully added"
 *
 */

/**
 * @api {get} /question/add-answer Дабавить ответь
 * @apiName AddAnswer
 * @apiGroup Question
 *
 * @apiParam {String} question_id ID вопроса.
 * @apiParam {String} text  Текст ответа.
 * @apiParam {String} files  Файл.
 *
 * @apiSuccess {String} status Статус  true  - ОК
 * @apiSuccess {String} message Собшения "Answer successfully added"
 *
 *
 */

/**
 * @api {get} /question/:id/answers Запрос ответа
 * @apiName GetAnswers
 * @apiGroup Question
 *
 * @apiParam {Number} id ID вопроса для которых запрашивается ответы
 *
 * @apiSuccess {Number} user_id Ползователь дают ответа.
 * @apiSuccess {Number} question_id
 *
 *
 */

/**
 * @api {get} /question/all-categories Запрос Вс категория
 * @apiName GetAll-categories
 * @apiGroup Question
 *
 * @apiParam {String} all-categories Запрос ВСЕ категория
 *
 * @apiSuccess {Number} id Категория UNIQUE ID.
 * @apiSuccess {String} name Названия
 * @apiSuccess {number} root Древо
 * @apiSuccess {String} slug URL категория
 * @apiSuccess {Number} active Статус 1 - актив; 0 - не актив
 * @apiSuccess {Number} type  Тип 100 - Посты, 200 = Странице, 300 = Направлении
 *
 *
 */

/**
 * @api {get} /question/category/:slug Запрос категория
 * @apiName GetCategory
 * @apiGroup Question
 *
 * @apiParam {String} slug Slug категория
 *
 * @apiSuccess {Object} question Вопросы
 *
 */
class QuestionController extends ApiController
{
	public $modelClass = Question::class;
	public $searchModelClass = QuestionSearch::class;

	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view']);
		return $actions;
	}
//
//    public function actionIndex() {
//	    $request_params = \Yii::$app->request->getBodyParams();
//	    if (empty($request_params)) {
//	        $request_params = \Yii::$app->request->queryParams;
//        }
//
//        if (array_key_exists('sort', $request_params) && strpos($request_params['sort'], 'answer_count') !== FALSE) {
//
//        }
//	    return array($request_params);
//    }

	/**
	 * @param $slug
	 * @return ActiveDataProvider
	 * @throws NotFoundHttpException
	 */
	public function actionCategory($slug)
	{
		$category = Categories::find()->slug($slug)->one();

		if (!$category instanceof Categories) {
			throw new NotFoundHttpException("Category not found");
		}

		$query = Question::find()->category($slug);

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		return $dataProvider;
	}

	public function actionView($id)
	{
		$model = Question::findOne($id);
		if ($model) {
			$model->updateCounters(['view' => 1]);
			return $model;
		}
		throw new NotFoundHttpException('Вопрос не существует');
	}

	public function actionAllCategories()
	{
        if (in_array(\Yii::$app->language, array('oz', 'uz'))) {
            $category =  Categories::find()->where(['type'=> 300, 'lang' => Lang::getLangId('uz')]);
        }else{
            $category = Categories::find()->where(['type'=> 300, 'active'=> '1'])->lang();
        }
		$data = $category->all();
		if ($category->count() > 0) {
			unset($data[0]);
		}
		$dataProvider = new ArrayDataProvider([
			'allModels' => $data,
		]);
		return $dataProvider;

	}


	public function actionAnswer($id = null)
	{
		$answers = Answer::find()->where(['question_id' => $id]);
		$dataProvider = new ActiveDataProvider([
			'query' => $answers,
		]);

		return $dataProvider;
	}


	public function actionAdd()
	{
		if (Yii::$app->user->identity->status == User::STATUS_ACTIVE && !Yii::$app->user->isGuest) {

			$model = new QuestionForm();
			if ($model->load(\Yii::$app->request->post(), '')) {
				return $model->question();
			}
		} else {
			Yii::$app->response->setStatusCode(401);
			return false;
		}


		return $model->getErrors();

	}

	public function actionAddAnswer()
	{
		$model = new AnswerForm();
		$user = \Yii::$app->user->identity;
		if (($user->status == User::STATUS_ACTIVE) && (($user->type == User::TYPE_ADVOCATE) || ($user->type == User::TYPE_JURIST) || $user->type == User::TYPE_ADMIN)) {
		    if($user->profile->status == Profile::STATUS_ACTIVE){
                $model->load(\Yii::$app->request->post(), '');
                if ($answer = $model->answer()) {
                    return $answer;
                } else {
                    return $model->getErrors();
                }
            }else{
		     throw new ForbiddenHttpException("Не активирован профиль");
            }

		}
		throw new UnauthorizedHttpException('Недостаточно прав для ответа');
	}

	public function actionUpdate($id)
	{
		$model = Question::find()->where(["id" => $id])->one();
		$user_id = Yii::$app->user->identity->id;
		if ($user_id != $model->user_id) {
			Yii::$app->response->setStatusCode(401);
			return ['status' => 'error', 'message' => 'You don\'t have permission to change this question'];
		}
		$model->load(Yii::$app->request->post(), '');
		$model->status = Question::STATUS_PENDING;
		$model->user_id = $user_id;
		if ($model->save(false)) {
			return ['status' => 'success', 'message' => 'Question updated successfully'];
		} else {
			Yii::$app->response->setStatusCode(422);
			return $model->getErrors();
		}
	}

    /**
     * @param $id
     * @return \yii\data\ActiveDataProvider
     * @throws NotFoundHttpException
     */
    public function actionRelated($id)
    {
        $model = Question::find()->where(['id' => $id])->one();
        if (!$model instanceof Question) {
            throw new NotFoundHttpException('Post not found');
        }

//        $post = Post::find()->where(['lang_hash' => $model->lang_hash])->lang()->one();

        $post = $model;

        if (!$post instanceof Question) {
            throw new NotFoundHttpException('Post not found');
        }

        $postSearch = new QuestionSearch();
        $postSearch->detachBehaviors();

        $categories = ArrayHelper::getColumn($post->categories, 'id');

        $postSearch->category = $categories;
        $postSearch->current_post = $post->id;
        $dataProvider = $postSearch->search(\Yii::$app->request->queryParams);
        $dataProvider->query->andWhere(['q.status'=> Question::STATUS_ACTIVE]);
        $dataProvider->pagination->pageSize = 6;
        return $dataProvider;

    }

	public function actionDeactive()
	{
		$user = Yii::$app->user->identity;
		$query = Question::find()->andWhere(['user_id' => $user->id, 'status' => Question::STATUS_DEACTIVE]);

		return $this->getFilteredData($query, QuestionSearch::class);
    }

	public function actionSearch($q)
	{
		$query = Question::find()->joinWith('categories c')->andFilterWhere(['like', 'question.description',$q])->orWhere(['like', 'text', $q]);

		if (strpos($q, ' ')) {
			$words = explode(' ', $q);
			$query->orFilterWhere(['in', 'question.description', $words])->orFilterWhere(['in', 'text', $words]);
		}
		$cat = Yii::$app->request->get('cat');
		if (!empty($cat)){
			$query->andWhere(['in', 'c.id', $cat]);
		}

		return $this->getFilteredData($query, QuestionSearch::class);
    }
}