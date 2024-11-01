<?php

/**
 * Plugin Name:       Shoutbox 2024
 * Plugin URI:        https://garethjohnstone.co.uk/portfolio/shoutbox-2024
 * Short Description: Customisable shoutbox for your website
 * Description:       Customisable shoutbox for your website
 * Version:           2024.06.17
 * Author:            Gareth Johnstone
 * Author URI:        https://garethjohnstone.co.uk
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Copyright (C) 2024 Gareth Johnstone
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.

 */

 if( ! defined( 'ABSPATH' ) ) exit;

require_once 'controllers/shoutbox-ajax.php';
require_once 'controllers/shoutbox-database.php';

class shoutbox2024
{
  public $version = '2024.06.17';

  public function __construct()
  {

  }

  public function start_controllers()
  {
    require_once 'controllers/shoutbox-activation.php';
    $activation = new shoutbox2024_activation();
    $activation->hooks();

    require_once 'controllers/shoutbox-schedule.php';
    $schedule = new shoutbox2024_schedule();

    require_once 'controllers/shoutbox-shortcode.php';
    $shortcode = new shoutbox2024_shortcode();

    require_once 'controllers/shoutbox-scripts.php';
    $scripts = new shoutbox2024_scripts($this->version);
    $scripts->enqueue_scripts();
  }

  public function setupAjax()
  {
    $ajax = new shoutbox2024_ajax();
    $ajax->initialize();
  }
}

$shoutbox = new shoutbox2024();
$shoutbox->start_controllers();

function shoutbox2024_load()
{
  $shoutbox = new shoutbox2024();
  $shoutbox->setupAjax();

  require_once 'admin/shoutbox-admin.php';

}

add_action('init', 'shoutbox2024_load');

// Add settings link to plugin page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'shoutbox2024_settings_link');
function shoutbox2024_settings_link(array $links)
{
  $url_settings = get_admin_url() . "admin.php?page=shoutboxsettings";
  $settings_link = '<a href="' . $url_settings . '">' . __('Settings', 'shoutbox-2024') . '</a>';

  $url_smilies = get_admin_url() . "upload.php?page=shoutbox2024_smilies";
  $smilies_link = '<a href="' . $url_smilies . '">' . __('Smilies 🙂', 'shoutbox-2024') . '</a>';

  $url_coffee = 'https://buymeacoffee.com/garethjohnstone';
  $coffee_link = '<a href="' . $url_coffee . '" target="_blank">' . __('Buy me a coffee ☕', 'shoutbox-2024') . '</a>';

  $links[] = $settings_link;
  $links[] = $smilies_link;
  $links[] = $coffee_link;
  return $links;
}

