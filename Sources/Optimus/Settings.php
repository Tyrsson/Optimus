<?php

declare(strict_types=1);

namespace Bugo\Optimus;

/**
 * Settings.php
 *
 * @package Optimus
 * @link https://custom.simplemachines.org/mods/index.php?mod=2659
 * @author Bugo https://dragomano.ru/mods/optimus
 * @copyright 2010-2024 Bugo
 * @license https://opensource.org/licenses/artistic-license-2.0 Artistic-2.0
 *
 * @version 2.13
 */

if (! defined('SMF'))
	die('No direct access...');

final class Settings
{
	public function hooks()
	{
		add_integration_function('integrate_modify_basic_settings', __CLASS__ . '::modifyBasicSettings', false, __FILE__, true);
		add_integration_function('integrate_admin_areas', __CLASS__ . '::adminAreas', false, __FILE__, true);
		add_integration_function('integrate_admin_search', __CLASS__ . '::adminSearch', false, __FILE__, true);
	}

	/**
	 * Remove meta_keywords setting (it moved to Optimus settings) and disable queryless_urls settings (if "Remove index.php" enabled)
	 *
	 * Удаляем настройку meta_keywords из её стандартного места и помещаем на страницу настроек Optimus
	 */
	public function modifyBasicSettings(array &$config_vars)
	{
		foreach ($config_vars as $key => $dump) {
			if (isset($dump[1]) && $dump[1] == 'meta_keywords') {
				unset($config_vars[$key]);
			}
		}
	}

	public function adminAreas(array &$admin_areas)
	{
		global $settings, $txt;

		addInlineCss('
		.main_icons.optimus::before {
			background:url(' . $settings['default_images_url'] . '/optimus.png) no-repeat 0 0 !important;
		}
		.large_admin_menu_icon.optimus::before {
			background:url(' . $settings['default_images_url'] . '/optimus_large.png) no-repeat 0 0;
		}
		.fa-optimus::before {
			content: "\f717";
		}');

		if (op_request('area') === 'optimus')
			loadCSSFile('optimus/optimus.css');

		$admin_areas['config']['areas']['optimus'] = array(
			'label' => $txt['optimus_title'],
			'function' => array($this, 'actions'),
			'icon' => 'optimus',
			'subsections' => array(
				'basic'    => array($txt['optimus_basic_title']),
				'extra'    => array($txt['optimus_extra_title']),
				'favicon'  => array($txt['optimus_favicon_title']),
				'metatags' => array($txt['optimus_meta_title']),
				'redirect' => array($txt['optimus_redirect_title']),
				'counters' => array($txt['optimus_counters']),
				'robots'   => array($txt['optimus_robots_title']),
				'htaccess' => array($txt['optimus_htaccess_title']),
				'sitemap'  => array($txt['optimus_sitemap_title'])
			)
		);
	}

	public function adminSearch(array &$language_files, array &$include_files, array &$settings_search)
	{
		$settings_search[] = array(array($this, 'basicTabSettings'), 'area=optimus;sa=basic');
		$settings_search[] = array(array($this, 'extraTabSettings'), 'area=optimus;sa=extra');
		$settings_search[] = array(array($this, 'faviconTabSettings'), 'area=optimus;sa=favicon');
		$settings_search[] = array(array($this, 'sitemapTabSettings'), 'area=optimus;sa=sitemap');
	}

	public function actions()
	{
		global $context, $txt, $sourcedir, $smcFunc;

		$context['page_title'] = OP_NAME;

		loadTemplate('Optimus');
		loadLanguage('ManageSettings');

		$subActions = array(
			'basic'    => 'basicTabSettings',
			'extra'    => 'extraTabSettings',
			'favicon'  => 'faviconTabSettings',
			'metatags' => 'metatagsTabSettings',
			'redirect' => 'redirectTabSettings',
			'counters' => 'counterTabSettings',
			'robots'   => 'robotsTabSettings',
			'htaccess' => 'htaccessTabSettings',
			'sitemap'  => 'sitemapTabSettings'
		);

		require_once($sourcedir . '/ManageSettings.php');

		loadGeneralSettingParameters($subActions, 'basic');

		db_extend();

		$context[$context['admin_menu_name']]['tab_data'] = array(
			'title' => $txt['optimus_title'],
			'tabs' => array(
				'basic' => array(
					'description' => sprintf($txt['optimus_basic_desc'], OP_VERSION, phpversion(), $smcFunc['db_title'], $smcFunc['db_get_version']())
				),
				'extra' => array(
					'description' => $txt['optimus_extra_desc']
				),
				'favicon' => array(
					'description' => $txt['optimus_favicon_desc']
				),
				'metatags' => array(
					'description' => $txt['optimus_meta_desc']
				),
				'redirect' => array(
					'description' => $txt['optimus_redirect_desc']
				),
				'counters' => array(
					'description' => $txt['optimus_counters_desc']
				),
				'robots' => array(
					'description' => $txt['optimus_robots_desc']
				),
				'htaccess' => array(
					'description' => $txt['optimus_htaccess_desc']
				),
				'sitemap' => array(
					'description' => sprintf($txt['optimus_sitemap_desc'], OP_NAME)
				)
			)
		);

		$this->{$subActions[op_request('sa')]}();
	}

	/**
	 * @return void|array
	 */
	public function basicTabSettings(bool $return_config = false)
	{
		global $context, $txt, $scripturl, $modSettings;

		$context['page_title'] .= ' - ' . $txt['optimus_basic_title'];
		$context['post_url'] = $scripturl . '?action=admin;area=optimus;sa=basic;save';

		$this->addDefaultSettings(
			['optimus_forum_index' => sprintf($txt['forum_index'], $context['forum_name'])]
		);

		$config_vars = array(
			array('title', 'optimus_main_page'),
			array('text', 'optimus_forum_index', 80, 'value' => un_htmlspecialchars($modSettings['optimus_forum_index'] ?? '')),
			array('large_text', 'optimus_description', 'value' => un_htmlspecialchars($modSettings['optimus_description'] ?? ''), 'subtext' => $txt['optimus_description_subtext']),
			array('large_text', 'meta_keywords', 'subtext' => $txt['meta_keywords_note']),
			array('title', 'optimus_all_pages'),
			array('select', 'optimus_board_extend_title', $txt['optimus_board_extend_title_set']),
			array('select', 'optimus_topic_extend_title', $txt['optimus_topic_extend_title_set']),
			'',
			array('check', 'optimus_topic_description'),
			array('check', 'optimus_allow_change_topic_desc', 'subtext' => $txt['optimus_allow_change_topic_desc_subtext']),
			'',
			array('check', 'optimus_allow_change_topic_keywords', 'subtext' => $txt['optimus_allow_change_topic_keywords_subtext']),
			array('check', 'optimus_show_keywords_block'),
			array('check', 'optimus_show_keywords_on_message_index'),
			array('check', 'optimus_allow_keyword_phrases'),
			array('check', 'optimus_use_color_tags'),
			'',
			array('check', 'optimus_correct_http_status'),
			array('title', 'optimus_extra_settings'),
			array('check', 'optimus_log_search'),
		);

		// Mod authors can add own options
		call_integration_hook('integrate_optimus_basic_settings', array(&$config_vars));

		if ($return_config)
			return $config_vars;

		if (op_is_get('save')) {
			checkSession();

			if (op_is_post('optimus_forum_index'))
				$_POST['optimus_forum_index'] = op_filter('optimus_forum_index');

			if (op_is_post('optimus_description'))
				$_POST['optimus_description'] = op_filter('optimus_description');

			call_integration_hook('integrate_save_optimus_basic_settings');

			$save_vars = $config_vars;
			saveDBSettings($save_vars);

			redirectexit('action=admin;area=optimus;sa=basic');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * @return void|array
	 */
	public function extraTabSettings(bool $return_config = false)
	{
		global $context, $txt, $scripturl, $settings;

		$context['page_title'] .= ' - ' . $txt['optimus_extra_title'];
		$context['post_url'] = $scripturl . '?action=admin;area=optimus;sa=extra;save';

		$txt['optimus_extra_info'] = sprintf($txt['optimus_extra_info'], $scripturl);
		$og_image_option_link = $scripturl . '?action=admin;area=theme;sa=list;th=' . $settings['theme_id']  . '#options_og_image';

		$config_vars = array(
			array('title', 'optimus_extra_title'),
			array('desc', 'optimus_extra_info'),
			array('check', 'optimus_og_image', 'help' => 'optimus_og_image_help', 'subtext' => sprintf($txt['optimus_og_image_subtext'], $og_image_option_link)),
			array('check', 'optimus_allow_change_board_og_image', 'subtext' => $txt['optimus_allow_change_board_og_image_subtext']),
			array('text', 'optimus_fb_appid', 40, 'help' => 'optimus_fb_appid_help'),
			array('text', 'optimus_tw_cards', 40, 'preinput' => '@', 'help' => 'optimus_tw_cards_help')
		);

		// Mod authors can add own options
		call_integration_hook('integrate_optimus_extra_settings', array(&$config_vars));

		if ($return_config)
			return $config_vars;

		if (op_is_get('save')) {
			checkSession();

			if (op_is_post('optimus_fb_appid'))
				$_POST['optimus_fb_appid'] = op_filter('optimus_fb_appid');

			if (op_is_post('optimus_tw_cards'))
				$_POST['optimus_tw_cards'] = str_replace('@', '', op_filter('optimus_tw_cards'));

			call_integration_hook('integrate_save_optimus_extra_settings');

			$save_vars = $config_vars;
			saveDBSettings($save_vars);

			redirectexit('action=admin;area=optimus;sa=extra');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * @return void|array
	 */
	public function faviconTabSettings(bool $return_config = false)
	{
		global $context, $txt, $scripturl;

		$context['page_title'] .= ' - ' . $txt['optimus_favicon_title'];
		$context['post_url'] = $scripturl . '?action=admin;area=optimus;sa=favicon;save';

		$config_vars = array(
			array('large_text', 'optimus_favicon_text')
		);

		if ($return_config)
			return $config_vars;

		$context['sub_template'] = 'favicon';

		if (op_is_get('save')) {
			checkSession();

			$save_vars = $config_vars;
			saveDBSettings($save_vars);

			redirectexit('action=admin;area=optimus;sa=favicon');
		}

		prepareDBSettingContext($config_vars);
	}

	public function metatagsTabSettings()
	{
		global $context, $txt, $scripturl, $modSettings;

		$context['sub_template'] = 'metatags';
		$context['page_title'] .= ' - ' . $txt['optimus_meta_title'];
		$context['post_url'] = $scripturl . '?action=admin;area=optimus;sa=metatags;save';

		$context['optimus_metatags_rules'] = empty($modSettings['optimus_meta']) ? [] : unserialize($modSettings['optimus_meta']);

		$config_vars = [];

		if (op_is_get('save')) {
			checkSession();

			$save_vars = $config_vars;
			saveDBSettings($save_vars);

			$meta = [];
			if (op_is_post('custom_tag_name') && op_is_post('custom_tag_value')) {
				$custom_tag = filter_input_array(INPUT_POST, FILTER_DEFAULT);
				$custom_tag['custom_tag_name'] = array_filter($custom_tag['custom_tag_name']);

				foreach ($custom_tag['custom_tag_name'] as $key => $value) {
					$meta[$value] = $custom_tag['custom_tag_value'][$key];
				}
			}

			updateSettings(['optimus_meta' => serialize($meta)]);
			redirectexit('action=admin;area=optimus;sa=metatags');
		}

		prepareDBSettingContext($config_vars);
	}

	public function redirectTabSettings()
	{
		global $context, $txt, $scripturl, $modSettings;

		$context['sub_template'] = 'redirect';
		$context['page_title'] .= ' - ' . $txt['optimus_redirect_title'];
		$context['post_url'] = $scripturl . '?action=admin;area=optimus;sa=redirect;save';

		$context['optimus_redirect_rules'] = empty($modSettings['optimus_redirect']) ? [] : unserialize($modSettings['optimus_redirect']);

		$config_vars = [];

		if (op_is_get('save')) {
			checkSession();

			$save_vars = $config_vars;
			saveDBSettings($save_vars);

			$redirect = [];
			if (op_is_post('custom_redirect_from') && op_is_post('custom_redirect_to')) {
				$custom_redirect = filter_input_array(INPUT_POST, FILTER_DEFAULT);
				$custom_redirect['custom_redirect_from'] = array_filter($custom_redirect['custom_redirect_from']);

				foreach ($custom_redirect['custom_redirect_from'] as $to => $from) {
					$redirect[$from] = $custom_redirect['custom_redirect_to'][$to];
				}
			}

			updateSettings(['optimus_redirect' => serialize($redirect)]);
			redirectexit('action=admin;area=optimus;sa=redirect');
		}

		prepareDBSettingContext($config_vars);
	}

	public function counterTabSettings()
	{
		global $context, $txt, $scripturl;

		$context['sub_template'] = 'counters';
		$context['page_title'] .= ' - ' . $txt['optimus_counters'];
		$context['post_url'] = $scripturl . '?action=admin;area=optimus;sa=counters;save';

		$this->addDefaultSettings([
			'optimus_counters_css'    => '.counters {text-align: center}',
			'optimus_ignored_actions' => 'admin,bookmarks,credits,helpadmin,pm,printpage'
		]);

		$config_vars = array(
			array('large_text', 'optimus_head_code'),
			array('large_text', 'optimus_stat_code'),
			array('large_text', 'optimus_count_code'),
			array('large_text', 'optimus_counters_css'),
			array('text', 'optimus_ignored_actions')
		);

		if (op_is_get('save')) {
			checkSession();

			$save_vars = $config_vars;
			saveDBSettings($save_vars);

			redirectexit('action=admin;area=optimus;sa=counters');
		}

		prepareDBSettingContext($config_vars);
	}

	public function robotsTabSettings()
	{
		global $context, $txt, $scripturl, $boarddir;

		$context['sub_template'] = 'robots';
		$context['page_title'] .= ' - ' . $txt['optimus_robots_title'];
		$context['post_url'] = $scripturl . '?action=admin;area=optimus;sa=robots;save';

		$config_vars = [];

		$robots_path = (op_server('document_root') ?: $boarddir) . '/robots.txt';
		$context['robots_content'] = is_writable($robots_path) ? file_get_contents($robots_path) : '';

		(new Robots())->generate();

		if (op_is_get('save')) {
			checkSession();

			$save_vars = $config_vars;
			saveDBSettings($save_vars);

			file_put_contents($robots_path, op_filter('optimus_robots'), LOCK_EX);

			redirectexit('action=admin;area=optimus;sa=robots');
		}

		prepareDBSettingContext($config_vars);
	}

	public function htaccessTabSettings()
	{
		global $context, $txt, $scripturl, $boarddir;

		$context['sub_template'] = 'htaccess';
		$context['page_title'] .= ' - ' . $txt['optimus_htaccess_title'];
		$context['post_url'] = $scripturl . '?action=admin;area=optimus;sa=htaccess;save';

		$config_vars = [];

		$htaccess_path = (op_server('document_root') ?: $boarddir) . '/.htaccess';
		$context['htaccess_content'] = is_writable($htaccess_path) ? file_get_contents($htaccess_path) : '';

		if (op_is_get('save')) {
			checkSession();

			$save_vars = $config_vars;
			saveDBSettings($save_vars);

			if (is_file($htaccess_path)) {
				copy($htaccess_path, $htaccess_path . '.backup');
			}

			file_put_contents($htaccess_path, trim($_POST['optimus_htaccess']), LOCK_EX);

			redirectexit('action=admin;area=optimus;sa=htaccess');
		}

		prepareDBSettingContext($config_vars);
	}

	/**
	 * @return void|array
	 */
	public function sitemapTabSettings(bool $return_config = false)
	{
		global $context, $txt, $scripturl, $smcFunc;

		$context['page_title'] .= ' - ' . $txt['optimus_sitemap_title'];
		$context['settings_title'] = $txt['optimus_sitemap_title'];
		$context['post_url'] = $scripturl . '?action=admin;area=optimus;sa=sitemap;save';

		$this->addDefaultSettings([
			'optimus_sitemap_topics_num_replies' => 5,
			'optimus_sitemap_items_display'      => 10000,
			'optimus_start_year'                 => 1994,
			'optimus_update_frequency'           => 1
		]);

		$config_vars = array(
			array('check', 'optimus_sitemap_enable', 'subtext' => $txt['optimus_sitemap_enable_subtext']),
			array('check', 'optimus_sitemap_link'),
			array('check', 'optimus_remove_previous_xml_files'),
			array('select', 'optimus_main_page_frequency', $txt['optimus_main_page_frequency_set']),
			array('check', 'optimus_sitemap_boards', 'subtext' => $txt['optimus_sitemap_boards_subtext']),
			array('check', 'optimus_sitemap_all_topic_pages', 'subtext' => $txt['optimus_sitemap_all_topic_pages_subtext']),
			array('int', 'optimus_sitemap_topics_num_replies', 'min' => 0),
			array('int', 'optimus_sitemap_items_display', 'min' => 1, 'max' => 50000),
			array('int', 'optimus_start_year', 'min' => 1994, 'max' => date('Y')),
			array('select', 'optimus_update_frequency', $txt['optimus_update_frequency_set'])
		);

		// Mod authors can add own options
		call_integration_hook('integrate_optimus_sitemap_settings', array(&$config_vars));

		if ($return_config)
			return $config_vars;

		if (op_is_get('save')) {
			checkSession();

			// Recreate a sitemap after save settings
			$smcFunc['db_query']('', '
				DELETE FROM {db_prefix}background_tasks
				WHERE task_class = {string:task_class}',
				array(
					'task_class' => '\Bugo\Optimus\Task'
				)
			);

			if (op_is_post('optimus_sitemap_enable')) {
				$smcFunc['db_insert']('insert',
					'{db_prefix}background_tasks',
					array('task_file' => 'string-255', 'task_class' => 'string-255', 'task_data' => 'string'),
					array('$sourcedir/Optimus/Task.php', '\Bugo\Optimus\Task', ''),
					array('id_task')
				);
			}

			call_integration_hook('integrate_save_optimus_sitemap_settings');

			$save_vars = $config_vars;
			saveDBSettings($save_vars);

			redirectexit('action=admin;area=optimus;sa=sitemap');
		}

		prepareDBSettingContext($config_vars);
	}

	private function addDefaultSettings($settings)
	{
		global $modSettings;

		if (empty($settings))
			return;

		$vars = [];
		foreach ($settings as $key => $value) {
			if (! isset($modSettings[$key]))
				$vars[$key] = $value;
		}

		updateSettings($vars);
	}
}
