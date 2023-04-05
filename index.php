<?php

/*
  Plugin Name: Show Widget by Logic
  Plugin URI: #
  Description: Show Widget by Logic using: WordPress Conditional Tags, WooCommerce Conditional Tags, custom PHP code
  Requires at least: WP 5.8
  Tested up to: WP 6.0
  Author: realmag777
  Author URI: https://pluginus.net/
  Version: 1.0.0
  Requires PHP: 7.0
  Tags: #
  Text Domain: show-widget-by-logic
  Domain Path: /languages
  Forum URI: #
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('SWBL_PATH', plugin_dir_path(__FILE__));
define('SWBL_LINK', plugin_dir_url(__FILE__));

include SWBL_PATH . 'classes/helper.php';

//13-09-2022
final class SWBL {

    public function __construct() {
        add_action('init', array($this, 'init'), 1);
    }

    public function init() {
        load_plugin_textdomain('show-widget-by-logic', false, dirname(plugin_basename(__FILE__)) . '/languages');

        add_filter('in_widget_form', array($this, 'in_widget_form'), 10, 3);

        add_filter('widget_update_callback', function ($instance, $new_instance, $old_instance, $this_widget) {
            isset($new_instance['show_widget_by_logic']) ? ($instance['show_widget_by_logic'] = $new_instance['show_widget_by_logic']) : null;
            return $instance;
        }, 10, 4);

        if (!is_admin() && !isset($_REQUEST['_locale'])) {
            add_filter('sidebars_widgets', function ($sidebars) {

                if (isset($sidebars['wp_inactive_widgets'])) {
                    unset($sidebars['wp_inactive_widgets']);
                }

                if (!empty($sidebars)) {
                    foreach ($sidebars as $sidebar => $widgets) {
                        if (!empty($widgets)) {
                            foreach ($widgets as $k => $wid) {

                                if (preg_match('/^(.+)-(\d+)$/', $wid, $wdt)) {
                                    $data = get_option('widget_' . $wdt[1], [])[$wdt[2]];
                                } else {
                                    $data = (array) get_option('widget_' . $wid, []);
                                }

                                if (isset($data['show_widget_by_logic']) AND!empty($data['show_widget_by_logic'])) {

                                    $conditions = esc_html(trim($data['show_widget_by_logic']));
                                    $conditions = trim($conditions, ';');
                                    $conditions = str_replace('$wpdb', '', $conditions);
                                    $conditions = htmlspecialchars_decode($conditions, ENT_QUOTES);
                                    //$conditions = str_replace('&amp;&amp;', '&&', $conditions); //for AND using ampersands
                                    //https://www.w3schools.com/php/php_ref_filesystem.asp
                                    //functions which should be avoid
                                    $patterns = [
                                        'chgrp',
                                        'chmod',
                                        'chown',
                                        'clearstatcache',
                                        'copy',
                                        'delete',
                                        'fclose',
                                        'fflush',
                                        'file_put_contents',
                                        'flock',
                                        'fopen',
                                        'fputs',
                                        'fwrite',
                                        'ftruncate',
                                        'lchgrp',
                                        'lchown',
                                        'link',
                                        'mkdir',
                                        'move_uploaded_file',
                                        'parse_ini_file',
                                        'pclose',
                                        'popen',
                                        'rename',
                                        'rmdir',
                                        'set_file_buffer',
                                        'symlink',
                                        'tempnam',
                                        'tmpfile',
                                        'touch',
                                        'umask',
                                        'unlink',
                                    ];

                                    foreach ($patterns as $key => $value) {
                                        $patterns[$key] = '/~' . $value . '\(.*\)/U'; //~ is mask here
                                    }

                                    //clear of dangerous file operations
                                    $conditions = '~' . $conditions; //set mask
                                    $conditions = str_replace(' ', '~', $conditions); //set mask
                                    $conditions = preg_replace($patterns, '~true', $conditions);
                                    $conditions = str_replace('~', ' ', $conditions); //remove mask

                                    if (!empty($conditions)) {
                                        try {
                                            if (!eval("return ( {$conditions} );")) {
                                                unset($sidebars[$sidebar][$k]);
                                            }
                                        } catch (Error $e) {
                                            add_filter('widget_title', function ($title, $instance, $id_base)use ($e, $wid) {

                                                if (!substr_count($title, 'E_USER_WARNING')) {
                                                    $tmp = explode('-', $wid);
                                                    if ($tmp[0] === $id_base) {
                                                        return $title . $id_base . ' <mark>[E_USER_WARNING: ' . esc_html($e->getMessage()) . ']</mark>';
                                                    }
                                                }

                                                return $title;
                                            }, 10, 3);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                return $sidebars;
            }, 9999, 1);
        }
    }

    public function in_widget_form($widget, $return, $instance) {
        echo wp_kses_post(SWBL_HELPER::draw_html_item('p', [],
                        SWBL_HELPER::draw_html_item('label', [
                            'for' => $widget->get_field_id('show_widget_by_logic')
                                ], esc_html__('Show Widget by Logic', 'show-widget-by-logic') . ':'
                        )
                        . SWBL_HELPER::draw_html_item('textarea', [
                            'id' => $widget->get_field_id('show_widget_by_logic'),
                            'name' => $widget->get_field_name('show_widget_by_logic'),
                            'class' => 'widefat'
                                ], esc_textarea(isset($instance['show_widget_by_logic']) ? $instance['show_widget_by_logic'] : '')
                        ) . SWBL_HELPER::draw_html_item('a', [
                            'href' => 'https://codex.wordpress.org/Conditional_Tags',
                            'target' => '_blank'
                                ], 'WordPress Conditional Tags'
                        ) . ', ' . SWBL_HELPER::draw_html_item('a', [
                            'href' => 'https://woocommerce.com/document/conditional-tags/',
                            'target' => '_blank'
                                ], 'WooCommerce Conditional Tags'
                        )
        ));

        return true;
    }

}

new SWBL();

