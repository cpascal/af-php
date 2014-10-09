<?php
/**
 * @class Event_Database
 *
 * @brief 정의된 이벤트 데이터를 관리
 * @author Lee Hyeon-gi
 */
class Event_Database {
  private $class_names = array();

  public function __construct() {
    // @TODO REMOVE
    $this->add_event('Event', 'Event');
    $this->add_event('Proxy_Event', 'Proxy_Event');
    $this->add_event('Proxy_Event', 'Proxy_Event');
    $this->add_event('Req_Get_User', '\ft\sys\presenters\auth_presenter\events\Req_Get_User');
    $this->add_event('Res_Get_User', '\ft\sys\presenters\auth_presenter\events\Res_Get_User');
    $this->add_event('Req_Register_Device', '\ft\sys\presenters\auth_presenter\events\Req_Register_Device');
    $this->add_event('Res_Register_Device', '\ft\sys\presenters\auth_presenter\events\Res_Register_Device');
  }

  /**
   * 이벤트 클래스의 이름을 조회
   * 
   * @param event_name String 이벤트 이름
   * @return String 이벤트 클래스 이름(네임스페이스포함)
   */
  public function get_class_name($event_name) {
    if (isset($this->class_names[$event_name]))
      return $this->class_names[$event_name];
    return '';
  }

  public function add_event($event_name, $class_name) {
    $this->class_names[$event_name] = $class_name;
  }

  public static function get_instance() {
    static $instance = null;
    if (null == $instance) {
      $instance = new Event_Database();
    }
    return $instance;
  }
}