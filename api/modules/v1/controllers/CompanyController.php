<?php

namespace api\modules\v1\controllers;

use api\components\ApiController;
use common\models\Company;
use common\models\CompanySearch;

/**
 * @api {get} /company
 * @apiName GetCompanyList
 * @apiGroup Company
 *
 * @apiSuccess {String} name Названия компания
 * @apiSuccess {Number} worker_count Количество профил компания
 */

/**
 * @api {get} /company/:id?include=owner,profile
 * @apiName GetCompany
 * @apiGroup Company
 *
 * @apiParam {Number} id ID Компания
 * @apiParam {String} [owner] Создател Компания
 * @apiParam {String} [profile] Список Ползоватиле
 *
 * @apiSuccess {String} name Названия компания
 * @apiSuccess {Number} worker_count Количество профил компания
 * @apiSuccess {Object} owner Создател Компания
 * @apiSuccess {Object} profile Список Ползоватиле
 *
 */
class CompanyController extends ApiController
{
	public $modelClass = Company::class;
	public $searchModelClass = CompanySearch::class;

}