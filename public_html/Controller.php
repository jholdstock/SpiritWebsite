<?php

abstract class Controller {
  
  protected $newStrings;
  protected $strings;
  protected $context;

  abstract protected function parseNewStrings();
  abstract protected function getName();

	function __construct(&$request, &$strings, &$context) {
    $this->newStrings = $request->get($this->getName());
    $this->strings = $strings;
    $this->context = $context;
	}

  function handle() {
    if ($this->newStrings) {
        $this->parseNewStrings();
        $this->strings[$this->getName()] = $this->newStrings;
        
        writeJson($this->strings, $GLOBALS["stringsFilePath"]);

        $this->context["strings"] = $this->strings;
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
