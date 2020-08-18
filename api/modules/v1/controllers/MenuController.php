<?php
/**
 * @author Izzat <i.rakhmatov@list.ru>
 * @package advanced
 */

namespace api\modules\v1\controllers;
/**
 * @api {get} /menu/
 * @apiName GetMenu
 * @apiGroup Menu
 *
 * @apiSuccess {String} title Menu unique Названия
 * @apiSuccess {Array} childs Пункт меню и под меню
 * @apiSuccess {String} url Адрес.
 * @apiSuccess {Number} sort Сортировка
 * @apiSuccess {String} icon иконка или картинка
 * @apiSuccess {Array} childs под меню
 */


use api\components\ApiController;
use common\modules\langs\components\Lang;
use common\modules\menu\models\Menu;

class MenuController extends ApiController {

    public $modelClass = 'common\\modules\\menu\\models\\Menu';
    public $searchModelClass = 'common\\modules\\menu\\models\\MenuSearch';

    public function actions()
    {
        return array(
            'options' => array(
                'class' => 'yii\rest\OptionsAction',
                'resourceOptions' => array('GET', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS', 'POST'),
                'collectionOptions' => array('GET', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS', 'POST'),
            ),
        );
    }

    public function actionIndex()
    {
        if (in_array(\Yii::$app->language, array('oz', 'uz'))) {
            return Menu::find()->where(['alias' => 'header-menu', 'lang' => Lang::getLangId('uz')])->one();
        }
        return Menu::find()->where(['alias' => 'header-menu'])->lang()->one();

    }
}