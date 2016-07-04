<?php

class WhatWeDoController extends Controller {

  function handle($strings) {
  	$newStrings = $this->request->get("what-we-do");
		if ($newStrings) {
	    $rawRecentClients = $newStrings["recent-clients"];
	    $parsedRecentClients = explode(PHP_EOL, $rawRecentClients);
	    $parsedRecentClients = array_map('trim', $parsedRecentClients);
	    $parsedRecentClients = array_filter($parsedRecentClients);
	    $newStrings["recent-clients"] = $parsedRecentClients;

	    $rawList1 = $newStrings["list1"];
	    $parsedList1 = explode(PHP_EOL, $rawList1);
	    $parsedList1 = array_map('trim', $parsedList1);
	    $parsedList1 = array_filter($parsedList1);
	    $newStrings["list1"] = $parsedList1;

	    $rawList2 = $newStrings["list2"];
	    $parsedList2 = explode(PHP_EOL, $rawList2);
	    $parsedList2 = array_map('trim', $parsedList2);
	    $parsedList2 = array_filter($parsedList2);
	    $newStrings["list2"] = $parsedList2;

	    $strings["what-we-do"] = $newStrings;
	    writeJson($strings, $GLOBALS["stringsFilePath"]);
	  }
	  return $strings;
	}
}
