<?php
declare(strict_types=1);

namespace App\Core\Service;

/**
 * Класс Environment определяет окружение в котором работает скрипт
 * @package App\Core\Service
 * @author Sunny
 */
class Environment
{
	/**
	 * Оружение: продакшен
	 * @var string
	 */
	public const PRODUCTION = 'prod';
	
	/**
	 * Оружение: постановка
	 * @var string
	 */
	public const STAGING = 'stage';
	
	/**
	 * Оружение: тестирование
	 * @var string
	 */
	public const TESTING = 'test';
	
	/**
	 * Оружение: разработка
	 * @var string
	 */
	public const DEVELOPMENT = 'dev';
	
	/**
	 * Типы окружений
	 * @var string[]
	 */
	private $types = [
		self::PRODUCTION,
		self::STAGING,
		self::TESTING,
		self::DEVELOPMENT,
	];
	
	/**
	 * Текущее окружение
	 * @var string|null
	 */
	private $current;
	
	/**
	 * Абсолютный путь до корня сайта в ФС (в нем папки app/vendor/tests)
	 * @var string|null
	 */
	private $rootPath;
	
	/**
	 *
	 * Возвращает путь от корня сайта в ФС до указанного пути (директория или файл)
	 * @param string $path
	 * @return string
	 */
	public function getPath(string $path = null): string
	{
		$ds = DIRECTORY_SEPARATOR;
		$dsConv = [
			'\\'	=> '/',
			'/'		=> '\\',
		];
		
		// определяем путь до корня сайта в ФС
		if ($this->rootPath === null) {
			$this->rootPath = realpath(dirname(dirname(dirname(__DIR__)))).DIRECTORY_SEPARATOR;
		}
		
		// если есть путь добавляемый к
		if ($path !== null) {
			// проверяем наличие открывающих-закрывающих разделителей
			if (in_array(substr($path, 0, 1), $dsConv)) {
				$path = substr($path, 1);
			}
			
			// конвертация типов разделителей директорий
			$path = str_replace($dsConv[$ds], $ds, $path);
			
			// крепим путь к руту
			return $this->rootPath.$path;
		} else {
			// возвращаем путь до корня
			return $this->rootPath;
		}
	}
	
	/**
	 * Принудительно устанавливает окружение
	 * @param string $env
	 * @return $this
	 */
	public function setCurrent($env): Environment
	{
		if (in_array($env, $this->types)) {
			$this->current = $env;
		}
		
		return $this;
	}
	
	/**
	 * Возвращает текущее окружение
	 * @return string
	 */
	public function getCurrent(): string
	{
		if (!$this->current) {
			// логика определяющая окружение
			if (
				(getenv('COMPUTERNAME') === 'GAMESTATION') ||
				(isset($_SERVER['HTTP_HOST']) && in_array($_SERVER['HTTP_HOST'], ['127.0.0.1', 'localhost']))
			) {
				$this->current = self::DEVELOPMENT;
			} else {
				$this->current = self::PRODUCTION;
			}
		}
		
		return $this->current;
	}
	
	/**
	 * Возвращает типы окружений
	 * @return string[]
	 */
	public function getTypes(): array
	{
		return $this->types;
	}
}
