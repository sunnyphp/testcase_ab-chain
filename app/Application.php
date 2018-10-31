<?php
declare(strict_types=1);

namespace App;

use App\Configuration\VKClientConf;
use App\Core\Service\Environment;
use App\Core\Service\Session;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use VK\Client\VKApiClient;
use VK\OAuth\VKOAuth;

/**
 * Класс Application
 *
 * @property Environment $env Класс для определения окружения скриптов
 * @property Filesystem $fs Класс для взаимодействия с файловой системой
 * @property VKApiClient $client Клиент для работы с API ВКонтакте
 * @property VKOAuth $client_auth Клиент для авторизации в API ВКонтакте
 * @property Request $request Клиент для работы с HTTP-переменными
 * @property Session $session Класс реализующий запоминание пользователей на сайте (локально)
 *
 * @package App
 * @author Sunny
 */
class Application
{
	/**
	 * Конфигурация API VK
	 * @var array
	 */
	private $conf = [];
	
	/**
	 * Магический метод для получения субклассов
	 * @param string $property
	 * @return object|null
	 * @throws \Exception
	 */
	public function __get(string $property)
	{
		switch ($property) {
			// Класс для определения окружения скриптов
			case 'env':
				return $this->{$property} = new Environment;
			
			// Класс для взаимодействия с файловой системой
			case 'fs':
				return $this->{$property} = new Filesystem;
			
			// Клиент для работы с API ВКонтакте
			case 'client':
				return $this->{$property} = new VKApiClient($this->getConfKey('version'), $this->getConfKey('language'));
			
			// Клиент для авторизации в API ВКонтакте
			case 'client_auth':
				return $this->{$property} = new VKOAuth($this->getConfKey('version'));
			
			// Клиент для работы с HTTP-переменными
			case 'request':
				return $this->{$property} = Request::createFromGlobals();
			
			// Класс реализующий запоминание пользователей на сайте
			case 'session':
				return $this->{$property} = new Session($this);
		}
		
		return null;
	}
	
	/**
	 * Возвращает данные конфигурации API VK по ключу
	 * @param string $key
	 * @return int|string|array|mixed|null
	 * @throws \Exception
	 */
	public function getConfKey(string $key)
	{
		if ($this->conf === []) {
			$this->conf = (new VKClientConf($this->env))->getArray();
		}
		
		return ($this->conf[$key] ?? null);
	}
}
