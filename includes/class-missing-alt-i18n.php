<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://whatjackhasmade.co.uk
 * @since      1.0.0
 *
 * @package    Missing_Alt
 * @subpackage Missing_Alt/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Missing_Alt
 * @subpackage Missing_Alt/includes
 * @author     Jack Pritchard <jack@noface.co.uk>
 */
class Missing_Alt_i18n
{
  /**
   * Load the plugin text domain for translation.
   *
   * @since    1.0.0
   */
  public function load_plugin_textdomain()
  {
    load_plugin_textdomain(
      "missing-alt",
      false,
      dirname(dirname(plugin_basename(__FILE__))) . "/languages/"
    );
  }
}
