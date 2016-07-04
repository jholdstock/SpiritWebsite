<?php

class ContactController extends Controller {

  function handle($strings) {
  	$newStrings = $this->request->get("contact");
  	if ($newStrings) {
      $rawAddress = $newStrings["address"];
      $parsedAddress = explode(PHP_EOL, $rawAddress);
      $parsedAddress = array_map('trim', $parsedAddress);
      $parsedAddress = array_filter($parsedAddress);
      $newStrings["address"] = $parsedAddress;

      $strings["contact"] = $newStrings;
      writeJson($strings, $GLOBALS["stringsFilePath"]);
    }
    return $strings;
  }
}
