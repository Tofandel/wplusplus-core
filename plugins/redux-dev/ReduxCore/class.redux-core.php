<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('ReduxCore', false)) {

    /**
     * Class ReduxCore
     */
    class ReduxCore {

        /**
         * @var
         */
        public static $instance;
        /**
         * @var
         */
        public static $_version;
        /**
         * @var
         */
        public static $_dir;
        /**
         * @var
         */
        public static $_url;
        /**
         * @var
         */
        public static $_path;
        /**
         * @var null
         */
        public static $_upload_dir = null;
        /**
         * @var null
         */
        public static $_upload_url = null;
        /**
         * @var bool
         */
        public static $_is_plugin = true;
        /**
         * @var string
         */
        public static $_installed = '';
        /**
         * @var bool
         */
        public static $_as_plugin = false;
        /**
         * @var bool
         */
        public static $_in_theme = false;
        /**
         * @var bool
         */
        public static $_pro_loaded = false;
        /**
         * @var array
         */
        public static $_google_fonts = array();
        /**
         * @var array
         */
        public static $_callers = array();
        /**
         * @var null
         */
        public static $_server = null;
        /**
         * @var null
         */
        public static $third_party_fixes = null;

        /**
         * @return ReduxCore
         */
        public static function instance() {
            if (!self::$instance) {
                self::$instance = new self();

                self::$instance->includes();
                self::$instance->init();
                self::$instance->hooks();
            }

            return self::$instance;
        }

        /**
         *
         */
        private function init() {
            if (class_exists('ReduxPro') && isset(ReduxPro::$_dir)) {
                self::$_pro_loaded = true;
            }

            self::$_dir = trailingslashit(wp_normalize_path(dirname(realpath(__FILE__))));

            Redux_Helpers::generator();

            // See if Redux is a plugin or not.
            $plugin_info = Redux_Helpers::is_inside_plugin(__FILE__);
            $theme_info  = Redux_Helpers::is_inside_theme(__FILE__);

            if (false !== $plugin_info) {
                self::$_installed = class_exists('ReduxFrameworkPlugin') ? 'plugin' : 'in_plugin';

                self::$_is_plugin = class_exists('ReduxFrameworkPlugin');
                self::$_as_plugin = true;
                self::$_url       = trailingslashit(dirname($plugin_info['url']));
            } elseif (false !== $theme_info) {
                self::$_url       = trailingslashit(dirname($theme_info['url']));
                self::$_in_theme  = true;
                self::$_installed = 'in_theme';
            }

            self::$_url       = apply_filters('redux/_url', self::$_url);
            self::$_dir       = apply_filters('redux/_dir', self::$_dir);
            self::$_is_plugin = apply_filters('redux/_is_plugin', self::$_is_plugin);

            $upload_dir        = wp_upload_dir();
            self::$_upload_dir = $upload_dir['basedir'] . '/redux/';
            self::$_upload_url = str_replace(array('https://', 'http://'), '//', $upload_dir['baseurl'] . '/redux/');

            self::$_upload_dir = apply_filters('redux/_upload_dir', self::$_upload_dir);
            self::$_upload_url = apply_filters('redux/_upload_url', self::$_upload_url);

            self::$_server = filter_input_array(INPUT_SERVER, $_SERVER);
        }

        /**
         * @param $parent
         * @param $args
         */
        public static function core_construct($parent, $args) {
            new Redux_P();

            self::$third_party_fixes = new Redux_ThirdParty_Fixes($parent);

            Redux_ThemeCheck::get_instance();

            self::tracking($parent);
        }

        /**
         * @param $parent
         */
        private static function tracking($parent) {
            if (isset($parent->args['allow_tracking']) && $parent->args['allow_tracking']) {
                if (file_exists(self::$_dir . '/inc/classes/class.redux_tracking.php')) {
                    $tracking = Redux_Tracking::get_instance();
                    $tracking->load($parent);
                }
            }
        }

        /**
         * @throws Exception
         */
        private function includes() {

            require_once dirname(__FILE__) . '/inc/classes/class.redux_path.php';

            spl_autoload_register(array($this, 'register_classes'));

            new Redux_Builder_Api();
            new Redux_Welcome();
        }

        /**
         * @param $class_name
         */
        public function register_classes($class_name) {
            if (!class_exists($class_name, false)) {

                // Backward compatibility for extensions sucks!
                if ('Redux_Instances' === $class_name && !class_exists('ReduxFrameworkInstances', false)) {
                    require_once Redux_Path::get_path('/inc/classes/class.redux_instances.php');
                    require_once Redux_Path::get_path('/inc/lib/lib.redux_instances.php');

                    return;
                }

                // Redux API.
                if ('Redux' === $class_name) {
                    require_once Redux_Path::get_path('/inc/classes/class.redux_api.php');

                    return;
                }

                // Redux extra theme checks.
                if ('Redux_ThemeCheck' === $class_name) {
                    require_once Redux_Path::get_path('/inc/themecheck/class.redux_themecheck.php');

                    return;
                }

                if ('Redux_Welcome' === $class_name) {
                    require_once Redux_Path::get_path('/inc/welcome/class.redux_welcome.php');

                    return;
                }

                if (strpos($class_name, 'ReduxFramework_') === 0) {
                    $field_name = strtolower(Redux_Helpers::remove_prefix($class_name, 'ReduxFramework_'));
                    $class_path = Redux_Path::get_path('/inc/fields/' . $field_name . '/field_' . $field_name . '.php');
                    if (file_exists($class_path)) {
                        include $class_path;

                        return;
                    }
                }
                // Everything else.
                $file = 'class.' . strtolower($class_name) . '.php';

                $class_path = Redux_Path::get_path('/inc/classes/' . $file);
                if (file_exists($class_path)) {
                    include $class_path;
                }
            }

            do_action('redux/core/includes', $this);
        }

        /**
         *
         */
        private function hooks() {
            do_action('redux/core/hooks', $this);
        }

        /**
         * @return bool
         */
        public static function is_heartbeat() {
            // Disregard WP AJAX 'heartbeat'call.  Why waste resources?
            if (isset($_POST) && isset($_POST['action']) && 'heartbeat' === $_POST['action']) {

                // Hook, for purists.
                if (has_action('redux/ajax/heartbeat')) {
                    do_action('redux/ajax/heartbeat');
                }

                // Buh bye!
                return true;
            }

            return false;
        }

    }

}
