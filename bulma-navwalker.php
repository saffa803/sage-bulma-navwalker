<?php
namespace App;

/**
 * Bulma "Navbar" component navwalker drop-in functionality for Sage 9
 * Version: 1.0
 * Author: Mike Sasnovski
 *
 */

/*
 * This walker was mostly copied from the Bulmapress theme.
 * https://github.com/teamscops/bulmapress
 *
 * The start_lvl method was improved based on https://gist.github.com/Log1x/358dd055554363488f6220c89a4ec358, fixing nested menu items.
 */

/**
 * Custom Navwalker Class
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Bulmapress
 *
 * Class Name: bulmapress_navwalker
 * Description: A custom WordPress nav walker class to implement the Bulma navigation style in a custom theme using the WordPress built in menu manager.
 * Version: 0.0.1
 * Author: Scops UG (haftungsbeschränkt)
 * Credit: Based on Bootstrap navwalker from Edward McIntyre - @twittem
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (class_exists('\Walker_Nav_Menu')) {
    class wp_bulma_navwalker extends \Walker_Nav_Menu
    {

        /**
         * @see Walker::start_lvl()
         * @since 3.0.0
         *
         * @param string $output Passed by reference. Used to append additional content.
         * @param int $depth Depth of page. Used for padding.
         */
        public function start_lvl(&$output, $depth = 0, $args = [])
        {
            // Depth
            $indent = ($depth ? str_repeat("\t", $depth) : '');
            // Class
            $class = ($depth == 1 ? '' : 'navbar-dropdown');
            // Output
            $output .= $indent . '<ul class="' . $class . '">';
        }

        /**
         * @see Walker::start_el()
         * @since 3.0.0
         *
         * @param string $output Passed by reference. Used to append additional content.
         * @param object $item Menu item data object.
         * @param int $depth Depth of menu item. Used for padding.
         * @param int $current_page Menu item ID.
         * @param object $args
         */
        public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
        {
            $indent = ($depth) ? str_repeat("\t", $depth) : '';

            /**
             * Dividers, Headers or Disabled
             * =============================
             * Determine whether the item is a Divider, Header, Disabled or regular
             * menu item. To prevent errors we use the strcasecmp() function to so a
             * comparison that is not case sensitive. The strcasecmp() function returns
             * a 0 if the strings are equal.
             */
            if (strcasecmp($item->attr_title, 'divider') == 0 && $depth === 1) {
                $output .= $indent . '<li role="presentation" class="divider">';
            } elseif (strcasecmp($item->title, 'divider') == 0 && $depth === 1) {
                $output .= $indent . '<li role="presentation" class="divider">';
            } elseif (strcasecmp($item->attr_title, 'dropdown-header') == 0 && $depth === 1) {
                $output .= $indent . '<li role="presentation" class="dropdown-header">' . esc_attr($item->title);
            } elseif (strcasecmp($item->attr_title, 'disabled') == 0) {
                $output .= $indent . '<li role="presentation" class="disabled"><a href="#">' . esc_attr($item->title) . '</a>';
            } else {
                $class_names = $value = '';

                $classes = empty($item->classes) ? array() : (array) $item->classes;
                $classes[] = 'navbar-item menu-item-' . $item->ID;

                $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));

                if ($args->has_children) {
                    $class_names .= ' has-dropdown is-hoverable';
                }

                if (in_array('current-menu-item', $classes)) {
                    $class_names .= ' active';
                }

                $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

                $id = apply_filters('nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args);
                $id = $id ? ' id="' . esc_attr($id) . '"' : '';

                $output .= $indent . '<li' . $id . $value . $class_names .'>';

                $atts = array();
                $atts['title']  = ! empty($item->title)	? $item->title	: '';
                $atts['target'] = ! empty($item->target)	? $item->target	: '';
                $atts['rel']    = ! empty($item->xfn)		? $item->xfn	: '';

                // If item has_children add atts to a.
                if ($args->has_children && $depth === 0) {
                    $atts['class']			= 'navbar-link';
                    $atts['aria-haspopup']	= 'true';
                }

                $atts['href'] = ! empty($item->url) ? $item->url : '';

                $atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args);

                $attributes = '';
                foreach ($atts as $attr => $value) {
                    if (! empty($value)) {
                        $value = ('href' === $attr) ? esc_url($value) : esc_attr($value);
                        $attributes .= ' ' . $attr . '="' . $value . '"';
                    }
                }

                $item_output = $args->before;

                /*
                 * Glyphicons
                 * ===========
                 * Since the the menu item is NOT a Divider or Header we check the see
                 * if there is a value in the attr_title property. If the attr_title
                 * property is NOT null we apply it as the class name for the glyphicon.
                 */
                if (! empty($item->attr_title)) {
                    $item_output .= '<a'. $attributes .'><span class="glyphicon ' . esc_attr($item->attr_title) . '"></span>&nbsp;';
                } else {
                    $item_output .= '<a'. $attributes .'>';
                }

                $item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
                $item_output .= ($args->has_children && 0 === $depth) ? ' <span class="caret"></span></a>' : '</a>';
                $item_output .= $args->after;

                $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
            }
        }

        /**
         * Traverse elements to create list from elements.
         *
         * Display one element if the element doesn't have any children otherwise,
         * display the element and its children. Will only traverse up to the max
         * depth and no ignore elements under that depth.
         *
         * This method shouldn't be called directly, use the walk() method instead.
         *
         * @see Walker::start_el()
         * @since 2.5.0
         *
         * @param object $element Data object
         * @param array $children_elements List of elements to continue traversing.
         * @param int $max_depth Max depth to traverse.
         * @param int $depth Depth of current element.
         * @param array $args
         * @param string $output Passed by reference. Used to append additional content.
         * @return null Null on failure with no changes to parameters.
         */
        public function display_element($element, &$children_elements, $max_depth, $depth, $args, &$output)
        {
            if (! $element) {
                return;
            }
            $id_field = $this->db_fields['id'];
            // Display this element.
            if (is_object($args[0])) {
                $args[0]->has_children = ! empty($children_elements[ $element->$id_field ]);
            }
            parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
        }

        /**
         * Menu Fallback
         * =============
         * If this function is assigned to the wp_nav_menu's fallback_cb variable
         * and a manu has not been assigned to the theme location in the WordPress
         * menu manager the function with display nothing to a non-logged in user,
         * and will add a link to the WordPress menu manager if logged in as an admin.
         *
         * @param array $args passed from the wp_nav_menu function.
         *
         */
        public static function fallback($args)
        {
            if (current_user_can('manage_options')) {
                extract($args);
                $fb_output = null;
                if ($container) {
                    $fb_output = '<' . $container;
                    if ($container_id) {
                        $fb_output .= ' id="' . $container_id . '"';
                    }
                    if ($container_class) {
                        $fb_output .= ' class="' . $container_class . '"';
                    }
                    $fb_output .= '>';
                }
                $fb_output .= '<li';
                if ($menu_class) {
                    $fb_output .= ' class="nav-item"';
                }
                $fb_output .= '>';
                $fb_output .= '<a class="button is-danger is-outlined" href="' . admin_url('nav-menus.php') . '">Add a menu</a>';
                $fb_output .= '</li>';
                if ($container) {
                    $fb_output .= '</' . $container . '>';
                }
                echo $fb_output;
            }
        }
    }
}
