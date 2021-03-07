<?php

/**
 * Register all actions and filters for the plugin
 *
 * @link       https://whatjackhasmade.co.uk
 * @since      1.0.0
 *
 * @package    Missing_Alt
 * @subpackage Missing_Alt/includes
 */

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Missing_Alt
 * @subpackage Missing_Alt/includes
 * @author     Jack Pritchard <jack@noface.co.uk>
 */
class Missing_Alt_Loader
{
  /**
   * The array of actions registered with WordPress.
   *
   * @since    1.0.0
   * @access   protected
   * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
   */
  protected $actions;

  /**
   * The array of filters registered with WordPress.
   *
   * @since    1.0.0
   * @access   protected
   * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
   */
  protected $filters;

  /**
   * Initialize the collections used to maintain the actions and filters.
   *
   * @since    1.0.0
   */
  public function __construct()
  {
    $this->actions = [];
    $this->filters = [];
  }

  /**
   * Add a new action to the collection to be registered with WordPress.
   *
   * @since    1.0.0
   * @param    string               $hook             The name of the WordPress action that is being registered.
   * @param    object               $component        A reference to the instance of the object on which the action is defined.
   * @param    string               $callback         The name of the function definition on the $component.
   * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
   * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
   */
  public function add_action(
    $hook,
    $component,
    $callback,
    $priority = 10,
    $accepted_args = 1
  ) {
    $this->actions = $this->add(
      $this->actions,
      $hook,
      $component,
      $callback,
      $priority,
      $accepted_args
    );
  }

  /**
   * Add a new filter to the collection to be registered with WordPress.
   *
   * @since    1.0.0
   * @param    string               $hook             The name of the WordPress filter that is being registered.
   * @param    object               $component        A reference to the instance of the object on which the filter is defined.
   * @param    string               $callback         The name of the function definition on the $component.
   * @param    int                  $priority         Optional. The priority at which the function should be fired. Default is 10.
   * @param    int                  $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
   */
  public function add_filter(
    $hook,
    $component,
    $callback,
    $priority = 10,
    $accepted_args = 1
  ) {
    $this->filters = $this->add(
      $this->filters,
      $hook,
      $component,
      $callback,
      $priority,
      $accepted_args
    );
  }

  /**
   * A utility function that is used to register the actions and hooks into a single
   * collection.
   *
   * @since    1.0.0
   * @access   private
   * @param    array                $hooks            The collection of hooks that is being registered (that is, actions or filters).
   * @param    string               $hook             The name of the WordPress filter that is being registered.
   * @param    object               $component        A reference to the instance of the object on which the filter is defined.
   * @param    string               $callback         The name of the function definition on the $component.
   * @param    int                  $priority         The priority at which the function should be fired.
   * @param    int                  $accepted_args    The number of arguments that should be passed to the $callback.
   * @return   array                                  The collection of actions and filters registered with WordPress.
   */
  private function add(
    $hooks,
    $hook,
    $component,
    $callback,
    $priority,
    $accepted_args
  ) {
    $hooks[] = [
      "hook" => $hook,
      "component" => $component,
      "callback" => $callback,
      "priority" => $priority,
      "accepted_args" => $accepted_args,
    ];

    return $hooks;
  }

  /**
   * Register the filters and actions with WordPress.
   *
   * @since    1.0.0
   */
  public function run()
  {
    foreach ($this->filters as $hook) {
      add_filter(
        $hook["hook"],
        [$hook["component"], $hook["callback"]],
        $hook["priority"],
        $hook["accepted_args"]
      );
    }

    foreach ($this->actions as $hook) {
      add_action(
        $hook["hook"],
        [$hook["component"], $hook["callback"]],
        $hook["priority"],
        $hook["accepted_args"]
      );
    }

    add_action("wp_ajax_convert_decorative", "convert_decorative");
    add_action("wp_ajax_nopriv_convert_decorative", "convert_decorative");

    function convert_decorative()
    {
      function failed()
      {
        $response = [
          "message" => "failed",
        ];

        echo json_encode($response);
        wp_die();
      }

      /*
      If AJAX/Fetch request contains key 'id' then
      Get the value of 'id' as a string

      If no 'id' defined then exit function
     */
      $id = isset($_POST["id"]) ? $_POST["id"] : failed();

      /*
      If AJAX/Fetch request contains key 'value' then
      Get the value of 'value' as a string

      If no 'value' defined then exit function
     */
      $value = isset($_POST["value"]) ? $_POST["value"] : failed();

      update_post_meta($id, "only_decorative", $value);

      $response = [
        "id" => $id,
        "message" => "success",
        "updated" => $value,
      ];

      echo json_encode($response);
      wp_die();
    }

    add_action("admin_menu", "missing_alt_menu_item");
    function missing_alt_menu_item()
    {
      $page_title = "Missing Alt";
      $menu_title = "Missing Alt";
      $capability = "manage_options";
      $menu_slug = "missing-alt";
      $function = "extra_post_info_page";
      $icon_url = "dashicons-media-code";
      $position = 10;

      add_menu_page(
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $function,
        $icon_url,
        $position
      );
    }

    // AND post_content LIKE '%<img%alt=\"\"%'

    function extra_post_info_page()
    {
      $sql = "SELECT ID FROM `wp_posts` WHERE post_type='attachment'";

      global $wpdb;
      $wpdb->get_results($sql);
      $results = $wpdb->last_result;

      $bad = [];
      $good = [];
      $decorative = [];

      foreach ($results as $key => $value):
        $id = $value->ID;

        $alt_text = get_post_meta($id, "_wp_attachment_image_alt", true);
        $decorative_comparison = get_post_meta($id, "only_decorative");
        $compare = $decorative_comparison[0];
        $only_decorative = $compare === "true";

        $object = (object) [
          "id" => $id,
          "alt_text" => $alt_text,
          "is_decorative" => $only_decorative,
        ];

        if ($alt_text):
          $good[] = $object;
        elseif (!$only_decorative):
          $bad[] = $object;
        else:
          $decorative[] = $object;
        endif;
      endforeach;

      $count_total = count($results);
      $count_good = count($good);
      $count_bad = count($bad);
      $count_decorative = count($decorative);

      function get_percentage($total, $number)
      {
        $percentage = ($number !== 0 ? $number / $total : 0) * 100;
        return round($percentage, 2);
      }

      $coverage_good = get_percentage($count_total, $count_good);
      $coverage_bad = get_percentage($count_total, $count_bad);
      $coverage_decorative = get_percentage($count_total, $count_decorative);

      ob_start();
      ?>

			<div style="margin: 0 50px;">
			<h1>Missing Alt text</h1>
			<p>You've added alt text to <?= $count_good ?> (<?= $coverage_good ?>%) images.</p>
			<p>You're missing alt text on <?= $count_bad ?> (<?= $coverage_bad ?>%) images.</p>
			<p>You have <?= $count_decorative ?> (<?= $coverage_decorative ?>%) decorative images.</p>
			<table><thead>
			<tr>
			<th>ID</th>
			<th>Is Decorative</th>
			<th>Actions</th>
			<th>Status</th>
      <th>Alt Text</th>
			</tr>
			</thead>
			<tbody>
			<? foreach($bad as $value): ?>
			<tr class="<?= $value->is_decorative
     ? "row row--missing row--decorative"
     : "row row--missing" ?>">
				<td><?= $value->id ?></td>
        <td>
          <button class="button button--decorative-handler" id="decorative_button_<?= $value->id ?>" data-id="<?= $value->id ?>">
          Is Decorative
          </button>
        </td>
				<td><a href="/wp/wp-admin/post.php?post=<?= $value->id ?>&action=edit" target="_blank">Edit</a></td>
				<td>Missing alt text attribute</td>
        <td><?= $value->alt_text ?></td>
			</tr>
			<? endforeach; ?>
			<? foreach($good as $value): ?>
			<tr class="<?= $value->is_decorative
     ? "row row--provided row--decorative"
     : "row row--provided" ?>">
				<td><?= $value->id ?></td>
        <td>
          <button class="button button--decorative-handler" id="decorative_button_<?= $value->id ?>" data-id="<?= $value->id ?>">
          Is Decorative
          </button>
        </td>
				<td><a href="/wp/wp-admin/post.php?post=<?= $value->id ?>&action=edit" target="_blank">Edit</a></td>
				<td>Has a valid alt text attribute</td>
        <td><?= $value->alt_text ?></td>
			</tr>
			<? endforeach; ?>
      <? foreach($decorative as $value): ?>
			<tr class="<?= $value->is_decorative
     ? "row row--provided row--decorative"
     : "row row--provided" ?>">
				<td><?= $value->id ?></td>
        <td>
          <button class="button button--decorative-handler" id="decorative_button_<?= $value->id ?>" data-id="<?= $value->id ?>">
          Is Decorative
          </button>
        </td>
				<td></td>
				<td></td>
        <td></td>
			</tr>
			<? endforeach; ?>
			</tbody>
			</table>
			</div>

			<?php
   $my_var = ob_get_clean();
   echo $my_var;
    }
  }
}
