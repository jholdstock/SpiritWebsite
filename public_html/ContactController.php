<?php

class ContactController extends Controller {
   
  protected function getName() {
    return "contact";
  }

  protected function parseNewStrings() {
    $address = $this->newStrings["address"];
  }

}
