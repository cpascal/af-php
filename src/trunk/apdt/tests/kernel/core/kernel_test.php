<?php
require_once 'apdt/plugins/test/test_case.php';
require_once 'apdt/kernel/core/kernel.php';

class Kernel_Test extends Test_Case {
  public function __construct($method_name) {
    parent::__construct($method_name);
  }

  public function test_create() {
    $kernel = new Kernel();
    $node_class = 'Node';
    $node_path = '/temp/node1';
    $node = $kernel->new_object($node_class, $node_path);

    $this->assert($node->get_name() == 'node1', 'node name');
  }
}
