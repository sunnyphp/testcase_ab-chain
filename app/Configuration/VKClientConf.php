<?php
declare(strict_types=1);

namespace App\Configuration;

use App\Core\Service\AbstractConfiguration;
use VK\Client\Enums\VKLanguage;

/**
 * Класс VKClientConf хранит настройки для подключения API VK
 * @package App\Configuration
 * @author Sunny
 */
class VKClientConf extends AbstractConfiguration
{
	/**
	 * @inheritDoc
	 */
	protected $devConf = [
		'client_id'		=> 6736777,
		'secret_key'	=> '',
		'version'		=> '5.87',
		'language'		=> VKLanguage::RUSSIAN,
	];
}
