<?php

class WhatWeDoController extends Controller {
   
	protected function getName() {
		return "what-we-do";
	}

  protected function parseNewStrings() {
  	$recentClients = $this->newStrings["recent-clients"];

		$list1 = $this->newStrings["list1"];

    $list2 = $this->newStrings["list2"];
	}
}
