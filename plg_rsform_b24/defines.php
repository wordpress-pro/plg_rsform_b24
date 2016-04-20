<?php
/**
 * @file        defines.php
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

// CRM server connection data
defined('RSFORM_B24_CRM_PORT') or define('RSFORM_B24_CRM_PORT', '443'); // CRM server port
defined('RSFORM_B24_CRM_LEAD_PATH') or define('RSFORM_B24_CRM_LEAD_PATH', '/crm/configs/import/lead.php'); // CRM server REST service path

/**
 * протокол, по которому работаем. должен быть https
 */
define('RSFORM_B24_PROTOCOL', "https");

/**
 * Определения для костыльных решений, чтобы не запускать приложения
 */
// CRM server conection data
defined('WCB24_CRM_HOST') or define('WCB24_CRM_HOST', 'kinohouse-work.bitrix24.ru'); // your CRM domain name
// CRM server authorization data
defined('WCB24_CRM_LOGIN') or define('WCB24_CRM_LOGIN', 'vladimr.surkov@ya.ru'); // login of a CRM user able to manage leads
defined('WCB24_CRM_PASSWORD') or define('WCB24_CRM_PASSWORD', 'wK1R9t'); // password of a CRM user
