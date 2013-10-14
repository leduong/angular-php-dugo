<?php
class Template {
	var $filename;

	public function __construct($filename="layout") {
		global $CONF;
		if (strpos($filename,TPL)){
			$this->filename = $filename;
		}
		else{
			$file = THEME.config('theme').sprintf('/html/%s.html', $filename);
			if (!file_exists($file)){
				$file = THEME.sprintf('/default/html/%s.html', $filename);
			}
			$this->filename = $file;
		}
	}

	public function mk($filename) {
		$this->filename = $filename;
		return $this->make();
	}

	public function make() {
		if($fopen = fopen($this->filename, 'r')){
			$template = @fread($fopen, filesize($this->filename));
			fclose($fopen);
			return $this->parse($template);
		}
	}

	private function parse($template) {
		global $TMPL, $LNG;

		$template = preg_replace_callback(
			'/{\$lng->(.+?)}/i',
			create_function('$matches', 'global $LNG; return $LNG[$matches[1]];'), $template);
		$template = preg_replace_callback(
			'/{\$([a-zA-Z0-9_]+)}/',
			create_function('$matches', 'global $TMPL; return (isset($TMPL[$matches[1]])?$TMPL[$matches[1]]:"");'), $template);
		return $template;
	}
}
?>