<?php

namespace Pantheon\TerminusPancakes\Apps;

/**
 * Open Site database in HeidiSQL
 */
class HeidiSqlCommand extends PancakesApp{

  /**
   * {@inheritdoc}
   */
  public $aliases = ['heidisql', 'heidi'];

  /**
   * {@inheritdoc}
   */
  public $app = 'HeidiSQL';

  /**
   * - App Location Candinates
   */
  public $app_location;


  public function open(){
    $this->execCommand('start /b ""', [
      $this->app_location,
      '-h=' . $this->escapeShellArg($this->connection_info['mysql_host']),
      '-P=' . $this->escapeShellArg($this->connection_info['mysql_port']),
      '-u=' . $this->escapeShellArg($this->connection_info['mysql_username']),
      '-p=' . $this->escapeShellArg($this->connection_info['mysql_password']),
    ]);
  }

  /**
   * Validates the app can be used
   */
  public function validate() {
    if ($this->isWindows()) {
      return FALSE;
    }

    // @TODO: Should probably just check the path for these instead...
    $candidates = array(
      '\Program Files\HeidiSQL\heidisql.exe',
      '\Program Files (x86)\HeidiSQL\heidisql.exe',
      "'" . getenv('TERMINUS_PANCAKES_HEIDISQL_LOC') . "'",
    );

    foreach ($candidates as $candinate) {
      if (file_exists($candinate)) {
        $this->app_location = '"' . $candinate . '"';
        break;
      }
    }

    return !empty($this->app_location);
  }
}