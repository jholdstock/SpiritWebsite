<?php

class WhatWeDoController extends Controller {
   
	protected function getName() {
		return "what-we-do";
	}

  protected function parseNewStrings() {
  	$recentClients = $this->newStrings["recent-clients"];
    $this->newStrings["recent-clients"] = $this->explodeAndCleanup($recentClients);

		$list1 = $this->newStrings["list1"];
    $this->newStrings["list1"] = $this->explodeAndCleanup($list1);

    $list2 = $this->newStrings["list2"];
    $this->newStrings["list2"] = $this->explodeAndCleanup($list2);    
	}
}
