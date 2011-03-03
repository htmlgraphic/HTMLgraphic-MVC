<?php

abstract class PageController extends Controller
{

	protected $settings, $page_view = 'Page';

	function __construct()
	{
		parent::__construct();

		if (method_exists($this, "useSSL") && $this->useSSL($this)) {
			$obj = URL::parseCurrentURL();

			if ($obj->scheme <> "https") {
				$link = URL::getCurrentSSL();
				Debugger::consoleLog("redirect:" . $link);
				$this->redirect($link);
			}
		}
		else if (method_exists($this, "useSSL") && !$this->useSSL($this)) {
			$obj = URL::parseCurrentURL();

			if ($obj->scheme <> "http") {
				$link = URL::getCurrentWithoutSSL();
				Debugger::consoleLog("redirect:" . $link);
				$this->redirect($link);
			}
		}
	}

	/*
	 * This will overwrite all previous data under the same $name
	 */
	protected function setPageData($name, $val)
	{
		$store = & $this->get_store($name);
		$store = $val;
	}

	/*
	 * Similar to setPageDate except it merges new data with existing data
	 */
	protected function addPageData($name, $val)
	{
		$store = & $this->get_store($name);
		if (is_array($store)) {
			if (is_array($val))
				$store = array_merge($store, $val);
			else
				$store[] = $val;
		}
		else
			$store = $val;
	}

	/*
	 * Replace items from page data array
	 */
	protected function replacePageData($name, $old_val, $new_val)
	{
		$store = & $this->get_store($name);
		if (is_array($store)) {
			$index = array_search($old_val, $store);

			if ($index !== false)
				$store[$index] = $new_val;
		}
	}

	private function &get_store($name)
	{
		$indexes = explode('/', $name);
		$store = & $this->settings;
		foreach ($indexes as $index)
		{
			if (empty($index))
				$store = & $store[];
			else
				$store = & $store[$index];
		}

		return $store;
	}

	protected function setPageView($view)
	{
		$this->page_view = $view;
	}

	protected function setBodyView($view)
	{
		$this->settings['body_view'] = $view;
	}

	protected function loadPage()
	{
		$this->loadView($this->page_view, $this->settings);
	}

}
?>