<?php

class ContactController extends Controller {
   
  protected function getName() {
    return "contact";
  }

  protected function parseNewConfig() {
    $address = $this->newConfig["address"];
    $this->newConfig["address"] = $this->explodeAndCleanup($address);
  }

}
