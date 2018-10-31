<?php
declare(strict_types=1);

namespace App\Engine;

/**
 * Класс LocalUser
 * @package App\Engine
 * @author Sunny
 */
class LocalUser
{
	/**
	 * Идентификатор локального пользователя
	 * @var string
	 */
	private $id;
	
	/**
	 * Флаг измененности данных пользователя
	 * @var bool
	 */
	private $changed = false;
	
	/**
	 * Данные клиента
	 * @var array
	 */
	private $data = [];
	
	/**
	 * Конструктор класса
	 * @param string $id
	 * @param array $data
	 */
	public function __construct(string $id, array $data)
	{
		$this->setId($id);
		$this->setData($data);
	}
	
	/**
	 * Возвращает идентификатор пользователя или NULL
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}
	
	/**
	 * Устанавливает идентификатор пользователя или NULL
	 * @param string $id
	 * @return LocalUser
	 */
	public function setId(string $id): LocalUser
	{
		$this->id = $id;
		
		return $this;
	}
	
	/**
	 * Возвращает флаг измененности данных пользователя
	 * @return bool
	 */
	public function isChanged(): bool
	{
		return $this->changed;
	}
	
	/**
	 * Устанавливает флаг измененности данных пользователя
	 * @param bool $changed
	 * @return LocalUser
	 */
	public function setChanged(bool $changed): LocalUser
	{
		$this->changed = $changed;
		
		return $this;
	}
	
	/**
	 * Возвращает массив данных
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}
	
	/**
	 * Устанавливает массив данных
	 * @param array $data
	 * @return LocalUser
	 */
	public function setData(array $data): LocalUser
	{
		$this->data = $data;
		
		return $this;
	}
	
	/**
	 * Возвращает True если есть Access Token для доступа к VK API
	 * @return bool
	 */
	public function isAccessToken(): bool
	{
		return isset($this->data['access_token']['access_token']);
	}
	
	/**
	 * Возвращает True если Access Token требует замены
	 * @return bool
	 */
	public function isAccessExpired(): bool
	{
		return (time() >= ($this->data['access_expires'] ?? 0));
	}
	
	/**
	 * Устанавливает данные Access Token
	 * @param array $token
	 * @return LocalUser
	 */
	public function setAccessToken(array $token): LocalUser
	{
		$this->data['access_token'] = $token;
		$this->data['access_expires'] = time() + (int)$token['expires_in'];
		
		return $this->setChanged(true);
	}
	
	/**
	 * Возвращает данные Access Token по ключу или NULL
	 * @param string $key
	 * @return string|int|null
	 */
	public function getAccessTokenData(string $key = 'access_token')
	{
		return ($this->data['access_token'][$key] ?? null);
	}
	
	/**
	 * Возвращает True если данные существуют в кеше по ключу
	 * @param string $key
	 * @return bool
	 */
	public function isCacheData(string $key): bool
	{
		return (isset($this->data['cached']) && array_key_exists($key, $this->data['cached']));
	}
	
	/**
	 * Возвращает данные из кеша по ключу
	 * @param string $key
	 * @param callable $generator Коллбек метод вызывает для генерации данных (при их отсутствии)
	 * @return mixed|null
	 */
	public function getCacheData(string $key, callable $generator = null)
	{
		if (($this->data['cached'][$key] ?? null) === null && $generator !== null) {
			$this->setCacheData($key, $generator($this));
		}
		
		return ($this->data['cached'][$key] ?? null);
	}
	
	/**
	 * Устанавливает данные в кеш по ключу
	 * @param string $key
	 * @param $data
	 * @return $this
	 */
	public function setCacheData(string $key, $data)
	{
		$this->data['cached'][$key] = $data;
		
		return $this->setChanged(true);
	}
	
	/**
	 * Полная очистка данных пользователя
	 * @return LocalUser
	 */
	public function flush(): LocalUser
	{
		$this->data = [];
		
		return $this->setChanged(true);
	}
}
