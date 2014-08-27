<?php
/**
*
* Pages extension for the phpBB Forum Software package.
*
* @copyright (c) 2014 phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace phpbb\pages\tests\event;

class show_page_links_test extends \phpbb_database_test_case
{
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/page.xml');
	}

	protected $controller_helper, $listener, $template, $user;

	/**
	* Data set for test_show_page_links
	*
	* @return array Array of test data
	* @access public
	*/
	public function show_page_links_data()
	{
		return array(
			array(
				array(
					// Links for page 1
					'overall_header_navigation_prepend_links' => array(
						'U_LINK_URL' => 'app.php/page/page_1',
						'LINK_TITLE' => 'title_1',
						'ICON_LINK' => '',
					),
					'S_OVERALL_HEADER_NAVIGATION_PREPEND' => true,
					// Links for page 2
					'overall_header_navigation_append_links' => array(
						'U_LINK_URL' => 'app.php/page/page_2',
						'LINK_TITLE' => 'title_2',
						'ICON_LINK' => '',
					),
					'S_OVERALL_HEADER_NAVIGATION_APPEND' => true,
				),
			),
		);
	}

	/**
	* Test the show_page_links event
	*
	* @dataProvider show_page_links_data
	* @access public
	*/
	public function test_show_page_links($expected)
	{
		global $phpbb_dispatcher, $phpbb_root_path, $phpEx;

		$this->db = $this->new_dbal();

		// Mock some global classes that may be called during code execution
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher();

		// Load/Mock classes required by the event listener class
		$this->template = new \phpbb\pages\tests\mock\template();
		$this->user = new \phpbb\user('\phpbb\datetime');
		$this->ext_manager = new \phpbb_mock_extension_manager($phpbb_root_path);
		$this->controller_helper = new \phpbb_mock_controller_helper(
			$this->template,
			$this->user,
			new \phpbb\config\config(array()),
			new \phpbb\controller\provider(),
			$this->ext_manager,
			'',
			$phpEx,
			dirname(__FILE__) . '/../../'
		);

		$phpbb_container = $this->getMock('Symfony\Component\DependencyInjection\Container');
		$this->listener = new \phpbb\pages\event\listener(
			$this->controller_helper,
			new \phpbb\pages\operators\page($phpbb_container, $this->db, $this->ext_manager, 'phpbb_pages', 'phpbb_pages_links', 'phpbb_pages_pages_links'),
			$this->template,
			$this->user,
			$phpbb_root_path,
			$phpEx
		);

		$dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
		$dispatcher->addListener('core.page_header', array($this->listener, 'show_page_links'));
		$dispatcher->dispatch('core.page_header');

		$this->assertEquals($expected, $this->template->get_template_vars());
	}
}
