<?php

class AboutUsController extends Controller {
    
  function handle($strings) {
    $newStrings = $this->request->get("about-us");
    if ($newStrings) {
        $strings["about-us"] = $newStrings;
        writeJson($strings, $GLOBALS["stringsFilePath"]);
    }   
    return $strings;
  }
}
