<?php

namespace Pantheon\TerminusPancakes\Apps;

use Pantheon\Terminus\Commands\TerminusCommand;

/**
 * Interface PancakesApp
 * @package Pantheon\TerminusPancakes\Apps
 */
class PancakesApp{

  /**
   * @var array
   */
  protected $connection_info;

  /**
   * @var
   */
  protected $logger;

  /**
   * @var \Pantheon\TerminusPancakes\Commands\PancakesCommand
   */
  protected $command;

  /**
   * {@inheritdoc}
   */
  public $aliases = [];

  /**
   * {@inheritdoc}
   */
  public $app = '';

  /**
   * PancakesApp constructor.
   */
  public function __construct($connection_info, $logger){
    $this->connection_info = $connection_info;
    $this->logger = $logger;
  }

  /**
   * @return string
   */
  public function __toString() {
    return $this->app;
  }

  /**
   * @return void
   */
  public function open(){}

  /**
   * @return void
   */
  public function validate(){}

  /**
   * Execute Command
   * @param $command
   * @param $arguments
   * @return bool True if command executes without an error
   */
  protected function execCommand($command, $arguments = array()) {
    $arguments = is_array($arguments) ? $arguments : [$arguments];

    if (!empty($arguments)) {
      $command .= ' ' . implode(' ', $arguments);
    }
    $this->logger->debug('Executing: {command}', ['command' => $command]);
    exec($command, $output, $error_code);
  }

  /**
   * Writes a file to a temporary location
   *
   * @param $data
   * @param $suffix
   * @return mixed
   */
  protected function writeFile($data, $suffix = NULL) {
    $tempfile = tempnam(sys_get_temp_dir(), 'terminus-pancakes');
    $tempfile .= !empty($suffix) ? ('.' . $suffix) : '';

    $handle = fopen($tempfile, "w");
    fwrite($handle, $data);
    fclose($handle);
    return $tempfile;
  }
}