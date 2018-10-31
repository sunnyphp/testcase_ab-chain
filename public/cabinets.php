<?php
declare(strict_types=1);

/**
 * 2) Страничка со списком рекламных кабинетов		При отсутствии cid в query string
 * 3) Страничка кабинета							При наличии cid/campid в query string
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
		
		if (($cid = $app->request->query->getInt('cid')) > 0) {
			// отображение конкретного кабинета
			$campaigns = $wrapper->getAdsCampaigns($cid);
			
			// отображение заголовка
			Templates::getCampsBreadcrumbs($campaigns);
			
			if ($campaigns && $campaigns['campaigns']) {
				// выводим список кампаний
				Templates::getCampsHeader();
				foreach ($campaigns['campaigns'] as $camp) {
					Templates::getCampsBody($cid, $camp);
				}
				Templates::getCampsFooter();
			} elseif (!$campaigns) {
				// нет такого кабинета
				Templates::getMessage('Кабинет не найден');
			} else {
				// нет кампаний
				Templates::getMessage('Нет рекламных кампаний в данном кабинете');
			}
		} else {
			// получаем список кабинетов
			$cabinets = $wrapper->getAdsCabinets();
			if ($cabinets) {
				// выводим список кабинетов
				Templates::getCabinetsHeader();
				foreach ($cabinets as $acc) {
					Templates::getCabinetsBody($acc);
				}
				Templates::getCabinetsFooter();
			} else {
				// нет кабинетов
				Templates::getMessage('Нет рекламных кабинетов');
			}
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
