<?php
namespace af\tests\kernel\actor;

use af\plugins\test\Test_Case;
use af\plugins\test\Test_Suite;
use af\kernel\actor\actor;
use af\kernel\actor\net\Actor_Accepter;
use af\kernel\core\kernel;
use af\kernel\actor\errors\Actor_Error;
use af\tests\kernel\event\Dummy_Event;

class Actor_Accepter_Test extends Test_Case {
  public $receive_dummy_event = false;
  private $occur_actor_error = false;
  private $actor;
  private $actor_accepter;

  public function __construct($method_name) {
    parent::__construct($method_name);
  }

  public function test_json_string_event() {
    $this->actor_accepter->recv($this->create_event_string());
    $this->assert(true == $this->receive_dummy_event, 'receive dummy event');
  }

  public function test_wrong_property_event() {
    try {
      $this->actor_accepter->recv($this->create_wrong_property_string());
    }
    catch (Actor_Error $e) {
      $this->occur_actor_error = true;
    }
    $this->assert(true == $this->occur_actor_error, 'occur_actor_error is true');
  }

  public function test_wrong_json_string_event() {
    try {
      $this->actor_accepter->recv("s3df234234ds");
    }
    catch (Actor_Error $e) {
      $this->occur_actor_error = true;
    }
    $this->assert(true == $this->occur_actor_error, 'occur_actor_error is true');
  }

  public function test_actor_path() {
    $sender_path = '';
    $receiver_path = '';
    $this->actor->add_event_listener(Dummy_Event::$DUMMY_EVENT_NAME1, function ($event) use(&$sender_path, &$receiver_path) {
      $remote_actor = $event->get_remote_actor();
      $sender_path = $remote_actor->get_sender_path();
      $receiver_path = $remote_actor->get_receiver_path();
      // @TODO 상대방 actor에게 보내는 메시지 문자열이 맞는지 확인하는 테스트케이스 추가. send함수를 쓸 경우 sender/receiver가 뒤바꾼다.
      //$remote_actor->send($event);
    });
    
    $this->actor_accepter->recv($this->create_event_string());

    $this->assert(0 == strcmp($sender_path, '/sys/end_point'), 'sender path is /sys/end_point');
    $this->assert(0 == strcmp($receiver_path, '/temp/actor1'), 'receiver path is /temp/actor1');
  }

  public function set_up() {
    $this->actor = Kernel::get_instance()->new_object('af\kernel\actor\Actor', '/temp/actor1');
    $this->actor->add_event_listener(Dummy_Event::$DUMMY_EVENT_NAME1, $this->create_listener());
    $this->actor_accepter = $this->actor->add_component('af\kernel\actor\net\Actor_Accepter');
  }

  public function tear_down() {
    Kernel::get_instance()->delete_object('/temp/actor1');
  }

  private function create_listener() {
    $other = $this;
    return function($event) use(&$other) {
      $other->receive_dummy_event = true;
    };
  }

  private function create_event_string() {
    $event = array();
    $event['target_type'] = 'Dummy_Event';
    $event['target_name'] = Dummy_Event::$DUMMY_EVENT_NAME1;
    $event['target_data'] = array();
    $event['sender_path'] = '/sys/end_point';
    $event['receiver_path'] = '/temp/actor1';

    $data = array();
    $data['type'] = 'Proxy_Event';
    $data['name'] = 'proxy_event';
    $data['data'] = $event;
    
    return json_encode($data);
  }

  private function create_wrong_property_string() {
    $event = array();
    $event['target_type'] = 'Dummy_Event';
    $event['target_name'] = Dummy_Event::$DUMMY_EVENT_NAME1;
    $event['target_data'] = array();
    $event['sender_path'] = '/sys/end_point';

    $data = array();
    $data['type'] = 'Proxy_Event';
    $data['name'] = 'proxy_event';
    $data['data'] = $event;
    
    return json_encode($data);
  }

  public static function create_suite() {
    $suite = new Test_Suite('Actor_Accepter_Test');
    $suite->add(new Actor_Accepter_Test('test_json_string_event'));
    $suite->add(new Actor_Accepter_Test('test_wrong_property_event'));
    $suite->add(new Actor_Accepter_Test('test_wrong_json_string_event'));
    $suite->add(new Actor_Accepter_Test('test_actor_path'));
    return $suite;
  }
}
