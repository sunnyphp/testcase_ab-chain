<?php
declare(strict_types=1);

namespace App\Engine;

use App\Application;

/**
 * Класс Wrapper реализует обертку для взаимодействия между нашими скриптами и API VK
 * @package App\Engine
 * @author Sunny
 */
class Wrapper
{
	/**
	 * Класс приложения
	 * @var Application
	 */
	private $app;
	
	/**
	 * Класс локального пользователя
	 * @var LocalUser
	 */
	private $user;
	
	/**
	 * Конструктор класса
	 * @param Application $app Класс приложения
	 * @param LocalUser $user
	 */
	public function __construct(Application $app, LocalUser $user)
	{
		$this->app = $app;
		$this->user = $user;
	}
	
	/**
	 * Возвращает информацию по текущему пользователю который авторизован в приложении VK
	 * @link https://vk.com/dev/users.get
	 * @return array
	 */
	public function getUserProfile(): array
	{
		// получаем данные
		$data = $this->user->getCacheData('profile', function(LocalUser $user){
			// запрашиваем профиль пользователя
			$result = $this->app->client->users()->get($user->getAccessTokenData(), [
				'user_ids'	=> $this->user->getAccessTokenData('user_id'),
				'fields'	=> 'has_photo,photo_50,screen_name',
			]);
			
			return current($result);
		});
		
		// сохраняем закешированное
		if ($this->user->isChanged()) {
			$this->app->session->saveLocalUser($this->user);
		}
		
		return $data;
	}
	
	/**
	 * Возвращает список рекламных кабинетов
	 * @link https://vk.com/dev/ads.getAccounts
	 * @return array
	 */
	public function getAdsCabinets(): array
	{
		// получаем данные
		$data = $this->user->getCacheData('ads_accounts', function(LocalUser $user){
			// запрашиваем список рекламных кабинетов
			return $this->app->client->ads()->getAccounts($user->getAccessTokenData());
		});
		
		// сохраняем закешированное
		if ($this->user->isChanged()) {
			$this->app->session->saveLocalUser($this->user);
		}
		
		return $data;
	}
	
	/**
	 * Возвращает сформированный массив для вывода рекламных кампаний в кабинете
	 * @link https://vk.com/dev/ads.getCampaigns
	 * @param int $cabinetId
	 * @return array
	 */
	public function getAdsCampaigns(int $cabinetId): array
	{
		// получаем кампании по идентификатору кабинета
		$data['campaigns'] = $this->user->getCacheData('campaigns_'.$cabinetId, function(LocalUser $user) use($cabinetId){
			return $this->app->client->ads()->getCampaigns($user->getAccessTokenData(), [
				'account_id'		=> $cabinetId,
				'include_deleted'	=> 1,
			]);
		});
		
		// сохраняем закешированное
		if ($this->user->isChanged()) {
			$this->app->session->saveLocalUser($this->user);
		}
		
		// генерация хлебных крошек
		$cabinets = $this->getAdsCabinets();
		$filtered = array_filter($cabinets, function($item) use($cabinetId){
			return ($item['account_id'] === $cabinetId);
		}, ARRAY_FILTER_USE_BOTH);
		
		// хлебные крошки
		$data['breadcrumbs1'] = ($filtered ? current($filtered) : []);
		
		return $data;
	}
	
	/**
	 * Возвращает список рекламных объявлений по указанным идентификаторам кабинета и кампании
	 * @link https://vk.com/dev/ads.getAds
	 * @param int $cabinetId
	 * @param int $campaignId
	 * @return array
	 */
	public function getAdsListing(int $cabinetId, int $campaignId): array
	{
		// получаем объявления по идентификатору кабинета
		$data['listing'] = $this->user->getCacheData('ads_'.$cabinetId.'_'.$campaignId, function(LocalUser $user) use($cabinetId, $campaignId){
			// @todo запилить пагинацию, т.к. возвращает максимум 2к объявлений и 2к кабинетов
			return $this->app->client->ads()->getAds($user->getAccessTokenData(), [
				'account_id'		=> $cabinetId,
				'include_deleted'	=> 1,
				'campaign_ids'		=> json_encode([$campaignId]),
			]);
		});
		
		// сохраняем закешированное
		if ($this->user->isChanged()) {
			$this->app->session->saveLocalUser($this->user);
		}
		
		// генерация хлебных крошек
		$cabinets = $this->getAdsCabinets();
		$filtered1 = array_filter($cabinets, function($item) use($cabinetId){
			return ($item['account_id'] === $cabinetId);
		}, ARRAY_FILTER_USE_BOTH);
		$campaigns = $this->getAdsCampaigns($cabinetId);
		$filtered2 = array_filter($campaigns['campaigns'], function($item) use($campaignId){
			return ($item['id'] === $campaignId);
		}, ARRAY_FILTER_USE_BOTH);
		
		// хлебные крошки
		$data['breadcrumbs1'] = ($filtered1 ? current($filtered1) : []);
		$data['breadcrumbs2'] = ($filtered2 ? current($filtered2) : []);
		
		return $data;
	}
}
