<?php

abstract class Controller {
  
  protected $newConfig;
  protected $config;
  protected $context;

  abstract protected function parseNewConfig();
  abstract protected function getName();

	function __construct(&$request, &$config, &$context) {
    $this->newConfig = $request->get($this->getName());
    $this->config = $config;
    $this->context = $context;
	}

  function handle() {
    if ($this->newConfig) {
        $this->parseNewConfig();
        $this->config[$this->getName()] = $this->newConfig;
        
        writeJson($this->config, $GLOBALS["configFilePath"]);

        $this->context["config"] = $this->config;
        $this->context["saveSuccess"] = true;
    }
    return $this->context;
  }

  protected function explodeAndCleanup($input) {
    $output = explode(PHP_EOL, $input);
    $output = array_map('trim', $output);
    $output = array_filter($output);
    return $output;
  }
}
