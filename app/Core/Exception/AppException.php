<?php
declare(strict_types=1);

namespace App\Core\Exception;

use Exception;

/**
 * Класс AppException реализует базовое исключение приложения
 * @package App\Core\Exception
 * @author Sunny
 */
class AppException extends Exception
{
	/**
	 * Список дополнительной информации
	 * @var array
	 */
	private $payload = [];
	
	/**
	 * Возвращает данные списка дополнительной информации
	 * @return array
	 */
	public function getPayload(): array
	{
		return $this->payload;
	}
	
	/**
	 * Устанавливает массив данных в список дополнительной информации
	 * @param array $payload
	 * @return AppException
	 */
	public function setPayload(array $payload): AppException
	{
		$this->payload = $payload;
		
		return $this;
	}
	
	/**
	 * Мержит массив данных со списком дополнительной информации
	 * @param array $payload
	 * @return AppException
	 */
	public function mergePayload(array $payload): AppException
	{
		$this->payload = array_merge($this->payload, $payload);
		
		return $this;
	}
	
	/**
	 * Добавляет данные по ключу в список дополнительной информации
	 * @param mixed $key
	 * @param mixed $value
	 * @return AppException
	 */
	public function addPayload($key, $value): AppException
	{
		$this->payload[$key] = $value;
		
		return $this;
	}
	
	/**
	 * Удаляет данные по ключу из списка дополнительной информации
	 * @param mixed $key
	 * @return AppException
	 */
	public function removePayload($key): AppException
	{
		unset($this->payload[$key]);
		
		return $this;
	}
}
