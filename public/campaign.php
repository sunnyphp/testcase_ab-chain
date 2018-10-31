<?php
declare(strict_types=1);

/**
 * 4) Страничка кампании			При наличии cid в query string
 * @author Sunny
 */

require_once '../vendor/autoload.php';

use App\Engine\Templates;
use App\Engine\Wrapper;
use VK\Client\VKApiError;
use VK\Exceptions\Api\VKApiAuthException;

$app = new App\Application;

// получение локальные данные пользователя
if (($user = $app->session->getLocalUser()) === null) {
	// начинаем с главной
	header('Location: index.php');
	exit;
}

// проверяем наличие переменных в query string
if (($cid = $app->request->query->getInt('cid')) < 1) {
	header('Location: cabinets.php');
	exit;
} elseif (($campId = $app->request->query->getInt('campid')) < 1) {
	header('Location: cabinets.php?cid='.$cid);
	exit;
}

// получаем авторизованного в VK пользователя, за одно проверяя валидность ключа
try {
	if ($user->isAccessToken()) {
		// проверяем тайминги
		if ($user->isAccessExpired()) {
			// время вышло
			throw new VKApiAuthException(new VKApiError([
				'error_msg'	=> 'App: require auth',
			]));
		}
		
		// создаем враппер
		$wrapper = new Wrapper($app, $user);
		
		// получаем данные пользователя
		$profile = $wrapper->getUserProfile();
		Templates::getUserProfile($profile);
		
		// отображение конкретного кабинета
		$listing = $wrapper->getAdsListing($cid, $campId);
		
		// отображение заголовка
		Templates::getAdsBreadcrumbs($cid, $listing);
		
		if ($listing && $listing['listing']) {
			// выводим список кампаний
			Templates::getAdsHeader();
			foreach ($listing['listing'] as $item) {
				Templates::getAdsBody(0, $item);
			}
			Templates::getAdsFooter();
		} elseif (!$listing) {
			// нет такого кабинета
			Templates::getMessage(' не найден');
		} else {
			// нет кампаний
			Templates::getMessage('Нет рекламных объявлений в данной кампании');
		}
	} else {
		// необходимо авторизоваться
		throw new VKApiAuthException(new VKApiError([
			'error_msg'	=> 'App: require auth',
		]));
	}
} catch (VKApiAuthException $e) {
	// необходимость в [повторной] авторизации
	header('Location: index.php?logout=1');
	exit;
}
