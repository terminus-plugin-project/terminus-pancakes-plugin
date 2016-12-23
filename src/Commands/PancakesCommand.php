<?php

namespace Pantheon\TerminusPancakes\Commands;

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;
use Pantheon\Terminus\Collections\Sites;
use Pantheon\Terminus\Exceptions\TerminusException;

use Pantheon\TerminusPancakes\Apps;

/**
 * Open Site database in your favorite MySQL Editor
 *
 * Terminus loads files based on \DirectoryIterator, so throw it on the top.
 */
class PancakesCommand extends TerminusCommand Implements SiteAwareInterface
{

  use SiteAwareTrait;

  /**
   * @var
   */
  private $connection_info;

  /**
   * Open Site database in Database Program
   *
   * @authorize
   *
   * @command site:pancakes
   * @aliases pc
   *
   * @param string $site_env Site & environment in the format `site-name.env`
   * @option string $app Application to Open (optional)
   *
   * @usage terminus site:pancakes <site>.<env> <app>
   */
  public function pancakes($site_env, $options = ['app' => NULL]) {
    list($site, $env) = $this->getSiteEnv($site_env);
    $env->wake();

    $this->connection_info = $env->connectionInfo();
    $this->connection_info['site_label'] = sprintf('%s [%s]', $site->get('name'), $env->id);

    $candidate_instances = $this->getCandinatePlugins();

    $instance = NULL;

    // Check if any of them match a direct parameter
    if (!empty($options['app'])) {
      $all_aliases = [];
      foreach ($candidate_instances as $candidate_instance) {
        if (isset($candidate_instance->aliases)) {
          $app_aliases = implode(', ', $candidate_instance->aliases);
          $all_aliases[] = "[{$candidate_instance->app}] $app_aliases";
          foreach ($candidate_instance->aliases as $alias) {
            if (strpos($alias, trim($options['app'])) !== FALSE) {
              $instance = $candidate_instance;
            }
          }
        }
      }

      if (empty($instance)) {
        $this->log()->error('{app} was not found. Valid Apps: {aliases}', [
          'app' => $options['app'],
          'aliases' => implode('; ', $all_aliases),
        ]);
        return;
      }
    }

    $indirect = FALSE;
    $candidates = implode(', ', $candidate_instances);

    if (empty($instance) && !empty($candidate_instances)) {
      $this->log()
        ->debug('Valid candidates: {candidates}', [
          'candidates' => $candidates,
        ]);

      if (count($candidate_instances) > 1) {
        $indirect = TRUE;
      }

      $instance = reset($candidate_instances);
    }

    if (empty($instance)) {
      $this->log()->error('No applications for pancakes found.');
      return;
    }

    if ($indirect) {
      $this->log()
        ->info("Multiple Pancakes Applications were found: $candidates. Add --app to be specific on the app.", [
          'site' => $site->id,
        ]);
    }

    $this->log()
      ->notice('Opening {site} database in {app}.', [
        'site' => $env->id . '-' . $site->get('name') . '.pantheon.io',
        'app' => $instance->app
      ]);

    $instance->open();
  }

  /**
   * Gets Candinate Classes that are Loaded
   * @return array
   */
  private function getCandinatePlugins() {
    // Find our Children!
    $classes = get_declared_classes();
    $candidate_instances = [];

    foreach ($classes as $class) {
      $reflection = new \ReflectionClass($class);
      if ($reflection->isSubclassOf('Pantheon\TerminusPancakes\Apps\PancakesApp')) {
        $candidate_instance = $reflection->newInstanceArgs([
          $this->connection_info,
          $this->log()
        ]);

        if (method_exists($candidate_instance, 'validate')) {
          if (!$candidate_instance->validate()) {
            continue;
          }
        }

        $candidate_instances[] = $candidate_instance;
      }
    }

    return $candidate_instances;
  }
}


// Include Sub-Commands - Terminus uses DirectoryIterator so we need to have better control over the order.
$iterator = new \DirectoryIterator(dirname(__FILE__) . '/../Apps');
foreach ($iterator as $file) {
  if ($file->isFile()) {
    include_once $file->getPathname();
  }
}