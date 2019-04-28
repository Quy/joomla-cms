<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Redirect\Administrator\View\Links;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Redirect\Administrator\Helper\RedirectHelper;

/**
 * View class for a list of redirection links.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * True if "System - Redirect Plugin" is enabled
	 *
	 * @var  boolean
	 */
	protected $enabled;

	/**
	 * True if "Collect URLs" is enabled
	 *
	 * @var  boolean
	 */
	protected $collect_urls_enabled;

	/**
	 * The id of the redirect plugin in mysql
	 *
	 * @var    integer
	 * @since  3.8.0
	 */
	protected $redirectPluginId = 0;

	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    \Joomla\CMS\Pagination\Pagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

	/**
	 * The model state
	 *
	 * @var  \Joomla\Registry\Registry
	 */
	protected $params;

	/**
	 * Form object for search filters
	 *
	 * @var    \JForm
	 * @since  4.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public $activeFilters;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  False if unsuccessful, otherwise void.
	 *
	 * @since   1.6
	 * @throws  \JViewGenericdataexception
	 */
	public function display($tpl = null)
	{
		// Set variables
		$this->items                = $this->get('Items');
		$this->pagination           = $this->get('Pagination');
		$this->state                = $this->get('State');
		$this->filterForm           = $this->get('FilterForm');
		$this->activeFilters        = $this->get('ActiveFilters');
		$this->params               = ComponentHelper::getParams('com_redirect');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		if (!(PluginHelper::isEnabled('system', 'redirect') && RedirectHelper::collectUrlsEnabled()))
		{
			$this->redirectPluginId = RedirectHelper::getRedirectPluginId();
		}

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');
		$canDo = ContentHelper::getActions('com_redirect');

		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_REDIRECT_MANAGER_LINKS'), 'refresh redirect');

		if ($canDo->get('core.create'))
		{
			$toolbar->addNew('link.add');
		}

		if ($canDo->get('core.edit.state') || $canDo->get('core.admin'))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fa fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if ($state->get('filter.state') != 2)
			{
				$childBar->publish('links.publish', 'JTOOLBAR_ENABLE')->listCheck(true);
				$childBar->unpublish('links.unpublish', 'JTOOLBAR_DISABLE')->listCheck(true);
			}

			if ($state->get('filter.state') != -1)
			{
				if ($state->get('filter.state') != 2)
				{
					$childBar->archive('links.archive')->listCheck(true);
				}
				elseif ($state->get('filter.state') == 2)
				{
					$childBar->unarchive('links.unarchive')->listCheck(true);
				}
			}

			if (!$state->get('filter.state') == -2)
			{
				$childBar->trash('links.trash')->listCheck(true);
			}
		}

		if ($canDo->get('core.delete'))
		{
			$toolbar->delete('links.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if (!$state->get('filter.state') == -2 && $canDo->get('core.delete'))
		{
			$toolbar->confirmButton('delete')
				->text('COM_REDIRECT_TOOLBAR_PURGE')
				->message('COM_REDIRECT_CONFIRM_PURGE')
				->task('links.purge');
		}

		if ($canDo->get('core.create'))
		{
			$toolbar->popupButton('batch')
				->text('JTOOLBAR_BULK_IMPORT')
				->selector('collapseModal')
				->listCheck(false);
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			$toolbar->preferences('com_redirect');
		}

		$toolbar->help('JHELP_COMPONENTS_REDIRECT_MANAGER');
	}
}
