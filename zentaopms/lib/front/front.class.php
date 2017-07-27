<?php
/**
 * ZenTaoPHP的前端类。
 * The front class file of ZenTaoPHP framework.
 *
 * The author disclaims copyright to this source code.  In place of
 * a legal notice, here is a blessing:
 * 
 *  May you do good and not evil.
 *  May you find forgiveness for yourself and forgive others.
 *  May you share freely, never taking more than you give.
 */

helper::import(dirname(dirname(__FILE__)) . '/base/front/front.class.php');
/**
 * html类，生成html标签。
 * The html class, to build html tags.
 * 
 * @package framework
 */
class html extends baseHTML
{
    /**
     * 生成超链接。
     * Create tags like <a href="">text</a>
     *
     * @param  string $href      the link url.
     * @param  string $title     the link title.
     * @param  string $misc      other params.
     * @param  string $newline
     * @static
     * @access public
     * @return string
     */
    static public function a($href = '', $title = '', $target = "_self", $misc = '', $newline = true)
    {
        if(empty($target)) $target = '_self';
        if($target != '_self') $misc .= " target='$target'";
        return parent::a($href, $title, $misc, $newline);
    }

    /**
     * 创建提交按钮。
     * Create submit button.
     * 
     * @param  string $label    the label of the button
     * @param  string $class    the class of the button
     * @param  string $misc     other params
     * @static
     * @access public
     * @return string the submit button tag.
     */
    public static function submitButton($label = '', $misc = '', $class = 'btn btn-primary')
    {
        return parent::submitButton($label, $class, $misc);
    }

    public static function commonButton($label = '', $misc = '', $class = 'btn btn-default', $icon = '')
    {
        return parent::commonButton($label, $class, $misc, $icon);
    }

    public static function linkButton($label = '', $link = '', $target = 'self', $misc = '', $class = 'btn btn-default')
    {
        return parent::linkButton($label, $link, $class, $misc, $target);
    }

    /**
     * 创建全选checkbox。
     * Create select buttons include 'selectAll' and 'selectReverse'.
     * 
     * @param  string $scope  the scope of select reverse.
     * @param  bool   $asGroup 
     * @param  string $appendClass 
     * @static
     * @access public
     * @return string
     */
    static public function selectButton($scope = "", $asGroup = true, $appendClass = 'btn')
    {
        global $lang;
        return "<div class='checkbox $appendClass'><label><input type='checkbox' data-scope='$scope' class='rows-selector'> $lang->select</label></div>";
    }

    /**
     * 生成select标签。
     * Create tags like "<select><option></option></select>"
     *
     * @param  string $name          the name of the select tag.
     * @param  array  $options       the array to create select tag from.
     * @param  string $selectedItems the item(s) to be selected, can like item1,item2.
     * @param  string $attrib        other params such as multiple, size and style.
     * @param  string $append        adjust if add options[$selectedItems].
     * @static
     * @access public
     * @return string
     */
    static public function select($name = '', $options = array(), $selectedItems = "", $attrib = "", $append = false)
    {
        $options = (array)($options);
        if($append and !isset($options[$selectedItems])) $options[$selectedItems] = $selectedItems;
        if(!is_array($options) or empty($options)) return false;

        /* The begin. */
        $id = $name;
        if(strpos($name, '[') !== false) $id = trim(str_replace(']', '', str_replace('[', '', $name)));
        $id = "id='{$id}'";
        if(strpos($attrib, 'id=') !== false) $id = '';

        $string = "<select name='$name' {$id} $attrib>\n";

        /* The options. */
        if(is_array($selectedItems)) $selectedItems = implode(',', $selectedItems);
        $selectedItems   = ",$selectedItems,";
        $convertedPinYin = class_exists('common') ? common::convert2Pinyin($options) : array();
        foreach($options as $key => $value)
        {
            $optionPinyin = zget($convertedPinYin, $value, '');
            $key      = str_replace('item', '', $key);
            $selected = strpos($selectedItems, ",$key,") !== false ? " selected='selected'" : '';
            $string  .= "<option value='$key'$selected data-keys='{$optionPinyin}'>$value</option>\n";
        }

        /* End. */
        return $string .= "</select>\n";
    }
}

/**
 * JS类。
 * JS class.
 * 
 * @package front
 */
class js extends baseJS
{
}

/**
 * css类。
 * css class.
 *
 * @package front
 */
class css extends baseCSS
{
}
