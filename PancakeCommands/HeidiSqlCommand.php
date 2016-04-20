<?php

namespace Terminus\Commands;

use Terminus\Utils;

/**
 * Open Site database in HeidiSQL
 */
class HeidiSqlCommand extends PancakesCommand {

  /**
   * {@inheritdoc}
   */
  public $aliases = ['heidisql', 'heidi', 'hsql'];

  /**
   * {@inheritdoc}
   */
  public $app = 'HeidiSQL';

  /**
   * - App Location Candinates
   */
  public $app_location;

  /**
   * Validates the app can be used
   */
  protected function validate($args, $assoc_args) {
    if (!Utils\isWindows()) {
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

  /**
   * Open Site database in HeidiSQL
   */
  public function pancakes($args, $assoc_args) {
    $this->execCommand('start /b ""', [
      $this->app_location,
      '-h=' . $this->escapeShellArg($this->connection_info['mysql_host']),
      '-P=' . $this->escapeShellArg($this->connection_info['mysql_port']),
      '-u=' . $this->escapeShellArg($this->connection_info['mysql_username']),
      '-p=' . $this->escapeShellArg($this->connection_info['mysql_password']),
    ]);
  }

}
