<?php
namespace apdos\tests\plugins\database\connecters\mysql;

use apdos\kernel\core\kernel;
use apdos\plugins\test\Test_Case;
use apdos\plugins\database\connecters\mysql\Mysql_Connecter;

class Mysql_Connecter_Test extends Test_Case {
  const TEST_DATABASE_NAME = "test_db";
  const TEST_TABLE_NAME = "test_table";

  public function test_create_database() {
    $this->assert($this->connecter->has_database(self::TEST_DATABASE_NAME), "Database is exist");
  }

  public function test_drop_database() {
    $this->assert($this->connecter->has_database(self::TEST_DATABASE_NAME), "Database is exist");
    $this->connecter->simple_query($this->drop_database_query(self::TEST_DATABASE_NAME));
    $this->assert(!$this->connecter->has_database(self::TEST_DATABASE_NAME), "Database is not exist");
  }

  public function test_insert() {
    $this->connecter->select_database(self::TEST_DATABASE_NAME);
    $this->assert(!$this->connecter->has_table(self::TEST_TABLE_NAME), "Table is not exist");
    $this->connecter->simple_query($this->create_table_query(self::TEST_TABLE_NAME));
    $this->assert($this->connecter->has_table(self::TEST_TABLE_NAME), "Table is exist");
    $this->connecter->simple_query($this->create_insert_query(self::TEST_TABLE_NAME, '11111'));
    $result = $this->connecter->query($this->create_count_query(self::TEST_TABLE_NAME));
    $rows = $result->get_result();
    $this->assert($rows[0]['count'] == 1, 'Row count is 1');
  }

  public function test_select() {
    $this->connecter->select_database(self::TEST_DATABASE_NAME);
    $this->connecter->simple_query($this->create_table_query(self::TEST_TABLE_NAME));
    $this->connecter->simple_query($this->create_insert_query(self::TEST_TABLE_NAME, '11111'));
    $result = $this->connecter->query($this->create_select_query(self::TEST_TABLE_NAME));
    $rows = $result->get_result();
    $this->assert($result->get_rows_count() == 1, "Rows count is 1");
    $this->assert($rows[0]['id'] == 1, "Id is 1");
    $this->assert($rows[0]['title'] == '11111', "Title is 11111");
  }

  public function test_delete() {
  }

  public function test_transaction() {
  }
  
  public function set_up() {
    $actor = Kernel::get_instance()->new_object('apdos\kernel\actor\Actor', '/sys/db/mysql');
    $this->connecter = $actor->add_component('apdos\plugins\database\connecters\mysql\Mysql_Connecter');
    $this->connecter->connect('localhost', 'root', 'hserver1@sql');
    $this->connecter->simple_query($this->create_database_query(self::TEST_DATABASE_NAME));
  }

  private function drop_database_query($name) {
    return "drop database if exists $name";
  }

  private function create_database_query($name) {
    return "create database if not exists $name";
  }

  private function create_table_query($name) {
    return "create table $name(\n
      id int(9) unsigned not null auto_increment primary key,
      title varchar(128) not null default ''\n
    )";
  }

  private function create_insert_query($table_name, $title) {
    return "insert into $table_name values(0, '$title')";
  }

  private function create_count_query($table_name) {
    return "select count(*) as count from $table_name";
  }

  private function create_select_query($table_name) {
    return "select id, title from $table_name";
  }

  private function create_select_where_query($table_name, $find_title) {
    return "select id, title from $table_name where title='$find_title'";
  }

  public function tear_down() {
    $this->connecter->simple_query($this->drop_database_query(self::TEST_DATABASE_NAME));
    $this->connecter->close();
    Kernel::get_instance()->delete_object('/sys/db/mysql');
  }
}

