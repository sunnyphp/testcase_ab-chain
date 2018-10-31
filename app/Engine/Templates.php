<?php

namespace App\Engine;

/**
 * Класс Templates хранит в себе шаблоны
 *
 * @note MVC нет времени реализовывать полноценно
 *
 * @package App\Engine
 * @author Sunny
 */
class Templates
{
	/**
	 * @return void
	 */
	public static function getIndexButton(): void
	{
		echo '<style>
		.btn{
			display:inline-block;
			padding:20px;
			font-size:120%;
			border:2px solid transparent;
			box-shadow:0 0 5px #000;
			background-color:rgba(0,0,255,.1)
		}
		</style>';
		
		echo '<a class="btn" href="index.php?auth=1">Авторизоваться ВКонтакте</a>';
	}
	
	/**
	 * @param array $user
	 * @return void
	 */
	public static function getUserProfile(array $user): void
	{
		printf(
			'<div style="line-height:25px;">
				<img alt="%s" src="%s" align="left" height="50" width="50"> <b>%s</b><br>
				<a href="index.php?logout=1">Выход</a>
			</div><hr>',
			$user['screen_name'], ($user['has_photo'] ? $user['photo_50'] : 'about:blank'),
			$user['first_name'] . ' ' . $user['last_name']
		);
	}
	
	/**
	 * @return void
	 */
	public static function getCabinetsHeader(): void
	{
		echo '<table cellpadding="5" border="1">
				<thead>
					<th>Идентификатор</th><th>Тип</th><th>Название кабинета</th><th>Уровень доступа</th><th>Активность</th>
				</thead>
			<tbody>';
	}
	
	/**
	 * @param array $acc
	 * @return void
	 */
	public static function getCabinetsBody(array $acc): void
	{
		printf(
			'<tr>
				<td><a href="cabinets.php?cid=%u">%u</a></td>
				<td>%s</td><td>%s</td><td>%s</td><td>%s</td>
			</tr>',
			$acc['account_id'], $acc['account_id'], $acc['account_type'], $acc['account_name'],
			$acc['access_role'], ($acc['account_status'] ? 'Включен' : 'Выключен')
		);
	}
	
	/**
	 * @return void
	 */
	public static function getCabinetsFooter(): void
	{
		echo '</tbody></table>';
	}
	
	/**
	 * @param array $data
	 */
	public static function getCampsBreadcrumbs(array $data): void
	{
		printf(
			'<div>Название кабинета: <b>%s</b> | <a href="cabinets.php">К списку кабинетов</a></div><hr>',
			($data['breadcrumbs1']['account_name'] ?? 'Кабинет не найден')
		);
	}
	
	/**
	 * @return void
	 */
	public static function getCampsHeader(): void
	{
		echo '<table cellpadding="5" border="1">
				<thead>
					<th>Идентификатор</th><th>Тип</th><th>Название кампании</th><th>Статус</th><th>Дневной лимит</th>
					<th>Общий лимит</th><th>Запуск</th><th>Остановка</th>
				</thead>
			<tbody>';
	}
	
	/**
	 * @param int $cabinetId
	 * @param array $camp
	 * @return void
	 */
	public static function getCampsBody(int $cabinetId, array $camp): void
	{
		static $statuses = [
			0	=> 'Остановлена',
			1	=> 'Запущена',
			2	=> 'Удалена',
		];
		
		printf(
			'<tr>
				<td><a href="campaign.php?cid=%u&campid=%u">%u</a></td>
				<td>%s</td><td>%s</td><td>%s</td><td>%s руб</td><td>%s руб</td><td>%s</td><td>%s</td>
			</tr>',
			$cabinetId, $camp['id'], $camp['id'], $camp['type'], $camp['name'], $statuses[$camp['status']],
			number_format($camp['day_limit'], 2, '.', ' '), number_format($camp['all_limit'], 2, '.', ' '),
			($camp['start_time'] ? date('r', $camp['start_time']) : '&mdash;'),
			($camp['stop_time'] ? date('r', $camp['stop_time']) : '&mdash;')
		);
	}
	
	/**
	 * @return void
	 */
	public static function getCampsFooter(): void
	{
		echo '</tbody></table>';
	}
	
	/**
	 * @param int $cabinetId
	 * @param array $data
	 */
	public static function getAdsBreadcrumbs(int $cabinetId, array $data): void
	{
		printf(
			'<div>Название кабинета: <b>%s</b> | Название кампании: <b>%s</b> | <a href="cabinets.php?cid=%u">К списку кампаний</a></div><hr>',
			($data['breadcrumbs1']['account_name'] ?? 'Кабинет не найден'),
			($data['breadcrumbs2']['name'] ?? 'Кампания не найдена'),
			$cabinetId
		);
	}
	
	/**
	 * @return void
	 */
	public static function getAdsHeader(): void
	{
		echo '<table cellpadding="5" border="1"><tbody>';
	}
	
	/**
	 * @param int $cabinetId
	 * @param array $item
	 * @return void
	 */
	public static function getAdsBody(int $cabinetId, array $item): void
	{
		static $fields = [
			'id'					=> 'Идентификатор объявления',
			'campaign_id'			=> 'Идентификатор кампании',
			'ad_format'				=> 'Формат объявления',
			'cost_type'				=> 'Тип оплаты',
			'cpc'					=> 'Цена за переход, коп.',
			'cpm'					=> 'Цена за 1000 показов, коп.',
			'all_limit'				=> 'Идентификатор',
			'category1_id'			=> 'Идентификатор тематики',
			'category2_id'			=> 'Идентификатор тематики (доп)',
			'status'				=> 'Статус объявления',
			'name '					=> 'Название объявления',
			'approved'				=> 'Статус модерации',
		];
		
		static $valueEnums = [
			'ad_format' => [
				1	=> 'Изображение и текст',
				2	=> 'Большое изображение',
				3	=> 'Эксклюзивный формат',
				4	=> 'Продвижение сообществ или приложений, квадратное изображение',
				5	=> 'Приложение в новостной ленте',
				6	=> 'Мобильное приложение',
				9	=> 'Запись в сообществе',
			],
			'status' => [
				0	=> 'Остановлено',
				1	=> 'Запущено',
				2	=> 'Удалено',
			],
			'approved' => [
				0	=> 'Не проходило модерацию',
				1	=> 'Ожидает модерации',
				2	=> 'Одобрено',
				3	=> 'Отклонено',
			],
		];
		
		foreach ($item as $k => $v) {
			if (array_key_exists($k, $fields)) {
				printf('<tr><td align="right">%s</td><td>%s</td></tr>', $fields[$k], ($valueEnums[$k][$v] ?? $v));
			}
		}
	}
	
	/**
	 * @return void
	 */
	public static function getAdsFooter(): void
	{
		echo '</tbody></table>';
	}
	
	/**
	 * @param string $message
	 * @return void
	 */
	public static function getMessage(string $message): void
	{
		printf('<div style="padding:5px;border:1px solid #000;background:rgba(0,255,0,.1)">%s</div>', $message);
	}
}
