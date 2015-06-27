<?php
namespace tests\apdos\plugins\database\connecters\mysql;

use apdos\plugins\test\Test_Suite;
use apdos\kernel\core\kernel;
use apdos\plugins\test\Test_Case;
use apdos\plugins\database\connecters\mysql\Mysql_Connecter;

class Mysql_Util_Test extends Test_Case {
  private $connecter;
  private $util;

  public function test_database_exists() {
    //$this->assert($this->connecter->has_database('test_db'), "Database test_db is exist");
  }

  public function set_up() {
    $actor = Kernel::get_instance()->new_object('apdos\kernel\actor\Actor', '/sys/db/mysql');
    $this->connecter = $actor->add_component('apdos\plugins\database\connecters\mysql\Mysql_Connecter'); 
    $this->connecter->connect('p:localhost', 'root', ''); 

    //$this->util = $actor->add_component('apdos\plugins\database\connecters\mysql\Mysql_Util');
    //$this->util->set_property('connecter', $this->connecter);
  }

  public function tear_down() {
    //$this->util->drop_database('test_db');
    $this->connecter->close();
    Kernel::get_instance()->delete_object('/sys/db/mysql');
  }

  public static function create_suite() {
    $suite = new Test_Suite('Mysql_Util_Test');
    $suite->add(new Mysql_Util_Test('test_database_exists'));
    return $suite;
  }
}
