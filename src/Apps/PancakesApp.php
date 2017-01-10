<?php

namespace Pantheon\TerminusPancakes\Apps;

/**
 * Interface PancakesApp
 * @package Pantheon\TerminusPancakes\Apps
 */
class PancakesApp {

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
   * @return bool
   */
  public function isWindows(){
    return php_uname('s') === 'Windows NT';
  }

  /**
   * Formats a flag for a OS
   *
   * @param $name
   * @return mixed
   */
  protected function flag($name) {
    // I was very tempted to use str_pad....
    // return str_pad($name, strlen($name) + 1 + !Utils\isWindows(), '-', STR_PAD_LEFT);
    return ($this->isWindows() ? "-" : "--") . $name;
  }

  /**
   * Runs which
   * @param $command
   * @return bool
   */
  protected function which($command) {
    return $this->execCommand("which $command 2> /dev/null");
  }

  /**
   * Platform Independent - Escape Shell Arg. Taken from Drush.
   *
   * @param $arg
   * @param bool $raw
   * @return string
   */
  protected function escapeShellArg($arg, $raw = FALSE) {
    // Short-circuit escaping for simple params (keep stuff readable)
    if (preg_match('|^[a-zA-Z0-9.:/_-]*$|', $arg)) {
      return $arg;
    }
    elseif ($this->isWindows()) {
      // Double up existing backslashes
      $arg = preg_replace('/\\\/', '\\\\\\\\', $arg);

      // Double up double quotes
      $arg = preg_replace('/"/', '""', $arg);

      // Double up percents.
      $arg = preg_replace('/%/', '%%', $arg);

      // Only wrap with quotes when needed.
      if (!$raw) {
        // Add surrounding quotes.
        $arg = '"' . $arg . '"';
      }

      return $arg;
    }
    else {
      // For single quotes existing in the string, we will "exit"
      // single-quote mode, add a \' and then "re-enter"
      // single-quote mode.  The result of this is that
      // 'quote' becomes '\''quote'\''
      $arg = preg_replace('/\'/', '\'\\\'\'', $arg);

      // Replace "\t", "\n", "\r", "\0", "\x0B" with a whitespace.
      // Note that this replacement makes Drush's escapeshellarg work differently
      // than the built-in escapeshellarg in PHP on Linux, as these characters
      // usually are NOT replaced. However, this was done deliberately to be more
      // conservative when running _drush_escapeshellarg_linux on Windows
      // (this can happen when generating a command to run on a remote Linux server.)
      $arg = str_replace(array("\t", "\n", "\r", "\0", "\x0B"), ' ', $arg);

      // Only wrap with quotes when needed.
      if (!$raw) {
        // Add surrounding quotes.
        $arg = "'" . $arg . "'";
      }

      return $arg;
    }
  }

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
    return exec($command, $output, $error_code);
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