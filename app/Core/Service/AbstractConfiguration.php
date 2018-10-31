<?php
//declare(strict_types=1);

namespace App\Core\Service;

use Exception;

/**
 * Абстрактный класс AbstractConfiguration реализует функционал по получению конфигурации в зависимости от окружения
 * @link https://en.wikipedia.org/wiki/Deployment_environment
 * @package App\Core\Service
 * @author Sunny
 */
abstract class AbstractConfiguration
{
	/**
	 * Массив настроек (окружение = PRODUCTION)
	 * @var array
	 */
	protected $prodConf = [];
	
	/**
	 * Массив настроек (окружение = STAGING)
	 * @var array
	 */
	protected $stageConf = [];
	
	/**
	 * Массив настроек (окружение = TESTING)
	 * @var array
	 */
	protected $testConf = [];
	
	/**
	 * Массив настроек (окружение = DEVELOPMENT)
	 * @var array
	 */
	protected $devConf = [];
	
	/**
	 * Класс окружения
	 * @var Environment
	 */
	private $env;
	
	/**
	 * Конструктор класса
	 * @param Environment $env
	 * @throws Exception
	 */
	public function __construct(Environment $env)
	{
		// установка класса окружения
		$this->env = $env;
		
		// проверка одинаковости ключей
		$tmpKeys = null;
		foreach ($this->env->getTypes() as $type) {
			// получаем ключи
			$keys = (property_exists($this, $type.'Conf') ? $this->{$type.'Conf'} : []);
			if ($keys === []) {
				// пропускаем незаполненные массивы
				continue;
			}
			
			// обработка ключей
			$keys = array_keys($keys);
			sort($keys, SORT_STRING);
			$keys = implode('/', $keys);
			
			if ($tmpKeys === null) {
				// устанавливаем во временный массив при первом проходе
				$tmpKeys = $keys;
			} elseif ($tmpKeys !== $keys) {
				// проверяем при следующих проходах
				throw new Exception("Configuration keys in '{$type}Conf' mismatched, must be equal to all other keys");
			}
		}
	}
	
	/**
	 * Возвращает массив с настройками. Алгоритм простой - берутся настройки на прямую, если они заполнены,
	 * если нет - перебираются и берутся самые верхние (от `prod` к `dev` окружению).
	 * @return array
	 */
	public function getArray()
	{
		$env = $this->env->getCurrent();
		
		if (property_exists($this, $env.'Conf') && $this->{$env.'Conf'} !== []) {
			// есть массив и он не пуст
			return $this->{$env.'Conf'};
		} else {
			// перебираем типы в поисках данных
			$tmpConf = [];
			foreach ($this->env->getTypes() as $type) {
				// пишем данные во временный массив, при их наличии
				if (property_exists($this, $type.'Conf') && $this->{$type.'Conf'} !== []) {
					$tmpConf = $this->{$type.'Conf'};
				}
				
				// нашли нужный тип и он не пустой
				if ($type === $env && $tmpConf !== []) {
					return $tmpConf;
				}
			}
			
			return $tmpConf;
		}
	}
}
