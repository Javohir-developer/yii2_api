<?php

namespace api\modules\v1\controllers;

use api\components\ApiController;
use api\modules\v1\forms\SignupForm;
use api\modules\v1\forms\SigninForm;
use common\models\AnswerSearch;
use common\models\FavoritePost;
use common\models\FavoriteQuestion;
use common\models\PostSearch;
use common\models\Question;
use common\models\QuestionSearch;
use common\models\ReviewSearch;
use common\models\User;
use common\models\UserSearch;
use common\modules\playmobile\models\PhoneConfirmation;
use GuzzleHttp\Client;
use Yii;
use yii\data\ActiveDataFilter;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;

/**
 * @var User \Yii->$app->user->identity
 */

/**
 * @api {get} /user Запрос списка пользователей
 * @apiName GetUserList
 * @apiGroup User
 *
 * @apiParam {String} [_f=JSON] Формат ответа, может быть XML или JSON
 *
 * @apiSuccess {Array} data Массив пользователей
 * @apiSuccess {String} full_name Имя User.
 * @apiSuccess {String} phone Телефон пользователей.
 * @apiSuccess {String} email  Email Ползователь ответяший вопрос
 * @apiSuccess {String} image Картинки icon - маленкий w = 50, h =50; small - w=320, h=320; low - w= 40, h=640; normal -
 * @apiSuccess {null} image Нет картинку
 */

/**
 * @api {get} /user/:id Запрос пользователей
 * @apiName GetUser
 * @apiGroup User
 *
 * @apiParam {String} [_f=JSON] Формат ответа, может быть XML или JSON
 *
 * @apiParam {Number} id Question unique ID.
 *
 * @apiSuccess {String} full_name Имя User.
 * @apiSuccess {String} phone Телефон пользователей.
 * @apiSuccess {String} email  Email Ползователь ответяший вопрос
 * @apiSuccess {String} image Картинки icon - маленкий w = 50, h =50; small - w=320, h=320; low - w= 40, h=640; normal -
 * @apiSuccess {null} image Нет картинку
 */

/** @api {get} /user?include=reviews Запрос Отзыви
 * @apiName GetReviews
 * @apiGroup User
 *
 * @apiSuccess {Array} reviews Отзыви
 * @apiSuccess {null} reviews Нет Отзыви
 * @apiSuccess {Number} id UNIQUE Ползователь
 * @apiSuccess {Number} user_id Пользователь даль ID
 * @apiSuccess {Number} question_id Вопрос ID что дал отзивь
 * @apiSuccess {Number} from_user_id User ID
 * @apiSuccess {String} text Текст
 * @apiSuccess {Number} rating Рейтинг 1 до 10 балл
 * @apiSuccess {Number} created_at  Время создания Отзыви.
 * @apiSuccess {Number} updated_at  Время  изменения моделя.
 * @apiSuccess {Number} type  Тип  0 - Негативний,  1 - Позитивный
 *
 *
 */

/**
 * @api {post} /user/signup  Регистратсия пользователей
 * @apiName RegUser
 * @apiGroup User
 *
 * @apiParam {Number} type value JOURNALIST = 3; JURIST = 2; ADVOCATE = 1; USER = 0.
 * @apiParam {String} full_name Имя User.
 * @apiParam {String} password Пароль пользователей.
 * @apiParam {String} phone Телефон пользователей.
 * @apiParam {String} company Название компании. Должен выводится только если выбран тип компания или бюро
 *
 * @apiSuccess {String} token Токен для пользователя
 */


/**
 * @api {post} /user/signin Вход пользователей
 * @apiName SigninUser
 * @apiGroup User
 *
 * @apiParam {String} login Логин пользователей (email  или  телефон).
 * @apiParam {String} password Телефон пользователей.
 *
 * @apiSuccess {String} token Токен для пользователя
 * @apiSuccess {null} token Токен нет ошибка логин или пароль
 */


/**
 * @api {post} /user/change-password Новый пароль пользователей Нужна ТОКЕН
 * @apiName Сhange-passwordUser
 * @apiGroup User
 *
 * @apiParam {String} password Новый Пароль.
 * @apiParam {String} password_confirm Новый повтор Пароль.
 *
 * @apiSuccess {Object} user Информация пользователя
 */

/**
 * @api {get} /user/get-me Данные авторизованного пользователя
 * @apiName GetMe
 * @apiGroup User
 *
 * @apiSuccess {Number} id ID User
 * @apiSuccess {Number} type value JOURNALIST = 3; JURIST = 2; ADVOCATE = 1; USER = 0.
 * @apiSuccess {String} full_name Имя Фамилия Отчество
 * @apiSuccess {String} email Email
 * @apiSuccess {Number} phone Телефон
 * @apiSuccess {Array} image Картинка
 *
 */

/**
 * @api {post} /user/confirm/ Подтверждение пользователя
 * @apiName ConfirmReg
 * @apiGroup User
 *
 * @apiParam {Number} phone Телефон пользователя
 * @apiParam {Number} code Код активатсия
 *
 * @apiSuccess {Object} user Данний пользователя
 */

/**
 * @api {post} /user/change-phone Изменения номер пользователя
 * @apiName ChangePhone
 * @apiGroup User
 *
 * @apiParam {Number} phone Новый телефон пользователя, Пользовател должен быт авторизованный
 *
 * @apiSuccess {Object} user Данний пользователя
 */

/**
 * @api {post} /user/restore-password Изменения  пароль Не нужна Токен
 * @apiName ChangePassword
 * @apiGroup User
 *
 * @apiParam {Number} phone Телефон номер пользователя, новый пароль отправится sms
 *
 * @apiSuccess {Array} status "message": "New password successfully sent"
 * }
 */
class UserController extends ApiController
{
	public $modelClass = User::class;
	public $searchModelClass = UserSearch::class;

	public function actionGetMe()
	{
		return \Yii::$app->user->identity;
	}

	/**
	 * @return User|null|\yii\web\IdentityInterface
	 * @throws ServerErrorHttpException
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionUpdateMe()
	{
		$requestParams = \Yii::$app->request->getBodyParams();

		if (empty($requestParams)) {
			$requestParams = \Yii::$app->request->getQueryParams();
		}

		/**
		 * @var User $user
		 */
		$user = \Yii::$app->user->identity;
		$user->updateAttributes($requestParams);

		if (!$user->save()) {
			throw new ServerErrorHttpException("Unknown Error: Data does not updated");
		}

		return $user;
	}

	/**
	 * @return User
	 * @throws ServerErrorHttpException
	 * @throws \yii\base\InvalidConfigException
	 * @throws BadRequestHttpException
	 * @throws \yii\base\Exception
	 */
	public function actionChangePassword()
	{
		$requestParams = \Yii::$app->request->getBodyParams();

		if (empty($requestParams)) {
			$requestParams = \Yii::$app->request->getQueryParams();
		}

		/**
		 * @var User $user
		 */

		$user = \Yii::$app->user->identity;
		if (!array_key_exists('password', $requestParams) || !array_key_exists('password_confirm', $requestParams)) {
			throw new BadRequestHttpException("Send required fields");
		}

		if ($requestParams['password'] !== $requestParams['password_confirm']) {
			\Yii::$app->response->setStatusCode(422);
			return false;


		}

		$user->setPassword($requestParams['password']);

		if (!$user->save()) {
			throw new ServerErrorHttpException("Unknown Error: Data does not updated");
		}

		return $user;
	}

	/**
	 * @return SigninForm|null|array
	 * @throws \yii\base\Exception
	 */
	public function actionSignin()
	{

		$model = new SigninForm();

		$serviceName = \Yii::$app->getRequest()->getQueryParam('service');
		if (isset($serviceName)) {
			$client = new Client();
			$identity = null;
			$access_token = \Yii::$app->getRequest()->post('access_token');

			if ($access_token) {

				$oauth = [
					"facebook" => [
						"profile_info" => "https://graph.facebook.com/me",
						"fields" => "id,email,name,picture",
					],
					"google" => [
						"profile_info" => "https://www.googleapis.com/plus/v1/people/me",
						"fields" => "id,emails,name,image",
					],

				];

				$response = $client->get($oauth[$serviceName]["profile_info"] . "?access_token={$access_token}");

				if ($response->getStatusCode() != 200) {
					return array(
						"status" => "error",
						"message" => $response->getBody()["error"]["message"],
						"code" => -10190,
					);
				}

				$responseData = array();

				$data = json_decode($response->getBody());

				switch ($serviceName) {
					case "facebook":
						$responseData = array(
							"id" => $data->id,
							"name" => $data->name,
							"email" => $data->email,
							"avatar" => $data->picture->data->url . "&type=large&redirect=false",
							"social" => "facebook"
						);
						break;
					case "google":
						$responseData = array(
							"id" => $data->id,
							"name" => $data->name->givenName . " " . $data->name->familyName,
							"email" => $data->emails[0]->value,
							"avatar" => $data->image->url . "&sz=192",
							"social" => "google"
						);
						break;
					default:
						return array(
							"status" => "error",
							"message" => "Soon",
							"code" => -7777777,
						);
						break;
				}

				$identity = User::findByEAuthAccessTokenResponse($responseData);
				\Yii::$app->user->login($identity, 3600 * 24 * 30);
				return ['user' => \Yii::$app->user->identity, 'token' => \Yii::$app->user->identity->token->token];
			}
		}

		if ($model->load(\Yii::$app->request->post(), '')) {
			if ($model->signin())
				return ['user' => \Yii::$app->user->identity, 'token' => \Yii::$app->user->identity->token->token];
		}

		\Yii::$app->response->statusCode = 422;
		return $model->getErrors();

	}

	public function actionSignup()
	{

		$model = new SignupForm();

		if ($model->load(\Yii::$app->request->post(), '')) {
			return $model->signup();
		}
		\Yii::$app->response->statusCode = 422;
		return $model->getErrors();

	}

	/**
	 * Logout action.
	 *
	 * @return string
	 */
	public function actionLogout()
	{
		Yii::$app->user->identity->logout();

		return ['status' => 'success', 'message' => 'Logout'];

	}

	/**
	 * @return \yii\web\IdentityInterface
	 * @throws NotFoundHttpException
	 * @throws BadRequestHttpException
	 */
	public function actionConfirm()
	{
		$request_params = \Yii::$app->request->post();

		if (!array_key_exists('phone', $request_params)) {
			throw new BadRequestHttpException("Bad request");
		}

		$phone = User::phone($request_params['phone']);
		$code = $request_params['code'];

		$confirmation = PhoneConfirmation::find()
			->where(['phone' => $phone, 'code' => $code, 'status' => PhoneConfirmation::STATUS_UNCONFIRMED])->one();

		if ($confirmation instanceof PhoneConfirmation) {
			$confirmation->updateAttributes(['status' => PhoneConfirmation::STATUS_CONFIRMED]);

			$user = User::findOne(['phone' => $phone]);
			$user->updateAttributes(['status' => User::STATUS_ACTIVE]);

			\Yii::$app->user->login($user);

			return \Yii::$app->user->identity;

		}

		throw new NotFoundHttpException('User phone or confirmation code mismatch');

	}


	/**
	 * @return array|User
	 * @throws BadRequestHttpException
	 * @throws NotFoundHttpException
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionChangePhone()
	{
		$requestParams = \Yii::$app->request->getBodyParams();

		if (empty($requestParams)) {
			$requestParams = \Yii::$app->request->getQueryParams();
		}

		/**
		 * @var User $user
		 */

		$user = User::authorize();
		if (!array_key_exists('phone', $requestParams)) {
			throw new BadRequestHttpException("Didn't sent required fields");
		}

		$code = rand(1000, 9999);
		$phone = $requestParams['phone'];

		$message = \Yii::t("main", "Confirmation code on tribuna.uz: {code}", ['code' => $code]);

		$confirmation = new PhoneConfirmation();
		$confirmation->phone = $phone;
		$confirmation->status = PhoneConfirmation::STATUS_UNCONFIRMED;
		$confirmation->code = (string)$code;
		$confirmation->save();

		$user->phone = $phone;
		$user->status = User::STATUS_UNCONFIRMED;

		if (!$user->validate()) {
			Yii::$app->response->setStatusCode(422);
			return $user->getErrors();
		} else $user->save();
		// Send code message
		\Yii::$app->playmobile->sendSms($user->phone, $message);

		return $user;
	}

	/**
	 * @return array
	 * @throws BadRequestHttpException
	 * @throws NotFoundHttpException
	 * @throws ServerErrorHttpException
	 * @throws \yii\base\Exception
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionRestorePassword()
	{
		$requestParams = \Yii::$app->request->getBodyParams();

		if (empty($requestParams)) {
			$requestParams = \Yii::$app->request->getQueryParams();
		}

		if (!array_key_exists('phone', $requestParams)) {
			throw new BadRequestHttpException("Don't send required fields");
		}
		$phone = User::phone($requestParams['phone']);

		$user = User::findByPhone($phone);

		if (!$user instanceof User) {
			throw new NotFoundHttpException("User not found");
		}

		$password = \Yii::$app->security->generateRandomString(6);

		$message = \Yii::t("main", "Your new password on tribuna.uz: {password}", ['password' => $password]);

		$user->setPassword($password);

		if (!$user->save()) {
			throw new ServerErrorHttpException("Unknown Error: Data does not updated");
		}


		\Yii::$app->playmobile->sendSms($user->phone, $message);

		return array(
			'status' => true,
			'message' => 'New password successfully sent'
		);
	}

	/**
	 * @api {get} /user[/:id]/questions Запрос вопросов пользователья
	 * @apiName GetUserQuestions
	 * @apiGroup User
	 *
	 * @apiParam {Number} [/:id] ID пользователя
	 *
	 * @apiSuccess {ArrayOfObjects} data array of <a href='#api-Question-GetQuestion'>Question</a> objects
	 *
	 * @param integer $id
	 * @return ActiveDataProvider
	 * @throws NotFoundHttpException
	 * @throws UnauthorizedHttpException
	 */
	public function actionQuestions($id = null)
	{
		if ($id) {
			$query = User::findOne($id)->getQuestions();
		} else {
			$query = User::authorize()->getQuestions();
		}

		return $this->getFilteredData($query, QuestionSearch::class);
	}

	/**
	 * @api {get} /user[/:id]/favorite-posts Запрос вопросов пользователья
	 * @apiName GetUserFavPosts
	 * @apiGroup User
	 *
	 * @apiParam {Number} [:id/] ID пользователя
	 *
	 * @apiSuccess {ArrayOfObjects} data array of <a href='#api-Post-GetPost'>Post</a> objects
	 *
	 * @return ActiveDataProvider
	 */
	public function actionFavoritePosts()
	{
		$query = \Yii::$app->user->identity->getFavoritePosts();

		return new ActiveDataProvider(['query' => $query]);
	}

	public function actionFavoriteQuestions()
	{
		$query = \Yii::$app->user->identity->getFavoriteQuestions();

		return new ActiveDataProvider(['query' => $query]);
	}

	public function actionAddFavorite()
	{
		if (\Yii::$app->user->isGuest)
			throw new UnauthorizedHttpException('Вы не авторизованы');

		$request = \Yii::$app->request->getBodyParams();
		$user = \Yii::$app->user->identity->id;

		if (array_key_exists('question_id', $request)) {
			$fav = new FavoriteQuestion();
			$fav->user_id = $user;
			$fav->question_id = $request['question_id'];
			if ($fav->save()) {
				return $fav;
			} else {
				\Yii::$app->response->setStatusCode(422);
				return $fav->getErrors();
			}
		}

		if (array_key_exists('post_id', $request)) {
			$fav = new FavoritePost();
			$fav->user_id = $user;
			$fav->post_id = $request['post_id'];
			if ($fav->save()) {
				return $fav;
			} else {
				\Yii::$app->response->setStatusCode(422);
				return $fav->getErrors();
			}
		}

		throw new UnprocessableEntityHttpException('Required parameters not sent');
	}

	public function actionDeleteFavorite($entity, $id)
	{
		if (\Yii::$app->user->isGuest)
			throw new UnauthorizedHttpException('Вы не авторизованы');

		$user = \Yii::$app->user->identity->id;

		if (is_numeric($id)) {
			if ($entity == 'question') {
				$model = FavoriteQuestion::find()->where(['question_id' => $id, 'user_id' => $user])->one();
				if ($model) {
					$model->delete();
					return $id;
				} else throw new NotFoundHttpException();
			}

			if ($entity == 'post') {
				$model = FavoritePost::find()->where(['post_id' => $id, 'user_id' => $user])->one();
				if ($model) {
					$model->delete();
					return $id;
				} else throw new NotFoundHttpException();
			}
		} else throw new UnprocessableEntityHttpException('Wrong params');

		throw new NotFoundHttpException();
	}

	public function actionAnswers($id = null)
	{
		if ($id) {
			$query = User::findOne($id)->getAnswers();
		} else {
			$query = User::authorize()->getAnswers();
		}

		return $this->getFilteredData($query, AnswerSearch::class);
	}

	public function actionPenalties($id = null)
	{
		if ($id) {
			$query = User::findOne($id)->getPenalties();
		} else {
			$query = User::authorize()->getPenalties();
		}

		return $this->getFilteredData($query, QuestionSearch::class);
	}

	public function actionReviews($id = null)
	{
		if ($id) {
			$query = User::findOne($id)->getReviews();
		} else {
			$query = User::authorize()->getReviews();
		}

		return $this->getFilteredData($query, ReviewSearch::class);
	}

	public function actionMyReviews()
	{
		$query = \Yii::$app->user->identity->getMyReviews();
		return $this->getFilteredData($query, ReviewSearch::class);
	}

	public function actionBlogs($id = null)
	{
		if ($id) {
			$query = User::findOne($id)->getPosts();
		} else {
			$query = User::authorize()->getPosts();
		}

		return $this->getFilteredData($query, PostSearch::class);
	}

	public function actionTop()
	{
		$query = User::find()
			->select('user.*, COUNT(answer.user_id) AS answer_count')
			->joinWith(['profile', 'answers'])
			->orWhere(['>', 'answer.created_at', time()-86400]);
		if (!$query->count()) {
			$query->orWhere(['>', 'answer.created_at', time()-2592000]);
		}
		$query->groupBy('answer.user_id')->orderBy(['answer_count' => SORT_DESC]);
//		return $query->createCommand()->rawSql;
		$data = $this->getFilteredData($query, UserSearch::class);
		$data->pagination->pageSize = 10;
		return $data;
	}

}