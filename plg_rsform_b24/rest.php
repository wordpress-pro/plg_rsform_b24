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

jimport('joomla.log.log');

/**
 * Отправка данных лида прямым запросом
 *
 * @param $lead_title - № заказа
 * @param $form - данные по оплате, доставке и способам
 * @param $params - параметры
 * @return bool|string
 */
function rsform_b24_send_lead($lead_title, array $form, array $params)
{
	$result = false;

	// get lead data from the form
	$postData = array(
		'TITLE'             => $lead_title,
		'NAME'              => $form['name'],
		'SOURCE_ID'         => 'WEB', // источник

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

		JLog::add('wcb24_send_lead: Response is '.print_r($response, true), JLog::DEBUG, 'plg_rsform_b24');

		$resp = preg_replace("/'/", '"', $response[1]);
		$resp = json_decode($resp, true);
		$jle = json_last_error();

		// Ошибка декодирования json ответа
		if($jle !== 0) {
			JLog::add("wcb24_send_lead: Error response decoding[$jle]: ".print_r($resp, true), JLog::DEBUG, 'plg_rsform_b24');
			return false;
		}

		// Статус ответа не 201
		if($resp['error'] != 201) {
			JLog::add('wcb24_send_lead: Error response status: '.print_r($resp, true), JLog::DEBUG, 'plg_rsform_b24');
			return false;
		}

		$result = $resp['ID'];

	} else {
		JLog::add('wcb24_send_lead: Connection Failed! ' . $errstr . ' (' . $errno . ')', JLog::DEBUG, 'plg_rsform_b24');
	}

	return $result;
}

