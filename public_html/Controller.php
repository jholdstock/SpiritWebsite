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

        $oldConfig = $this->config[$this->getName()];
        $newConfig = $this->newConfig;

        foreach($oldConfig as $key => $value) {
          if ($key == "address"
            ||$key == "recent-clients"
            ||$key == "list1"
            ||$key == "list2") {
            $oldConfig[$key] = $newConfig[$key];
          } else if (is_array($value)) {

            if ($key == "bg" && !isset($newConfig["bg"])) {
              continue;
            }

            $newConfig2 = array_merge($value, $newConfig[$key]);
            $oldConfig[$key] = $newConfig2;
          } else {
            $oldConfig[$key] = $newConfig[$key];
          }
        }

        $this->config[$this->getName()] = $oldConfig;

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
