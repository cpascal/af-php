<?php
namespace apdos\plugins\resource;

use apdos\kernel\actor\Component;

class File extends Component {
  private $contents = '';

  public function __construct() {
  }

  public function load($file_path) {
    $this->contents = file_get_contents($file_path);
    if (!$this->contents) {
      throw new File_Error("Read faield. path is $file_path", File_Error::FILE_IS_NOT_EXISTS);
    }
  }

  public function get_contents() {
    return $this->contents;
  }
}