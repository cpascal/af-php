<?php
Loader::get_instance()->include_module('kernel/actor/events/remote_event');

class Req_Register_Guest extends Remote_Event {
  public static $REQ_REGISTER_GUEST = "req_register_guest";

  public function init() {
    parent::init(self::$REQ_REGISTER_GUEST);
  }
}