<?php
declare(strict_types=1);

namespace App\Core\Service;

use App\Application;
use App\Engine\LocalUser;
use Exception;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Класс Session реализует запоминание пользоателя на сайте (локально)
 * @package App\Core\Service
 * @author Sunny
 */
class Session
{
	/**
	 * Класс приложения
	 * @var Application
	 */
	private $app;
	
	/**
	 * Ключ печеньки в которой хранится сессия
	 * @var string
	 */
	public const SESSION_KEY = 'cid';
	
	/**
	 * Конструктор класса
	 * @param Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}
	
	/**
	 * Возвращает идентификатор пользователя который должен быть записан в печеньки
	 * @return string
	 * @throws Exception
	 */
	public function createUserId(): string
	{
		$dir = $this->app->env->getPath('storage/');
		
		// note: такая дикость в рамках тестового задания приемлема
		do {
			$userId = md5((string)random_int(1, 999888777));
			$fileName = $dir.$userId.'.php';
		} while (file_exists($fileName));
		
		return $userId;
	}
	
	/**
	 * Возвращает идентификатор локального пользователя из печенек или NULL
	 * @return string|null
	 */
	public function getUserId():? string
	{
		if ($this->app->request->cookies->has(self::SESSION_KEY)) {
			$userId = $this->app->request->cookies->getAlnum(self::SESSION_KEY);
			if (preg_match('~^[a-f\d]{32}$~is', $userId)) {
				return $userId;
			}
		}
		
		return null;
	}
	
	/**
	 * Возвращает данные локального пользователя или NULL
	 * @return LocalUser|null
	 */
	public function getLocalUser():? LocalUser
	{
		if ($userId = $this->getUserId()) {
			$fileName = $this->app->env->getPath('storage/'.$userId.'.php');
			if (file_exists($fileName)) {
				return new LocalUser($userId, include($fileName));
			}
		}
		
		return null;
	}
	
	/**
	 * Возвращает True если данные локального пользователя записаны в файл успешно
	 * @param LocalUser $user
	 * @return bool
	 * @throws IOException
	 */
	public function saveLocalUser(LocalUser $user): bool
	{
		$fileName = $this->app->env->getPath('storage/'.$user->getId().'.php');
		$this->app->fs->dumpFile($fileName, "<?php\n\nreturn ".var_export($user->getData(), true).";\n\n// Gen: ".date('r')."\n");
		$user->setChanged(false);
		
		return true;
	}
}
