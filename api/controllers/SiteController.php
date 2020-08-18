<?php
namespace api\controllers;

use api\components\ApiController;
use common\models\Contact;
use common\models\Mistakes;
use common\models\Post;
use common\models\PostSearch;
use common\models\Question;
use common\models\Subscribe;
use common\models\User;
use oks\categories\models\Categories;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use api\models\PasswordResetRequestForm;
use api\models\ResetPasswordForm;
use api\models\SignupForm;
use api\models\ContactForm;
use yii\web\NotFoundHttpException;

/**
 * Site controller
 */
class SiteController extends ApiController
{

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->asJson(array(
        	'status' => true,
			'message' => 'Welcome to legans.uz API v1.0.0'
		));
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';
//			Yii::$app->session->setFlash('error', __("Email yoki parol noto'g'ri kirtilgan, tekshirib qaytadan kiriting"));
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        return $this->render('contact');
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    public function actionSitemap()
    {
        return $this->render('sitemap');
    }

	public function actionAjax()
	{
		if (Yii::$app->request->isAjax) {

			$data = Yii::$app->request->post();

			switch ($data['id']) {
				case "subscribe" :
					if (count(Subscribe::find()->where(['email' => $data['email']])->andWhere(['status' => '1'])->all())) {
						return json_encode(['text' => __(""), 'type' => 'success']);
					}
					else {
						if ($model = Subscribe::find()->where(['email' => $data['email']])->one()) {
							$model->status = 1;
						} else {
							$model = new Subscribe();
							$model->email = $data['email'];
						}
						if ($model->save()) {
							return json_encode(['text' => __("Yangiliklarga obuna bo'ldingiz"), 'type' => 'success']);
						} else return json_encode(['text' => __("Email tekshirib qaytadan kiriting"), 'type' => 'error']);
					}
					break;

				case "contact" :
					$model = new Contact();
					if ($model->load($data) && $model->save()) {
						return __('Habaringiz qabul qilindi');
					}
					else return __('Habar qabul qilinmadi, Tekshirib qaytadan jo\'nating');
					break;

				case "mistakes" :
					$model = new Mistakes();
					if ($model->load($data) && $model->save()) {
						return __('Habaringiz qabul qilindi');
					}
					else return __('Habar qabul qilinmadi, Tekshirib qaytadan jo\'nating');
					break;

				default : break;
			}
		}
		return $this->goHome();
    }

    public function actionSettings()
    {
    	$model = User::findOne(['email' => Yii::$app->user->identity->email]);
    	$data = Yii::$app->request->post();
        if($model->load($data)){
        	if (!empty($data['User']['newPassword'])) {
				if (empty($data['User']['oldPassword'])) {
					Yii::$app->session->setFlash('error', __('Eski parolni kiriting'));
				} else {
					if ($model->validatePassword($data['User']['oldPassword'])) {
						$model->setPassword($data['User']['newPassword']);
						Yii::$app->session->setFlash('success', __('Parol o\'zgardi'));
					}
				}
			}
        	if ($model->save())
	        	Yii::$app->session->setFlash('success', "Qo'shimcha ma'lumotlar saqlandi");
        	else Yii::$app->session->setFlash('error', 'Saqlamnadi');
            return $this->refresh();
        }

        return $this->render('settings', ['model' => $model]);
    }

	public function actionSearch($q)
	{
		$posts = Post::find()->where(['like', 'content', $q])->orWhere(['like', 'title', $q])->all();
//		var_dump($posts); exit();
		return $this->render('search', ['model' => $posts]);
    }


    public function actionQuestion()
    {
        $model = new Question();
        if ($model->load(Yii::$app->request->post())) {
            $model->user_id = Yii::$app->user->identity->id;
            if($model->save()){
                Yii::$app->session->setFlash('success', __('Joylandi'));

                return $this->redirect('/question');
            }

        }
        var_dump($model->getErrors());
        exit();
      //  return $this->redirect('/question');
    }


    public function actionCategory($slug = null)
    {
        $query_c = Categories::find()->andWhere(['categories.slug' => $slug])->orderBy(['categories.id' => SORT_DESC]);
        if($query_c->count() == 0){
            throw new BadRequestHttpException('Xatolik');
        }
        $searchModel = new PostSearch();
        $searchModel->category = $query_c->one()->id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->pagination->pageSize = Post::params()['archive']['pagination']['pagSize'];
        $dataProvider->query->where(['status'=>'1'])->category($slug);
        $cat = Post::find()->category($slug)->orderBy(['post_id' => SORT_DESC])->one();
        $cat_title = Categories::find()->where(['categories.slug'=> $slug])->one();
        return $this->render("category",[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'cat' =>$cat,
            'cat_title' => $cat_title
        ]);
    }


}
