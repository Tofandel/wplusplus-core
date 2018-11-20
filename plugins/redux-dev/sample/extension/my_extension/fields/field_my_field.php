<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Don't duplicate me!
if (!class_exists('ReduxFramework_my_field', false)) {

    /**
     * Main ReduxFramework_options_object class
     *
     * @since       1.0.0
     */
    class ReduxFramework_my_field extends Redux_Field {
        /**
         * Field Constructor.
         * Required - must call the parent constructor, then assign field and value to vars
         *
         * @param array  $field
         * @param string $value
         * @param        $parent
         */
        function __construct($field = array(), $value = '', $parent) {
            parent::__construct($field, $value, $parent);

            // Set default args for this field to avoid bad indexes. Change this to anything you use.
            $defaults = array(
                'options' => array(),
                'stylesheet' => '',
                'output' => true,
                'enqueue' => true,
                'enqueue_frontend' => true
            );

            $this->field = wp_parse_args($this->field, $defaults);
        }

        /**
         * Field Render Function.
         * Takes the vars and outputs the HTML for the field in the settings
         *
         * @return      void
         */
        public function render() {
            //Render the field
        }

        /**
         * Enqueue Function.
         * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
         *
         * @return      void
         */
        public function enqueue() {
            wp_enqueue_script('redux-my-field', $this->_url . 'field_my_field' . Redux_Functions::isMin() . '.js', array('jquery', 'redux-js'), ReduxFramework_Extension_my_extension::$version, true);
            wp_enqueue_style('redux-my-field', $this->_url . 'field_my_field.css', array(), ReduxFramework_Extension_my_extension::$version, 'all');
        }

    }

}