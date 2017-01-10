<?php

namespace Pantheon\TerminusPancakes\Apps;

/**
 * Open Site database in MySQL CLI
 */
class MySQLApp extends PancakesApp {
  /**
   * {@inheritdoc}
   */
  public $aliases = ['mysql'];

  /**
   * {@inheritdoc}
   */
  public $app = 'MySQL';

  /**
   * - App Location Candinates
   */
  public $app_location;

  public function open(){
    $cmd = join(' ', [
      'mysql',
      '-h ' . $this->escapeShellArg($this->connection_info['mysql_host']),
      '-P ' . $this->escapeShellArg($this->connection_info['mysql_port']),
      '-u ' . $this->escapeShellArg($this->connection_info['mysql_username']),
      '-p' . $this->escapeShellArg($this->connection_info['mysql_password']),
      $this->connection_info['mysql_database'],
    ]);

    $process = proc_open(
      $cmd,
      [
        0 => STDIN,
        1 => STDOUT,
        2 => STDERR,
      ],
      $pipes
    );
    proc_close($process);
  }

  /**
   * Validates the app can be used
   */
  public function validate() {
    if ($this->isWindows()) {
      return FALSE;
    }

    $this->app_location = 'mysql';
    return $this->which($this->app_location);
  }
}
