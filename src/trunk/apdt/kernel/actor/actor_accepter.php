<?php
require_once 'apdt/kernel/actor/component.php';
require_once 'apdt/kernel/actor/events/proxy_event.php';
require_once 'apdt/kernel/actor/remote_actor.php';
require_once 'apdt/kernel/event/errors/event_error.php';
require_once 'apdt/kernel/actor/errors/actor_error.php';

/**
 * @class Actor_Accepter
 *
 * @brief Accepter와 통신하는 Actor에게 이벤트를 전송. 
 *        HTTP 프로토콜은 접속형태의 통신이 아니므로 send시에 전송할 이벤트를 문자열형태로 출력해주기만 하면 된다.
 *
 * @author Lee Hyeon-gi
 */
class Actor_Accepter extends Component {
  public function recv($json_string) {
    try {
      $proxy_event = Event::deserialize($json_string);
      $target_event = Event::deserialize_by_parameter($proxy_event->get_target_type(), 
                                                      $proxy_event->get_target_name(), 
                                                      $proxy_event->get_target_data());
      $target_event->connect(new Remote_Actor($this, 
                                              $proxy_event->get_sender_path(), 
                                              $proxy_event->get_receiver_path()));
      $this->get_parent()->dispatch_event($target_event);
    }
    catch (Event_Error $e) {
      throw new Actor_Error($e->getMessage());
    }
    catch (Exception $e) {
      throw new Actor_Error($e->getMessage());
    }
  }

  /**
   * 특정 path의 Actor에게 이벤트를 전송한다.
   *
   * @param remote_event Remote_Event 전송할 리모트 이벤트 객체
   * @param sender_path String 이벤트를 전송한 Actor의 path
   * @param receiver_path String 이벤트를 받을 Actor의 path
   */
  public function send_by_path($remote_event, $sender_path, $receiver_path) {
    $event = new Proxy_Event();
    $event->init($remote_event, $sender_path, $receiver_path);
    echo Event::serialize($event);
  }

  /**
   * 객체에게 모두 event를 전송한다. 
   *
   * @param remote_event Remote_Event 전송할 리모트 이벤트 객체
   */
  public function send($remote_event) {
    $event = new Proxy_Event();
    $event->init_by_null_receiver($remote_event, $this->get_parent()->get_path());
    echo Event::serialize($event);
  }
}