<?php

class WhatWeDoController extends Controller {
   
	protected function getName() {
		return "what-we-do";
	}

  protected function parseNewConfig() {
  	$recentClients = $this->newConfig["recent-clients"];
    $this->newConfig["recent-clients"] = $this->explodeAndCleanup($recentClients);

		$list1 = $this->newConfig["list1"];
    $this->newConfig["list1"] = $this->explodeAndCleanup($list1);

    $list2 = $this->newConfig["list2"];
    $this->newConfig["list2"] = $this->explodeAndCleanup($list2);    
	}
}
