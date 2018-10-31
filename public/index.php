<?php
declare(strict_types=1);

/**
 * 1) Страничка авторизации для получения ключа доступа
 * @author Sunny
 */

require_once '../vendor/autoload.php';

use App\Core\Service\Session;
use App\Engine\Templates;
use VK\OAuth\Scopes\VKOAuthUserScope;
use VK\OAuth\VKOAuthDisplay;
use VK\OAuth\VKOAuthResponseType;

$app = new App\Application;

// получение локальных данных пользователя
if (($user = $app->session->getLocalUser()) === null) {
	// создание нового локального пользователя
	$clientId = $app->session->createUserId();
	
	// сохранение в печеньки
	setcookie(Session::SESSION_KEY, $clientId, time() + 315360000);
	
	// обновляем страницу
	header('Location: index.php');
	exit;
}

// корректный выход из аккаунта
if ($app->request->query->has('logout')) {
	// подчищаем ключи у пользователя и сохраняем его
	if ($user->flush()->isChanged()) {
		$app->session->saveLocalUser($user);
	}
	
	// переходим на главную (авторизацию)
	header('Location: index.php');
	exit;
}

// передан code-параметр который нужно превратить в access_token
if (($code = $app->request->query->get('code')) !== null) {
	// получаем запросом server <-> server
	$accessToken = $app->client_auth->getAccessToken(
		$app->getConfKey('client_id'),
		$app->getConfKey('secret_key'),
		'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'],
		$code
	);
	
	// сохраняем у пользователя Access Token и сохраняем локальные данные
	if ($user->setAccessToken($accessToken)->isChanged()) {
		$app->session->saveLocalUser($user);
	}
	
	// редирект для удаления query-части
	header('Location: index.php');
	exit;
}

// проверяем авторизацию в API VK
if ($user->isAccessToken()) {
	// ключ доступа имеется, переходим на страницу с рекламными кабинетами
	header('Location: cabinets.php');
} elseif ($app->request->query->has('auth')) {
	// авторизуемся
	$redirectTo = $app->client_auth->getAuthorizeUrl(
		VKOAuthResponseType::CODE,
		$app->getConfKey('client_id'),
		'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'],
		VKOAuthDisplay::PAGE,
		[VKOAuthUserScope::ADS, ]
	);
	
	header('Location: '.$redirectTo);
} else {
	// делаем кнопку, что бы не было циклического редиректа cabinets.php <-> index.php
	Templates::getIndexButton();
}
