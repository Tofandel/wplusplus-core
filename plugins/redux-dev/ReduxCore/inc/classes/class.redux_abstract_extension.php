<?php

if (!class_exists('Redux_Abstract_Extension', false)) {
    /**
     * Class Redux_Abstract_Extension
     * An abstract class to make the writing of redux extensions easier by allowing users to extend this class
     * @see the samples directory to find an usage example
     */
    abstract class Redux_Abstract_Extension {
        /**
         * @var string The version of the extension (This is a default value you may want to override it)
         */
        public static $version = "1.0.0";

        /**
         * @var string
         */
        protected $_extension_url;
        /**
         * @var string
         */
        protected $_extension_dir;

        /**
         * @var static The instance of the extension
         */
        protected static $_instance;

        /**
         * @var string The extension's file
         */
        protected $_file;

        /**
         * @var ReduxFramework The redux framework instance that spawned the extension
         */
        public $parent;

        /**
         * Redux_Abstract_Extension constructor.
         * @param ReduxFramework $parent
         * @param string         $file
         */
        public function __construct($parent, $file = '') {
            $this->parent = $parent;

            //If the file is not given make sure we have one
            if (empty($file)) {
                try {
                    $rc   = new ReflectionClass(get_class($this));
                    $file = $rc->getFileName();
                } catch (ReflectionException $e) {
                    //There will never be an exception but annoying warning
                }
            }
            $this->_file = $file;

            $this->_extension_dir = trailingslashit(str_replace('\\', '/', dirname($file)));
            $this->_extension_url = site_url(str_replace(trailingslashit(str_replace('\\', '/', ABSPATH)), '', $this->_extension_dir));

            static::$_instance = $this;
        }

        public static function getVersion() {
            return static::$version;
        }

        public static function getInstance() {
            return static::$_instance;
        }

        public function getDir() {
            return $this->_extension_dir;
        }

        public function getUrl() {
            return $this->_extension_url;
        }

        protected function add_field($field_name) {
            $file = $this->_file;
            add_filter('redux/fields', function ($classes) use ($field_name, $file) {
                $classes[$field_name] = trailingslashit(dirname($file)) . $field_name . '/field_' . $field_name . '.php';
            });
            add_filter('redux/' . $this->parent->args['opt_name'] . '/field/class/' . $field_name, array(
                &$this,
                'overload_field_path',
            ), 10, 2); // Adds the local field
        }

        public function overload_field_path($file, $field) {
            return trailingslashit(dirname($this->_file)) . $field['type'] . '/field_' . $field['type'] . '.php';
        }
    }
}