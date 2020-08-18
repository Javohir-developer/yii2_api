<?php

namespace api\modules\v1\controllers;

use api\components\ApiController;
use api\modules\v1\forms\ProfileUpdateForm;
use common\models\User;
use common\models\Award;
use common\models\Education;
use common\models\Experience;
use common\models\LawFields;
use common\models\Profile;
use common\models\ProfileSearch;
use common\components\Categories;
use common\modules\langs\components\Lang;
use oks\filemanager\models\Files;
use yii\data\ArrayDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UploadedFile;
/**
 * @api {get} /profile/ Профил Листь
 * @apiName ProfileList
 * @apiGroup Profile
 *
 * @apiParam {String} all-categories Запрос категория
 *
 * @apiSuccess {Number} id Профил UNIQUE ID.
 * @apiSuccess {String} full_name Имя Профиля
 * @apiSuccess {String} phone Телефон
 * @apiSuccess {String} email Email
 * @apiSuccess {String} bio Биография
 * @apiSuccess {Number} rating Рейтинг
 * @apiSuccess {Number} top ТОП
 * @apiSuccess {Object} region Область
 * @apiSuccess {Object} city Город
 * @apiSuccess {Object} address Улица
 * @apiSuccess {Object} user Ползавитель
 * @apiSuccess {Number} type value JOURNALIST = 3; JURIST = 2; ADVOCATE = 1; USER = 0.
 */

/**
 * @api {get} /profile/:id Запрос Ползаватель
 * @apiName ProfileID
 * @apiGroup Profile
 *
 * @apiParam {Number} id Запрос Ползаватель
 *
 * @apiSuccess {Number} id Профил UNIQUE ID.
 * @apiSuccess {String} full_name Имя Профиля
 * @apiSuccess {String} phone Телефон
 * @apiSuccess {String} email Email
 * @apiSuccess {String} bio Биография
 * @apiSuccess {Number} rating Рейтинг
 * @apiSuccess {Number} top ТОП
 * @apiSuccess {Object} region Область
 * @apiSuccess {Object} city Город
 * @apiSuccess {Object} address Улица
 * @apiSuccess {Object} user Ползователь
 * @apiSuccess {Number} type value JOURNALIST = 3; JURIST = 2; ADVOCATE = 1; USER = 0.
 */
/**
 * @api {post} /profile/add-edu/  Дабавить образование
 * @apiName AddEdu
 * @apiGroup Profile
 *
 * @apiParam {String} name Названия
 * @apiParam {String} faculty Факултет
 * @apiParam {String} country Страна
 * @apiParam {String} city Город
 * @apiParam {Number} grad_year Окончание образование
 * @apiParam {File} files Файл
 *
 * @apiSuccess {Array} success Education saved.
 * @apiSuccess {Array} error Can't save Education.
 */

/**
 * @api {post} /profile/add-award/  Дабавить Награды или Грамоты
 * @apiName AddAward
 * @apiGroup Profile
 *
 * @apiParam {String} name Названия
 * @apiParam {String} description О Награды или Грамоты
 * @apiParam {Number} year Год
 * @apiParam {File} files Файл
 *
 * @apiSuccess {Array} success Award saved.
 * @apiSuccess {Array} error Can't save Award.
 *
 */
/**
 * @api {post} /profile/add-exp/  Дабавить Опыть
 * @apiName AddExperience
 * @apiGroup Profile
 *
 * @apiParam {String} company_name Имя Компания
 * @apiParam {String} position Кем был или работал
 * @apiParam {Number} date_begin Год начала timestamp()
 * @apiParam {Number} date_end Год окончания работа timestamp()
 * @apiParam {String} description Описание описания
 *
 * @apiSuccess {Array} success Experience saved.
 * @apiSuccess {Array} error Can't save Experience.
 *
 */
/**
 * @api {post} /profile/change-photo/  Дабавить Изображения
 * @apiName AddChangePhoto
 * @apiGroup Profile
 *
 * @apiParam {String} files  Изображения
 *
 * @apiSuccess {Array} success Готов.
 * @apiSuccess {Number} user_id User id  присвязн на Профиль.
 * @apiSuccess {Number} date_create  Создания дата.
 * @apiSuccess {String} title  названия файла.
 * @apiSuccess {String} description  названия файла.
 * @apiSuccess {String} description  названия файла.
 * @apiSuccess {String} type  Тип файла.
 * @apiSuccess {String} file  Хеш названия.
 * @apiSuccess {Number} file_id Файл ИД.
 * @apiSuccess {Array} error Can't save.
 *
 */

/**
 * @api {post} /profile/change-profile Изменение данных профиля
 * @apiName ChangeProfile
 * @apiGroup Profile
 *
 * @apiParam {String} full_name
 * @apiParam {String} email
 * @apiParam {String} old_password
 * @apiParam {String} password
 * @apiParam {String} region
 * @apiParam {String} bio
 * @apiParam {Integer} gender
 * @apiParam {Integer} region_id
 * @apiParam {Integer} city
 * @apiParam {String} adress
 * @apiParam {String} adress_desc
 * @apiParam {Array} category_id
 *
 * @apiSuccess {Object} Profile Возвращает измененные данные
 *
 * @return ProfileUpdateForm|array|null
 * @throws UnprocessableEntityHttpException
 */

/**
 * @api {post} /profile/add-category Дабавить категория ползователя
 * @apiName AddCategory
 * @apiGroup Profile
 *
 * @apiParam {Array} category_id  ID Категорий ползователя
 *
 * @apiSuccess {Array} Category Сахранёние категории
 *
 *
 */
/**
 * @api {delete} /profile/delete-category/:id Удалить категория ползователя
 * @apiName DeleteCategory
 * @apiGroup Profile
 *
 * @apiParam {Number} id Категория ID
 *
 * @apiSuccess {Success} 204 Success
 *
 */




class ProfileController extends ApiController
{
    public $modelClass = Profile::class;
    public $searchModelClass = ProfileSearch::class;



    public function actionAllCategories(){
        if (in_array(\Yii::$app->language, array('oz', 'uz'))) {
            $category =  Categories::find()->where(['type'=> 300, 'lang' => Lang::getLangId('uz')]);
        }else{
            $category = Categories::find()->where(['type'=> 300, 'active'=> '1'])->lang();
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

    public function actionAddExp()
    {
        $exp = new Experience();
        $exp->load(\Yii::$app->request->post(), '');
        $exp->profile_id = \Yii::$app->user->identity->profile->id;

        if ($exp->save()) {
            return Experience::findAll(['profile_id' => $exp->profile_id]);
        } else {
            \Yii::$app->response->setStatusCode(422);
            return $exp->getErrors();
        }
    }

    public function actionAddEdu()
    {
        $edu = new Education();
        $edu->load(\Yii::$app->request->post(), '');

        $keys = array_keys($_FILES);
        $response = [];
        foreach ($keys as $key) {
            $files = UploadedFile::getInstancesByName($key);
            if (count($files)) {
                foreach ($files as $file):
                    $model = new Files();
                    $model->file_data = $file;
                    if ($model->save()) {
                        $edu->files .= $model->file_id . ",";
                    }
                    if ($model->hasErrors()):
                        $response['errors'] = $model->getErrors();
                        return $response;
                    endif;
                endforeach;
            }
        }

        $profile = \Yii::$app->user->identity->profile;
        $edu->profile_id = $profile->id;
        if ($edu->save()) {
            $progress = 0;
            foreach ($profile->getAttributes(['bio', 'address', 'adress_desc', 'city_id', 'region_id', 'gender']) as $attribute) {
                if ($attribute != null) $progress++;
            }
            foreach ($edu->getAttributes(['name','faculty','country','city','grad_year','files']) as $attribute) {
                if ($attribute !== null) $progress++;
            }
            $progress = ($progress / 12) * 100;
            $profile->progress = $progress;
            if ($profile->status == Profile::STATUS_DEACTIVE)
            	$profile->status = Profile::STATUS_WAITING;
            $profile->save();
            return $edu;
        } else {
            \Yii::$app->response->setStatusCode(422);
            return $edu->getErrors();
        }
    }

    public function actionAddAward()
    {
        $award = new Award();
        $award->load(\Yii::$app->request->post(), '');

        $keys = array_keys($_FILES);
        $response = [];
        foreach ($keys as $key) {
            $files = UploadedFile::getInstancesByName($key);
            if (count($files)) {
                foreach ($files as $file):
                    $model = new Files();
                    $model->file_data = $file;
                    if ($model->save()) {
                        $award->files .= $model->file_id . ",";
                    }
                    if ($model->hasErrors()):
                        $response['errors'] = $model->getErrors();
                        return $response;
                    endif;
                endforeach;
            }
        }
        $award->profile_id = \Yii::$app->user->identity->profile->id;
        if ($award->save()) {
            return $award;
        } else {
            \Yii::$app->response->setStatusCode(422);
            return $award->getErrors();
        }
    }

    /**
     * @throws BadRequestHttpException
     */
    public function actionChangePhoto()
    {
        $keys = array_keys($_FILES);

        if (!count($keys)) {
            throw new BadRequestHttpException('Profile a photo');
        }
        $response = [];
        foreach ($keys as $key) {
            $files = UploadedFile::getInstancesByName($key);
            if (count($files)) {
                foreach ($files as $file):
                    $model = new Files();
                    $model->file_data = $file;
                    if ($model->save()) {

                        \Yii::$app->user->identity->profile->updateAttributes(['image' => $model->file_id]);

                        $response = \Yii::$app->user->identity->profile->filesSrc;
                    }
                    if ($model->hasErrors()):
                        \Yii::$app->response->statusCode = 422;
                        $response['errors'] = $model->getErrors();
                    endif;
                endforeach;
            }
        }
        return $response;

    }


    public function actionUpdateExp($id)
    {
        $exp = Experience::findOne($id);
        $exp->load(\Yii::$app->request->post(), '');
        $exp->profile_id = \Yii::$app->user->identity->profile->id;

        if ($exp->save()) {
            return $exp;
        } else {
            throw new UnprocessableEntityHttpException();
        }
    }

    /**
     * Update Education
     * @param $id integer ID of education
     * @return array|Education
     */
    public function actionUpdateEdu($id)
    {

        $edu = Education::findOne($id);

        $file_id = explode(',', $edu->files);

        $edu->load(\Yii::$app->request->post(), '');
        $keys = array_keys($_FILES);
        if(!empty($keys)){
            $response = [];
            foreach ($keys as $key) {
                $files = UploadedFile::getInstancesByName($key);
                if (count($files)) {
                    foreach ($files as $file):
                        $model = new Files();
                        $model->file_data = $file;
                        if ($model->save()) {
                            $edu->files .= $model->file_id . ",";
                        }
                        if ($model->hasErrors()):
                            $response['errors'] = $model->getErrors();
                            return $response;
                        endif;
                    endforeach;
                }
            }
        }
        $profile = \Yii::$app->user->identity->profile;
        $edu->profile_id = $profile->id;
        if ($edu->save()) {
            $progress = 0;
            foreach ($profile->getAttributes(['bio', 'address', 'adress_desc', 'city_id', 'region_id', 'gender']) as $attribute) {
                if ($attribute != null) $progress++;
            }
            foreach ($edu->getAttributes(['name','faculty','country','city','grad_year','files']) as $attribute) {
                if ($attribute !== null) $progress++;
            }
            $progress = ($progress / 12) * 100;
            $profile->progress = $progress;
            $profile->save();
            return $edu;
        } else {
            \Yii::$app->response->setStatusCode(422);
            return $edu->getErrors();
        }
    }

    /**
     * Update Award
     * @param $id
     * @return array|string
     * @throws UnauthorizedHttpException
     */

    public function actionUpdateAward($id)
    {

        $award = Award::find()->where(['id'=>$id])->one();
        $award->load(\Yii::$app->request->post(), '');
        if($award->profile_id == \Yii::$app->user->identity->profile->id){

            $keys = array_keys($_FILES);
            if($keys){
                $response = [];
                foreach ($keys as $key) {
                    $files = UploadedFile::getInstancesByName($key);
                    if (count($files)) {
                        foreach ($files as $file):
                            $model = new Files();
                            $model->file_data = $file;
                            if ($model->save()) {
                                $award->files .= $model->file_id . ",";
                            }
                            if ($model->hasErrors()):
                                $response['errors'] = $model->getErrors();
                                return $response;
                            endif;
                        endforeach;
                    }
                }
            }

            $award->profile_id = \Yii::$app->user->identity->profile->id;
            if ($award->save()) {
                return $award;
            } else {
                \Yii::$app->response->setStatusCode(422);
                return $award->getErrors();
            }
        }else{
            throw new UnauthorizedHttpException('You don\'t have permission to change this');
        }
    }


    public function actionChangeProfile()
    {
        $model = new  ProfileUpdateForm();
        $model->load(\Yii::$app->request->post(), '');
        return $model->update();
    }


    public function actionAddCategory()
    {
        $profile = User::authorize();
        $model = \Yii::$app->request->post();
        if(is_array($model['category_id'])){
            foreach ($model['category_id'] as $item) {
                $law = new LawFields();
                $law->profile_id = $profile->profile->id;
                $law->category_id = $item;
                $law->save();
            }
            $lawField = LawFields::findAll(['profile_id' => $profile->profile->id]);
            $cat = [];
            foreach ($lawField as $item) {
            	$cat[] = $item->category;
			}
			return $cat;
        }
        throw new UnprocessableEntityHttpException("Wrong data sent");

    }

    public function actionDeleteCategory($id)
    {
        $profile = User::authorize();
        if($profile){
            $law = LawFields::find()->where(['category_id'=>$id,'profile_id'=> $profile->profile->id])->one();
            $law->delete();
            \Yii::$app->response->setStatusCode(204);
            return ['category_id'=> $id];
        }

        throw new UnauthorizedHttpException('не достотична прав');
    }

	public function actionExp()
	{
		$user = User::authorize();

		return Experience::findAll(['profile_id' => $user->profile->id]);
    }

    public function actionAward()
    {
        $user = User::authorize();

        return Award::findAll(['profile_id' => $user->profile->id]);
    }

}