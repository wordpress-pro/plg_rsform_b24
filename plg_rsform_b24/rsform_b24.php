<?php
/**
 * @file        rsform_b24.php
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

jimport( 'joomla.plugin.plugin' );
jimport('joomla.log.log');

require_once 'rest.php';

/**
 * RSForm! Bitrix24 system plugin
 */
class plgSystemRSForm_B24 extends JPlugin
{
	protected $debug;

	public function __construct(&$subject, $config)
	{
		parent::__construct( $subject, $config );

		$this->debug = $this->params->get('debug');

		if($this->debug) {
			$log = JLog::ALL;
		} else {
			$log = JLog::ALL & ~JLog::DEBUG;
		}

		JLog::addLogger(
			array(
				// Sets file name
				'text_file' => 'plg_rsform_b24.errors.php'
			),
			// Sets messages of all log levels to be sent to the file
			$log,
			// The log category/categories which should be recorded in this file
			// In this case, it's just the one category from our extension, still
			// we need to put it inside an array
			array('plg_rsform_b24')
		);

	}

	public function canRun()
	{
		if (class_exists('RSFormProHelper')) return true;

		$helper = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_rsform'.DS.'helpers'.DS.'rsform.php';
		if (file_exists($helper))
		{
			require_once($helper);
			return true;
		}

		return false;
	}

	/**
	 * Get plugin params as array
	 *
	 * @return array
	 */
	protected function getParams()
	{
		return array(
			'crm_host' => $this->params->get('crm_host'),
			'crm_login' => $this->params->get('crm_login'),
			'crm_password' => $this->params->get('crm_password'),
		);
	}

	public function rsfp_f_onBeforeStoreSubmissions(array $args = null)
	{
		JLog::add("rsfp_f_onBeforeStoreSubmissions(): Trigger called: ".print_r($args, true), JLog::DEBUG, 'plg_rsform_b24');

		if(isset($args['formId'])) {

			$params = $this->getParams();

			$SubmissionId = isset($form['SubmissionId']) ? '/'.$form['SubmissionId'] : '';
			$title = "Form - {$args['formId']}$SubmissionId - ".time();

			$form = array();

			if($args['formId'] == 4) {
				$form['email'] = isset($args['post']['emaill']) ? $args['post']['emaill'] : '';
			}

			rsform_b24_send_lead($title, $form, $params);
		}

	}

	public function onAfterRoute()
	{
		if(!empty($_POST)) {
			JLog::add("onAfterRoute(): Trigger called with form post: ".print_r($_POST, true), JLog::DEBUG, 'plg_rsform_b24');

			// Custom JForm
			if(isset($_POST['extsendcallback'])) {

				$post = array();
				// @todo get values by native Joomla! methods and validate
				$post['name'] = $_POST['name'];
				$post['phone'] = $_POST['phone'];
				$post['email'] = $_POST['email'];
				$post['message'] = $_POST['message'];

				rsform_b24_send_lead('Ext Send Callback - '.time(), $post, $this->getParams());

				// Order form
			} elseif(isset($_POST['siteform'])) {

				$post = array();
				// @todo get values by native Joomla! methods and validate
				$post['name'] = $_POST['fullname'];
				$post['phone'] = $_POST['phone'];
				$post['email'] = $_POST['email'];
				// @todo create text table with values
				$post['message'] = _rsform_b24_order_tpl($_POST['amount']);

				rsform_b24_send_lead('Order - '.time(), $post, $this->getParams());

			}
		}
//		$params = $this->getParams();
//		rsform_b24_send_lead('test - '.time(), array('name' => 'Test'), $params);
	}

}