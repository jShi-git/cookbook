<?php
//header("Content-Type:text/html; charset=utf-8");
require_once ('workflows.php');

class App {
	private static $_instance;
	private $_workflows;

	function __construct() {
		$this->_workflows = new workflows();
	}
	public function getInstance() {
		if (!self::$_instance instanceof self) {
			self::$_instance = new App();
		}
		return self::$_instance;
	}

	public function request($url) {
		return $this->_workflows->request($url);
	}

	public function getMagnetLinks($keyword) {
		$html   = file_get_contents("http://www.dacailou.com/t?q=" . $keyword);
		$result = array();

		if ($html != "") {
			preg_match_all('/<ul  class="list-inline list-unstyled">(.*?)<\/ul>/', str_replace("\n", "", $html), $dataList);

			if (isset($dataList[1]) && !empty($dataList[1][0])) {
				preg_match_all("/<li.*>(.*)<\/li>/iUs", $dataList[1][0], $items);

				if (!empty($items[1])) {
					foreach ($items[1] as $key => $item) {

						preg_match('/<h5>(.*)<\/h5>/iUs', $item, $info);

						preg_match('/<a href="(.*)" target="_blank" title="(.*)">.*<\/a>/iUs', $info[1], $link);
						$result[$key]['link']  = "http://www.dacailou.com/" . $link[1];
						$result[$key]['title'] = $link[2];

						preg_match('/<img src="(.*)" alt="(.*)".*\/>/iUs', $info[1], $title);
						$result[$key]['img']  = "http://www.dacailou.com/" . $title[1];
						$result[$key]['from'] = $title[2];

					}
				}
			}

		}
		return $result;
	}

	public function getData($keyword) {
		$results = $this->getMagnetLinks($keyword);

		foreach ($results as $item) {
			$this->_workflows->result(time(), $item['link'], $item['title'], $item['from'], 'icon.png');
		}
		return $this->_workflows->toxml();
	}
	public function run($query) {
		return $this->getData($query);
	}
}
//echo App::getInstance()->run("后会无期");
?>