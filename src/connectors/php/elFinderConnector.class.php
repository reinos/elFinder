<?php

class elFinderConnector {
	
	protected $elFinder;
	
	public function __construct($opts) {
		$isPost = $_SERVER["REQUEST_METHOD"] == 'POST';
		$src    = $isPost ? $_POST : $_GET;
		$cmd    = isset($src['cmd']) ? $src['cmd'] : '';
		$args   = array();
		
		if (!function_exists('json_encode')) {
			header("Content-Type: application/json");
			exit('{"error":"PHP JSON module not installed"}');
		}
		
		$this->elFinder = new elFinder($opts);
		
		if (!$this->elFinder->load()) {
			$this->output(array('error' => 'Invalid backend configuration'));
		}
			
		// telepat_mode: on
		if (!$cmd && $isPost) {
			$this->output(array('error' => 'Data exceeds the maximum allowed size'), 'Content-Type: text/html');
		}
		// telepat_mode: off
				
		if (!$this->elFinder->commandExists($cmd)) {
			$this->output(array('error' => 'Unknown command'));
		}
		
		foreach ($this->elFinder->commandArgsList($cmd) as $name => $req) {
			$arg = $name == 'FILES' ? $_FILES : (isset($src[$name]) ? trim($src[$name]) : '');
			if ($req && empty($arg)) {
				$this->output('Invalid parameters');
			}
			$args[$name] = $arg;
		}
		
		echo '<pre>';
		print_r($args);
		
		$result = $this->elFinder->exec($cmd, $args);
		
	}
	
	
	protected function output($data, $header = 'Content-Type: text/html' /*'Content-Type: application/json'*/) {
		
		if ($header) {
			header($header);
		}
		exit(json_encode($data));
	}
	
}

?>