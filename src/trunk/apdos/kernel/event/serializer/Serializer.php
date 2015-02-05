<?php 
namespace apdos\kernel\event\serializer;

abstract class Serializer {
  /**
   * 이벤트 객체를 직렬화하는 인터페이스 메서드
   *
   * @param event Event 이벤트 객체
   * @return object 직렬화 결과 데이터 
   */
  abstract public function write($event);

  /**
   * 데이터를 역직렬화하는 인터페이스 메서드
   *
   * @param object Object 역직렬화할 데이터
   */
  abstract public function read($object);
}
