<?php
/**
 * @file        rest.php
 * @description
 *
 * @version		0.0.1
 * PHP Version  5.3
 *
 * @package     plg_rsform_b24
 *
 * @copyright   2015, Vadim Pshentsov. All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPLv3
 * @author      Vadim Pshentsov <pshentsoff@gmail.com>
 * @link        http://pshentsoff.ru Author's homepage
 * @link        http://blog.pshentsoff.ru Author's blog
 *
 * @created     12.04.16
 *
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once 'defines.php';

function _rsform_b24_debug_log($message)
{
	if(class_exists('JLog')) {
		JLog::add($message, JLog::DEBUG, 'plg_rsform_b24');
	}
}

/**
 * Отправка данных лида прямым запросом
 *
 * @param $lead_title - № заказа
 * @param $form - данные по оплате, доставке и способам
 * @param $params - параметры
 * @return bool|string
 */
function rsform_b24_send_lead($lead_title, array $form, array $params = null)
{
	$result = false;

	// Если функция используется в одном из костылей и нет возможности получить параметры плагина с настройками для соединения и авторизации
	if(!isset($params)) {

		if(!defined('WCB24_CRM_HOST') || !defined('WCB24_CRM_LOGIN') || !defined('WCB24_CRM_PASSWORD')) {
			_rsform_b24_debug_log('CRM host and authorization settings required.');
			return false;
		}

		$params = array(
			'crm_host' => WCB24_CRM_HOST,
			'crm_login' => WCB24_CRM_LOGIN,
			'crm_password' => WCB24_CRM_PASSWORD,
		);
	}

	// get lead data from the form
	$postData = array(
		'TITLE'             => $lead_title,
		'NAME'              => isset($form['name']) ? $form['name'] : '',
		'SOURCE_ID'         => 'WEB', // источник

		'PHONE_MOBILE' => (isset($form['phone']) ? $form['phone'] : ''),

		'EMAIL_WORK' => (isset($form['email']) ? $form['email'] : ''),

		'COMMENTS' => (isset($form['message']) ? $form['message'] : ''),
	);

	// append authorization data
	if (defined('CRM_AUTH')) {
		$postData['AUTH'] = CRM_AUTH;
	} else {
		$postData['LOGIN'] = $params['crm_login'];
		$postData['PASSWORD'] = $params['crm_password'];
	}

	// open socket to CRM
	$fp = fsockopen("ssl://" . $params['crm_host'], RSFORM_B24_CRM_PORT, $errno, $errstr, 30);
	if ($fp) {
		// prepare POST data
		$strPostData = '';
		foreach ($postData as $key => $value) {
			$strPostData .= ($strPostData == '' ? '' : '&') . $key . '=' . urlencode($value);
		}

		// prepare POST headers
		$str = "POST " . RSFORM_B24_CRM_LEAD_PATH . " HTTP/1.0\r\n";
		$str .= "Host: " . $params['crm_host'] . "\r\n";
		$str .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$str .= "Content-Length: " . strlen($strPostData) . "\r\n";
		$str .= "Connection: close\r\n\r\n";

		$str .= $strPostData;

		// send POST to CRM
		fwrite($fp, $str);

		// get CRM headers
		$result = '';
		while (!feof($fp)) {
			$result .= fgets($fp, 128);
		}
		fclose($fp);

		// проверка отправки
		$response = explode("\r\n\r\n", $result);

		_rsform_b24_debug_log('wcb24_send_lead: Response is '.print_r($response, true));

		$resp = preg_replace("/'/", '"', $response[1]);
		$resp = json_decode($resp, true);
		$jle = json_last_error();

		// Ошибка декодирования json ответа
		if($jle !== 0) {
			_rsform_b24_debug_log("wcb24_send_lead: Error response decoding[$jle]: ".print_r($resp, true));
			return false;
		}

		// Статус ответа не 201
		if($resp['error'] != 201) {
			_rsform_b24_debug_log('wcb24_send_lead: Error response status: '.print_r($resp, true));
			return false;
		}

		$result = $resp['ID'];

	} else {
		_rsform_b24_debug_log('wcb24_send_lead: Connection Failed! ' . $errstr . ' (' . $errno . ')');
	}

	return $result;
}

/**
 * Форма заказа для отправки комментарием к лиду
 * Только для сайта http://www.stimul-tb.ru
 * @param $amount
 * @return string
 */
function _rsform_b24_order_tpl($amount)
{
	$out = <<<END
НИЖНЯЯ ТРАССА НА ВЕСЬ ДЕНЬ: Будни - {$amount[0]['weekdays']}/Выходные - {$amount[0]['weekend']}
 | ВЕСЬ ПАРК НА ВЕСЬ ДЕНЬ: Будни - {$amount[1]['weekdays']}/Выходные - {$amount[1]['weekend']}
 | СЕМЕЙНЫЙ 1+1 НА ВЕСЬ ДЕНЬ: Будни - {$amount[2]['weekdays']}/Выходные - {$amount[2]['weekend']}
 | СЕМЕЙНЫЙ 2+1 НА ВЕСЬ ДЕНЬ: Будни - {$amount[3]['weekdays']}/Выходные - {$amount[3]['weekend']}
 | СЕМЕЙНЫЙ 2+2 НА ВЕСЬ ДЕНЬ: Будни - {$amount[4]['weekdays']}/Выходные - {$amount[4]['weekend']}
 | ВНЕСТИ ПРЕДОПЛАТУ: Будни - {$amount[5]['weekdays']}/Выходные - {$amount[5]['weekend']}
END;

	return $out;
}