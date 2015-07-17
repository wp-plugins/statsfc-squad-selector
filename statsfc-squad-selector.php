<?php
/*
Plugin Name: StatsFC Squad Selector
Plugin URI: https://statsfc.com/widgets/squad-selector
Description: StatsFC Squad Selector
Version: 1.1
Author: Will Woodward
Author URI: http://willjw.co.uk
License: GPL2
*/

/*  Copyright 2013  Will Woodward  (email : will@willjw.co.uk)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define('STATSFC_SQUADSELECTOR_ID',      'StatsFC_SquadSelector');
define('STATSFC_SQUADSELECTOR_NAME',    'StatsFC Squad Selector');
define('STATSFC_SQUADSELECTOR_VERSION', '1.1');

/**
 * Adds StatsFC widget.
 */
class StatsFC_SquadSelector extends WP_Widget
{
    public $isShortcode = false;

    private static $defaults = array(
        'title'       => '',
        'key'         => '',
        'squad'       => '',
        'orientation' => 'vertical'
    );

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(STATSFC_SQUADSELECTOR_ID, STATSFC_SQUADSELECTOR_NAME, array('description' => 'StatsFC Squad Selector'));
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance)
    {
        $instance    = wp_parse_args((array) $instance, self::$defaults);
        $title       = strip_tags($instance['title']);
        $key         = strip_tags($instance['key']);
        $squad       = strip_tags($instance['squad']);
        $orientation = strip_tags($instance['orientation']);
        ?>
        <p>
            <label>
                <?php _e('Title', STATSFC_SQUADSELECTOR_ID); ?>
                <input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
            </label>
        </p>
        <p>
            <label>
                <?php _e('StatsFC Key', STATSFC_SQUADSELECTOR_ID); ?>
                <input class="widefat" name="<?php echo $this->get_field_name('key'); ?>" type="text" value="<?php echo esc_attr($key); ?>">
            </label>
        </p>
        <p>
            <label>
                <?php _e('StatsFC Squad ID', STATSFC_SQUADSELECTOR_ID); ?>
                <input class="widefat" name="<?php echo $this->get_field_name('squad'); ?>" type="text" value="<?php echo esc_attr($squad); ?>">
            </label>
        </p>
        <p>
            <label>
                <?php _e('Orientation', STATSFC_SQUADSELECTOR_ID); ?>
                <select class="widefat" name="<?php echo $this->get_field_name('orientation'); ?>">
                    <?php
                    foreach (array('vertical', 'horizontal') as $direction) {
                        echo '<option value="' . esc_attr($direction) . '"' . ($direction == $orientation ? ' select' : '') . '>' . esc_attr($direction) . '</option>' . PHP_EOL;
                    }
                    ?>
                </select>
            </label>
        </p>
    <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance)
    {
        $instance                = $old_instance;
        $instance['title']       = strip_tags($new_instance['title']);
        $instance['key']         = strip_tags($new_instance['key']);
        $instance['squad']       = strip_tags($new_instance['squad']);
        $instance['orientation'] = strip_tags($new_instance['orientation']);

        return $instance;
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        extract($args);

        $title       = apply_filters('widget_title', $instance['title']);
        $key         = $instance['key'];
        $squad       = $instance['squad'];
        $orientation = $instance['orientation'];

        $html  = $before_widget;
        $html .= $before_title . $title . $after_title;

        try {
            if (strlen($squad) == 0) {
                throw new Exception('Please enter a StatsFC squad ID from the widget options');
            }

            wp_register_script(STATSFC_SQUADSELECTOR_ID . '-js', plugins_url('script.js', __FILE__), null, STATSFC_SQUADSELECTOR_VERSION, true);
            wp_enqueue_script(STATSFC_SQUADSELECTOR_ID . '-js');

            $key         = esc_attr($key);
            $squad       = esc_attr($squad);
            $orientation = esc_attr($orientation);
            $width       = ($orientation == 'vertical' ? 'style="width: 100%; min-width: 310px;"' : 'width="710"');

            $html .= <<< HTML
            <iframe id="statsfc-squad-selector" src="https://xi.statsfc.com/{$key}/{$squad}?orientation={$orientation}" {$width} height="750" scrolling="no" frameborder="no"></iframe>
HTML;
        } catch (Exception $e) {
            $html .= '<p style="text-align: center;">StatsFC.com – ' . esc_attr($e->getMessage()) . '</p>' . PHP_EOL;
        }

        $html .= $after_widget;

        if ($this->isShortcode) {
            return $html;
        } else {
            echo $html;
        }
    }

    public static function shortcode($atts)
    {
        $args = shortcode_atts(static::$defaults, $atts);

        $widget              = new static;
        $widget->isShortcode = true;

        return $widget->widget(array(), $args);
    }
}

// Register StatsFC widget
add_action('widgets_init', function()
{
    register_widget(STATSFC_SQUADSELECTOR_ID);
});

add_shortcode('statsfc-squad-selector', STATSFC_SQUADSELECTOR_ID . '::shortcode');
