<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');

final class SWBL_HELPER {

    /**
     * Draw HTML item
     *
     * @since 1.0.0
     * @param string $type html tag: a, div, p, img, etc. ...
     * @param array $data tag attributes
     * @param string $content tag content
     * @return string html of an element
     */
    public static function draw_html_item($type, $data, $content = '') {
        $item = '<' . esc_attr($type);
        foreach ($data as $key => $value) {
            if (is_string($key) AND is_scalar($value)) {
                $item .= " " . esc_attr($key) . "='" . esc_attr($value) . "'";
            }
        }

        if (!empty($content) OR in_array($type, array('textarea'))) {
            $item .= '>' . $content . "</" . esc_attr($type) . ">";
        } else {
            $item .= ' />';
        }

        return $item;
    }

}
