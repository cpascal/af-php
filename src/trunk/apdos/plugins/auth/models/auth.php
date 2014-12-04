<?php
require_once 'apdt/kernel/actor/component.php';
require_once 'apdt/plugins/auth/dto/user_dto.php';
require_once 'apdt/plugins/auth/accessors/user.php';
require_once 'apdt/plugins/auth/errors/auth_error.php';

/**
 * @class Auth
 *
 * @brief 인증 처리 플러그인. Auth_Storage 확장을 통해  여러 형태의 DB에 회원 정보를 저장한다.
 * @author Lee Hyeon-gi
 */
class Auth extends Component {
  private $storage;
  private $user_dto_class_name;

  public function __construct() {
  }

  /**
   * 컴포넌트를 시작
   *
   * @param storage Auth_Storage 사용할 스토리지 인스턴스
   * @param user_dto_class_name String 사용할 User_DTO 클래스명
  */
  public function start($storage, $user_dto_class_name) {
    $this->storage = $storage;
    $this->user_dto_class_name = $user_dto_class_name;
  }

  /**
   * 일반 회원 가입. User_DTO를 상속받은 경우 추가정보는 update_user 메서드를
   * 통해 갱신한다.
   *
   * @return User 유저 객체
   */
  public function register($register_id, $register_password, $register_email) {
    $user = new $this->user_dto_class_name;
    $user->register_id = $register_id;
    $user->register_password = $register_password;
    $user->register_email = $register_email;
    $this->update_user_dto($user);
    $this->register_user($user);
    return $this->get_user(array('register_id'=>$register_id));
  }

  /**
   * 게스트 회원 가입
   *
   * @return User 유저 객체
   */
  public function register_guest() {
    $user = new $this->user_dto_class_name;
    $this->update_user_dto($user);
    $this->register_user($user);
    return $this->get_user(array('uuid'=>$user->uuid));
  }


  /**
   * 디바이스 기반 회원 가입
   *
   * @param device_id String 변경되지 않는 유니크한 디바이스 아이디
   * @return User 유저 객체 
   */
  public function register_device($device_id) {
    $user = new $this->user_dto_class_name;
    $user->device_id = $device_id;
    $this->update_user_dto($user);
    $this->register_user($user);
    return $this->get_user(array('device_id'=>$device_id));
  }

  private function update_user_dto($user) {
    if (isset($_SERVER['REMOTE_ADDR']))
      $user->install_ip = $_SERVER['REMOTE_ADDR'];
    else
      $user->install_ip = '<unknown system>';
    $user->install_date = date('Y-m-d H:i:s');
    $user->uuid = $this->gen_uuid();
  }

  private function register_user($user) {
    try {
      $array = Object_Converter::to_array($user);
      $this->storage->register($array);
    }
    catch (Auth_Storage_Error $e) {
      throw new Auth_Error($e->getMessage());
    }
  }

  /**
   * http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
   * UUID를 사용하여 플레이어 아이디가 겹치지 않게 한다.
   * 완벽하게 유니크한 값을 생성하지 않는다는 점은 염두에 두어야 한다.
   */
  private function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                   // 32 bits for "time_low"
                   mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

                   // 16 bits for "time_mid"
                   mt_rand( 0, 0xffff ),

                   // 16 bits for "time_hi_and_version",
                   // four most significant bits holds version number 4
                   mt_rand( 0, 0x0fff ) | 0x4000,

                   // 16 bits, 8 bits for "clk_seq_hi_res",
                   // 8 bits for "clk_seq_low",
                   // two most significant bits holds zero and one for variant DCE1.1
                   mt_rand( 0, 0x3fff ) | 0x8000,

                   // 48 bits for "node"
                   mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
  }


  /**
   * 회원 탈퇴
   * 
   * @param user_uuid String 유저의 UUID
   */ 
  public function unregister($user_uuid) {
    try {
      $user = $this->get_user(array('uuid'=>$user_uuid));
      if ($user->is_null())
        throw new Auth_Uuid_Is_None('Uuis is none');
      $this->storage->unregister($user_uuid);
    }
    catch (Auth_Storage_Error $e) {
      throw new Auth_Error($e->getMessage());
    }
  }

  /**
   * 유저 정보 조회
   *
   * @param user_wheres array 찾으려는 유저 데이터 
   * @return User 유저 객체 
   */
  public function get_user($user_wheres) {
    try {
      $users = $this->storage->get_users($user_wheres);
      if (0 == count($users))
        return new Null_User();
      $user_dto = new $this->user_dto_class_name;
      $user_dto->deserialize($users[0]);
      return new User($user_dto);
    }
    catch (Auth_Storage_Error $e) {
      throw new Auth_Error($e->getMessage());
    }
    return new Null_User();
  }

  /**
   * 유저 로그인.
   *
   * @param register_id String 가입 아이디
   * @param register_password String 가입 패스워드 
   * @return User 유저 객체
   */
  public function login($register_id, $register_password) {
    try {
      $users = $this->storage->get_users(array('register_id'=>$register_id));
      if (0 == count($users))
        throw new Auth_Id_Is_None('user is null');
      $user_dto = new User_DTO();
      $user_dto->deserialize($users[0]);
      if (0 != strcmp($user_dto->register_password, $register_password))
        throw new Auth_Password_Is_Wrong('password is wrong');
      if (true == $user_dto->unregistered)
        throw new Auth_Is_Unregistered('unregisterd is true');
    }
    catch (Auth_Storage_Error $e) {
      throw new Auth_Error($e->getMessage());
    }
    return new User($users[0]);
  }

  /**
   * 유저 정보를 갱신
   *
   * @parma where array 갱신할 유저 정보
   * @param contents array 갱신할 유저 데이터
   */
  public function update_user($user_wheres, $contents) {
    try {
      $this->storage->update_user($user_wheres, $contents);
    }
    catch (Auth_Storage_Error $e) {
      throw new Auth_Erro($e->getMessage());
    }
  }
}
