<?php
require_once 'apdt/kernel/actor/component.php';

class Auth_Storage extends Component {
  /**
   * 새로운 유저를 추가
   *
   * @param user Array 유저 정보
   */
  public function register($user) {
  }

  /**
   * 유저 정보를 조회
   *
   * @param where array 조회할 유저 정보
   * @return Array 유저 정보 리스트
   */
  public function get_users($wheres) {
  }

  /**
   * 유저의 정보를 변경
   *
   * @parma where array 갱신할 유저 정보
   * @param contents array 갱신할 유저 데이터
   */ 
  public function update_user($wheres, $contents) {
  }

  /**
   * 유저를 회원 탈퇴 상태로 변경 
   *
   * @param where array 탈퇴시킬 유저 정보
   */
  public function unregister($user_uuid) {
  }
}