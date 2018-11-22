<?php
/**
 * Created by PhpStorm.
 * User: Adrien
 * Date: 12/11/2018
 * Time: 22:49
 */


class Redux_Field_Descriptor_Fields implements ArrayAccess {
    protected $options;

    public static $order = 0;

    /**
     * Redux_Field_Descriptor_Fields constructor.
     * @param string $name
     * @param string $title
     * @param string $type
     * @param string $description
     * @param mixed  $default
     * @throws Exception
     */
    public function __construct($name, $title, $type, $description = '', $default = null) {
        if (!Redux_Descriptor_Types::isValidType($type)) {
            throw new Exception('Unknown type ' . $type . ' for option ' . $name);
        }
        if (!is_string($title) || empty($title)) {
            $title = ucfirst($name);
        }
        $this->options = array(
            'name'        => $name,
            'title'       => $title,
            'type'        => $type,
            'description' => $description,
            'default'     => $default,
            'order'       => static::$order ++,
        );
    }

    public function setOrder($order) {
        static::$order          = $order;
        $this->options['order'] = (float) $order;

        return $this;
    }

    public function setGroup($group) {
        $this->options['group'] = $group;

        return $this;
    }

    public function setOption($option_key, $option_value) {
        $this->options[$option_key] = $option_value;

        return $this;
    }

    public function removeOption($option_key) {
        unset($this->options[$option_key]);
    }

    /**
     * @return string
     */
    public function toDoc() {
        $doc = $this['name'] . "(" . $this['type'] . ")\n" . $this['description'] . "\n";

        return $doc;
    }

    /**
     * @return array
     */
    public function toArray() {
        return $this->options;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->options[] = $value;
        } else {
            $this->options[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->options[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->options[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->options[$offset]) ? $this->options[$offset] : null;
    }
}