<?php

class Controller {
  
  protected $request;

	function __construct($request) {
		$this->request = $request;
	}
}
