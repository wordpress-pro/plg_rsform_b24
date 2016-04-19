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

require_once 'rest.php';

/**
 * RSForm! Bitrix24 system plugin
 */
class plgSystemRSForm_B24 extends JPlugin
{

	public function plgSystemRSForm_B24(&$subject, $config)
	{
		parent::__construct( $subject, $config );

		JLog::addLogger(
			array(
				// Sets file name
				'text_file' => 'plg_rsform_b24.errors.php'
			),
			// Sets messages of all log levels to be sent to the file
			JLog::ALL,
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

	/**
	 * Trigger Event - onBeforeStoreSubmissions
	 *
	 * @param $formId
	 * @param $post
	 * @param $SubmissionId
	 */
	public function rsfp_f_onBeforeStoreSubmissions($formId, &$post, $SubmissionId)
	{
		$form = $post;
		$params = $this->getParams();
		rsform_b24_send_lead("$formId/$SubmissionId", $form, $params);
	}

	public function onAfterRender()
	{
		$params = $this->getParams();
		rsform_b24_send_lead('test - '.time(), array('name' => 'Test'), $params);
	}
}