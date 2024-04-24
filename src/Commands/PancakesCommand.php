<?php

namespace Pantheon\TerminusPancakes\Commands;

use Pantheon\Terminus\Commands\TerminusCommand;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Site\SiteAwareTrait;
use Pantheon\Terminus\Exceptions\TerminusException;
use Pantheon\Terminus\Models\Environment;
use Pantheon\Terminus\Models\Site;
use Pantheon\TerminusPancakes\Apps\PancakesApp;

/**
 * Open Site database in your favorite MySQL Editor
 *
 * Terminus loads files based on \DirectoryIterator, so throw it on the top.
 */
class PancakesCommand extends TerminusCommand Implements SiteAwareInterface {
  use SiteAwareTrait;

  /**
   * @var array
   */
  private $connection_info;

  /**
   * Open Site database in Database Program
   *
   * @authorize
   *
   * @command site:pancakes
   * @aliases site:pc pancakes pc
   *
   * @param string $site_env Site & environment in the format `site-name.env`
   * @option string $app Application to Open (optional)
   *
   * @usage terminus site:pancakes <site>.<env> <app>
   *
   * @throws TerminusException
   */
  public function pancakes($site_env, array $options = ['app' => NULL]) {
    /* @var $env Environment */
    /* @var $site Site */
    list($site, $env) = $this->getSiteEnv($site_env);
    $env->wake();

    $this->connection_info = $env->connectionInfo();
    $this->connection_info['site_label'] = sprintf('%s [%s]', $site->get('name'), $env->id);

    $domain = $env->id . '-' . $site->get('name') . '.pantheon.io';
    $this->connection_info['connection_id'] = md5($domain . '.connection');
    $this->connection_info['server_instance_id'] = md5($domain . '.server');
    $parts = explode(':', $this->connection_info['sftp_url']);
    if (isset($parts[2])) {
      $sftp_port = $parts[2];
    } else {
      $sftp_port = 2222;
    }
    $this->connection_info['sftp_port'] = $sftp_port;

    $candidate_instances = $this->getCandidatePlugins();

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
        ->notice("Multiple Pancakes Applications were found: $candidates. Add --app to be specific on the app.", [
          'site' => $site->id,
        ]);
    }

    $this->log()
      ->notice('Opening {site} database in {app}.', [
        'site' => $env->id . '-' . $site->get('name') . '.pantheonsite.io',
        'app' => $instance->app
      ]);

    $instance->open();
  }

  /**
   * Gets Candidate Classes that are Loaded
   *
   * @return array
   */
  private function getCandidatePlugins() {
    $iterator = new \DirectoryIterator(__DIR__ . '/../Apps');

    // Autoload plugins
    foreach ($iterator as $file) {
      $plugin = 'Pantheon\TerminusPancakes\Apps\\' . $file->getBasename('.php');

      // Don't load PancakesApp since that's the base plugin.
      if (PancakesApp::class === $plugin || !$file->isFile()) {
        continue;
      }

      /* @var $candidate_instance PancakesApp */
      $candidate_instance = new $plugin($this->connection_info, $this->log());

      if (!$candidate_instance->validate()) {
        continue;
      }

      $candidate_instances[$candidate_instance->weight()] = $candidate_instance;
    }

    ksort($candidate_instances);

    return $candidate_instances;
  }
}
