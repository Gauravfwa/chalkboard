<?php
/**
 * The Google Feed Template class.
 *
 * @link       https://rextheme.com
 * @since      1.0.0
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/
 */

/**
 *
 * Defines the attributes and template for google feed.
 *
 * @package    Rex_Product_Feed
 * @subpackage Rex_Product_Feed/admin/feed-templates/Rex_Feed_Template_Google
 * @author     RexTheme <info@rextheme.com>
 */
abstract class Rex_Feed_Abstract_Template {

    /**
     * The Feed Attributes.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Feed_Abstract_Template    attributes    Feed attributes.
     */
    protected $attributes;

    /**
     * WooCommerce Product Meta Keys.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Feed_Abstract_Template    attributes    Feed attributes.
     */
    protected $product_meta_keys;

    /**
     * The Feed Template Mappings Attributes and associated value and other constraints.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Feed_Abstract_Template    template_mappings    Feed attributes mapping for template genaration.
     */
    protected $template_mappings;

    /**
     * Data Sanitization options
     *
     * @since    1.0.0
     * @access   protected
     * @var      Rex_Feed_Abstract_Template    template_mappings    Feed attributes mapping for template genaration.
     */
    protected $sanitization_options;

    /**
     * Set the plugin atts and mapping.
     *
     * @since    1.0.0
     * @param bool $feed_rules
     */
    public function __construct( $feed_rules = false ){
        $this->init_atts();
        $this->init_template_mappings( $feed_rules );
        $this->init_sanitization_options();
    }

    /**
     * Return the template_mappings
     *
     * @since    1.0.0
     */
    public function get_template_mappings(){
        return $this->template_mappings;
    }


    /**
     * @desc Retrieve markups for product dropdown
     *
     * @since 1.0.0
     * @param $selected
     * @return bool|string|string[]
     */
    public function print_product_attributes( $selected = '' ) {
        $product_attribute_dropdown = $this->get_feed_cached_dropdown( 'product_attributes_dropdown', $selected );
        if ( false === $product_attribute_dropdown ) {
            $product_attributes = Rex_Feed_Attributes::get_attributes();
            return $this->make_cache_dropdown( 'product_attributes_dropdown', $product_attributes , $selected );
        }
        return $product_attribute_dropdown;
    }


    /**
     *
     * @param $key
     * @param string $selected
     * @return bool|string|string[]
     */
    private function get_feed_cached_dropdown( $key, $selected = '' ) {
        $product_attribute_dropdown = wpfm_get_cached_data( $key );
        if ( $selected && strpos( $product_attribute_dropdown, "value='" . esc_attr($selected) . "'" ) !== false ) {
            $product_attribute_dropdown = str_replace( "value='" . esc_attr($selected) . "'", 'value="' . esc_attr($selected) . '"' . ' selected', $product_attribute_dropdown );
        }
        return empty( $product_attribute_dropdown ) ? false : $product_attribute_dropdown;
    }


    /**
     * make cached dropdown list
     * for future use
     *
     * @param $key
     * @param $items
     * @param string $selected
     * @return string|string[]
     */
    private function make_cache_dropdown( $key, $items, $selected = '' ) {
        $drop_down = '';
        $i = 1;

        foreach ($items as $groupLabel => $groups) {
            if ( !empty($groupLabel)) {
                $drop_down .= "<optgroup label='" .esc_attr( $groupLabel ). "' data-i='".esc_attr($i)."'>";
            }
            foreach ($groups as $k => $it) {
                $drop_down .= "<option value='".esc_attr($k)."'>".esc_html($it)."</option>";
            }

            if ( !empty($groupLabel)) {
                $drop_down .= "</optgroup>";
            }
            $i = $i + 1;
        }
        wpfm_set_cached_data( $key, $drop_down );
        if ( $selected && strpos( $drop_down, "value='" . esc_attr($selected) . "'" ) !== false ) {
            $drop_down = str_replace( "value='" . esc_attr($selected) . "'", 'value="' . esc_attr($selected) . '"' . ' selected', $drop_down );
        }
        return $drop_down;
    }


    /**
     * Print attributes as select dropdown.
     *
     * @since    1.0.0
     * @param $key
     * @param $name
     * @param string $selected
     */
    public function print_select_dropdown( $key, $name, $selected = '', $class = '', $multiple = '', $array = '' ){

        if ( $name === 'attr' ) {
            $items = $this->attributes;
        }elseif ( $name === 'meta_key' ) {
            $items = $this->product_meta_keys;
        }elseif ( $name === 'escape' ) {
            $items = $this->sanitization_options;
        }else{
            return;
        }

        echo '<select class="' .esc_attr( $class ). '" name="fc['.esc_attr( $key ).'][' . esc_attr( $name ) . ']' .esc_attr( $array ). '" ' . esc_attr( $multiple ) . '>';
        echo "<option value='-1' disabled>Please Select</option>";
        $i = 1;
        foreach ($items as $groupLabel => $group) {
            if ( !empty($groupLabel)) {
                echo "<optgroup label='".esc_html( $groupLabel )."' data-i='".esc_attr( $i )."'>";
            }

            foreach ($group as $key => $item) {
                if ( ( is_array( $selected ) && in_array( $key, $selected ) ) || ( $selected === $key ) ) {
                    echo "<option value='".esc_attr($key)."' selected='selected'>".esc_attr($item)."</option>";
                }
                else{
                    echo "<option value='".esc_attr($key)."'>".esc_attr($item)."</option>";
                }
            }

            if ( !empty($groupLabel)) {
                echo "</optgroup>";
            }
            $i = $i + 1;
        }

        echo "</select>";
    }


    /**
     * Print attributes Type.
     *
     * @since    1.0.0
     * @param $key
     * @param string $select
     */
    public function print_attr_type( $key, $select = '', $class = '' ){
        $options = apply_filters('wpfm_pro_feed_attribute_type_render', array( 'meta' => 'Attribute', 'static' => 'Static'));
        echo "<select class='type-dropdown disable-custom-dropdown {$class}' name='fc[".esc_attr($key)."][type]' readonly='true'>";
        echo "<option value=''>Please Select</option>";
        foreach ($options as $key => $option) {
            $selected = $select === $key ? "selected='selected'" : "";
            echo "<option value='".esc_attr($key)."' ".esc_html($selected).">".esc_html($option)."</option>";
        }
        echo "</select>";
    }

    /**
     * Print Prefix input.
     *
     * @since    1.0.0
     * @param $key
     * @param string $name
     * @param string $val
     */
    public function print_input( $key, $name = '', $val = '', $class = '', $readonly = 'readonly' ){
        echo '<input type="text" class="'. esc_attr( $class ) .'" name="fc['.esc_attr($key).'][' . esc_attr( $name ) . ']" value="' . esc_attr( $val ) . '" ' . $readonly . '>';
    }

    /**
     * Initialize Product Meta Attributes
     *
     * @since    1.0.0
     */
    protected function init_product_meta_keys(){
        $this->product_meta_keys  = Rex_Feed_Attributes::get_attributes();
    }

    /**
     * Initialize Sanitization Options
     *
     * @since    1.0.0
     */
    protected function init_sanitization_options(){
        $this->sanitization_options = array(
            '' => array(
                'default'                      => 'Default',
                'strip_tags'                   => 'Strip Tags',
                'utf_8_encode'                 => 'UTF-8 Encode',
                'htmlentities'                 => 'htmlentities',
                'integer'                      => 'Integer',
                'price'                        => 'Price',
                'remove_space'                 => 'Remove Space',
                'remove_tab'                   => 'Remove Tab',
                'first_word_uppercase'         => 'First Word Uppercase',
                'each_word_uppercase'          => 'Each Word Uppercase',
                'remove_shortcodes'            => 'Remove ShortCodes',
                'remove_shortcodes_and_tags'   => 'Remove ShortCodes and Strip Tags',
                'remove_special character'     => 'Remove Special Character',
                'cdata'                        => 'CDATA',
                'cdata_without_space'          => 'CDATA without space',
                'remove_underscore'            => 'Remove underscore',
                'decode_url'                   => 'Decode url',
                'remove_decimal'               => 'Remove decimal points (Marktplaats only)',
                'add_two_decimal'              => 'Two decimal points',
                'comma_decimal'                => 'Decimal Separator - Comma (,)',
                'remove_hyphen'                => 'Remove hyphen',
                'remove_hyphen_space'          => 'Remove hyphen(space)',
                'replace_space_with_hyphen'    => 'Replace space ( ) with hyphen (-)',
                'replace_comma_with_backslash' => 'Replace comma (,) with backslash (/)',
                'replace_decimal_with_hyphen'  => 'Replace decimal point (.) with hyphen (-)',
            )
        );
    }


    /**
     * Initialize Template Mappings with Attributes from feed post_meta.
     *
     * @since    1.0.0
     * @param string $feed_rules The Rules Of Feeds
     */
    protected function init_template_mappings( $feed_rules ){

        if ( !empty($feed_rules) ) {
            $this->template_mappings = $feed_rules;
        }else{
            $this->init_default_template_mappings();
        }
    }


    /**
     * Initialize Attributes
     *
     * @since    1.0.0
     */
    abstract protected function init_atts();

    /**
     * Initialize Default Template Mappings with Attributes.
     *
     * @since    1.0.0
     */
    abstract protected function init_default_template_mappings();
}