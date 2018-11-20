<?php

use Redux_Descriptor_Types as RDT;

abstract class Redux_Field {
    /**
     * @var Redux_Field_Descriptor
     */
    public static $descriptor;

    public static function makeBaseDescriptor() {
        static::$descriptor = new Redux_Field_Descriptor(get_called_class());
        static::$descriptor->addField('id', __('Field ID'), RDT::TEXT)->setOrder(0);
        static::$descriptor->addField('required', null, RDT::BOOL, __('Should the field be required'), false)->setOrder(1);
        static::$descriptor->addField('readonly', null, RDT::BOOL, __('Should the field be readonly'), false)->setOrder(20);
        static::$descriptor->addField('compiler', __('CSS Compiler'), RDT::BOOL, __('Should the field be sent to the compiler'), false)->setOrder(60);
        static::$descriptor->addField('output', __('CSS Output'), RDT::BOOL, '', false);
    }

    public static function getDescriptor() {
        static::$descriptor = null;
        static::makeDescriptor();
        //This part is out of opt name because it's non vendor dependant
        static::$descriptor = apply_filters('redux/field/' . static::$descriptor->getFieldType() . '/descriptor', static::$descriptor);

        return static::$descriptor;
    }

    public static function makeDescriptor() {
        static::makeBaseDescriptor();
    }

    abstract public function render();


    public $style = '';
    public $_dir = "";
    public $_url = "";
    public $timestamp = '';
    public $field;
    public $select2_config;

    /**
     * @var ReduxFramework
     */
    public $parent;

    /**
     * @var string|array
     */
    public $value;

    public function __construct($field = array(), $value = '', $parent) {
        $this->parent = $parent;
        $this->field  = $field;
        $this->value  = $value;

        $this->select2_config = array(
            'width'      => 'resolve',
            'allowClear' => false,
            'theme'      => 'default',
        );

        $this->set_defaults();

        $class_name = get_class($this);
        $reflector  = new ReflectionClass($class_name);
        $path       = $reflector->getFilename();
        $path_info  = Redux_Helpers::path_info($path);
        $this->_dir = trailingslashit(dirname($path_info['realpath']));
        $this->_url = trailingslashit(dirname($path_info['url']));

        $this->timestamp = ReduxCore::$_version;
        if ($parent->args['dev_mode']) {
            $this->timestamp .= '.' . time();
        }
    }

    protected function get_dir() {
        return $this->_dir;
    }

    public function media_query($style_data = '') {
        //var_dump($this->field['media_query']);

        $query_arr = $this->field['media_query'];
        $css       = '';

        if (isset($query_arr['queries'])) {
            foreach ($query_arr['queries'] as $idx => $query) {
                $rule      = isset($query['rule']) ? $query['rule'] : '';
                $selectors = isset($query['selectors']) ? $query['selectors'] : array();

                if (!is_array($selectors) && $selectors != '') {
                    $selectors = array($selectors);
                }

                if ($rule != '' && !empty($selectors)) {
                    $selectors = implode(",", $selectors);

                    $css .= '@media ' . $rule . '{';
                    $css .= $selectors . '{' . $style_data . '}';
                    $css .= '}';
                }
            }
        } else {
            return;
        }

        if (isset($query_arr['output']) && $query_arr['output']) {
            $this->parent->outputCSS .= $css;
        }

        if (isset($query_arr['compiler']) && $query_arr['compiler']) {
            $this->parent->compilerCSS .= $css;
        }
    }

    public function output($style = '') {
        if ($style != '') {
            if (!empty($this->field['output']) && is_array($this->field['output'])) {
                $keys                    = implode(",", $this->field['output']);
                $this->parent->outputCSS .= esc_attr($keys . "{" . $style . '}');
            }

            if (!empty($this->field['compiler']) && is_array($this->field['compiler'])) {
                $keys                      = implode(",", $this->field['compiler']);
                $this->parent->compilerCSS .= esc_attr($keys . "{" . $style . '}');
            }
        }
    }

    public function css_style($data) {

    }

    public function set_defaults() {

    }

    public function enqueue() {

    }

    public function localize($field, $value = "") {

    }

}