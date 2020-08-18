<?php

namespace api\modules\v1\controllers;

use api\components\ApiController;
use api\modules\v1\forms\BlogForm;
use common\models\Post;
use common\models\PostSearch;
//use oks\categories\models\Categories;
use common\components\Categories;
use common\models\User;
use common\modules\langs\components\Lang;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * @api {get} /post/:slug Информация пост (View)
 * @apiName GetPost
 * @apiGroup Post
 *
 * @apiParam {String} slug Post unique slug.
 * @apiSuccess {Number} id ID Страницу.
 * @apiSuccess {String} title Названия.
 * @apiSuccess {String} intro Короткий текст (description).
 * @apiSuccess {String} content Текст.
 * @apiSuccess {Number} view  Сколка посматрения.
 * @apiSuccess {Number} status  Статус 1 актив 0 неактив
 * @apiSuccess {Number} type  NEWS = 1; BLOG = 2; ARTICLE = 3;
 * @apiSuccess {Number} top  Для паказать оделний
 * @apiSuccess {Number} published_on  Время публикатция.
 * @apiSuccess {Number} created_at  Время создания ответа.
 * @apiSuccess {Number} updated_at  Время  изменения моделя.
 * @apiSuccess {String} image Картинки icon - маленкий w = 50, h =50; small - w=320, h=320; low - w= 40, h=640; normal -
 *  w=1024, h=1024
 * @apiSuccess {String} lang_hash Lang hash  для перовода система
 * @apiSuccess {Number} lang Язык страницу  1 - Uzbek; 2 - Узбек; 3 - Русский; 4 - English.
 *
 * @apiSuccess {Array} categories Категория на пости
 *
 *
 */

/**
 * @api {get} /post/all-categories Запрос Все категория
 * @apiName GetAll-categories
 * @apiGroup Post
 *
 * @apiParam {String} all-categories Запрос категория
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
 * @api {get} /post/category/:slug Запрос категория
 * @apiName GetCategory
 * @apiGroup Post
 *
 * @apiParam {String} slug Slug категория
 *
 * @apiSuccess {Object} post Посты
 *
 */


/**
 * @api {post} /post/ Создать блог пость
 * @apiName CreatePost
 * @apiGroup Post
 *
 * @apiParam {String} title Названия блога
 * @apiParam {String} intro Description
 * @apiParam {String} content Контент
 * @apiParam {Number} lang Контент
 * @apiParam {Number} published_on Дата когда публиковать
 * @apiParam {Array} category_id[] Категорий
 * @apiParam {String} image Картинка
 *
 *
 * @apiSuccess {Object} post Блог
 *
 */

class PostController extends ApiController
{

	public $modelClass = Post::class;
	public $searchModelClass = PostSearch::class;

	public function actions()
	{
		$action = parent::actions();

		unset($action['view']);
		return $action;

	}


	/**
	 * @param $slug
	 * @return ActiveDataProvider
	 * @throws NotFoundHttpException
	 */
	public function actionCategory($slug)
	{
		$category = $this->getCategoryModelBySlug($slug);
		$query = PostSearch::find()->category($category->slug);

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		return $dataProvider;
	}

	/**
	 * @return ActiveDataProvider
	 */
	public function actionArticle()
	{
		$query = Post::find()->where(['type' => Post::TYPE_ARTICLE]);

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		return $dataProvider;
	}

	public function actionNew()
	{

	}

	/**
	 * @param $slug
	 * @return array|Post|\common\models\Posts|null|\yii\db\ActiveRecord
	 * @throws NotFoundHttpException
	 */
	public function actionView($slug)
	{
		$user = User::authorize();
		$post = Post::find()->slug($slug)->one();
		if ($user && $post->user_id == $user->id && $post->status != 2) {
			return $post;
		}
		else if ($post->status != 2) {
			throw new NotFoundHttpException('Post not found');
		}
		$post->updateCounters(['view' => 1]);
		return $post;
	}

	/**
	 * @param $slug
	 * @return array|null|Categories|\oks\categories\models\Category
	 * @throws NotFoundHttpException
	 */
	private function getCategoryModelBySlug($slug)
	{
		$category = Categories::find()->slug($slug)->one();

		if (!$category instanceof Categories) {
			throw new NotFoundHttpException("Category not found");
		}
		return $category;
	}



    public function actionAllCategories(){
        if (in_array(\Yii::$app->language, array('oz', 'uz'))) {
            $category =  Categories::find()->where(['type'=> 100, 'lang' => Lang::getLangId('uz')]);
        }else{
            $category = Categories::find()->where(['type'=> 100, 'active'=> '1'])->lang();
        }
        $data = $category->all();
        if($category->count() > 0){
            unset($data[0]);
        }
        $dataProvider = new ArrayDataProvider([
            'allModels' => $data,
        ]);
        return $dataProvider;

    }


    /**
     * @param $id
     * @return \yii\data\ActiveDataProvider
     * @throws NotFoundHttpException
     */
    public function actionRelated($slug)
    {
        $model = Post::find()->where(['slug' => $slug])->one();
        if (!$model instanceof Post) {
            throw new NotFoundHttpException('Post not found');
        }

//        $post = Post::find()->where(['lang_hash' => $model->lang_hash])->lang()->one();

        $post = $model;

        if (!$post instanceof Post) {
            throw new NotFoundHttpException('Post not found');
        }

        $postSearch = new PostSearch();
        $postSearch->detachBehaviors();

        $categories = ArrayHelper::getColumn($post->categories, 'id');

        $postSearch->category = $categories;
        $postSearch->current_post = $post->id;
        $dataProvider = $postSearch->search(\Yii::$app->request->queryParams);
        $dataProvider->query->active();

        return $dataProvider;

    }

    public function actionAdd(){
        $model = new BlogForm();
        if($model->load(\Yii::$app->request->post(), '')){
         return $model->save();
        }
        throw new UnauthorizedHttpException();
    }

	public function actionUpdate($slug)
	{
		$model = new BlogForm();
		$model->slug = $slug;
		if($model->load(\Yii::$app->request->post(), '')){
			return $model->save();
		}
		throw new UnauthorizedHttpException();
	}
}
