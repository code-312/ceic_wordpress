<?php
/**
 * Customizer Control Image Buttons
 *
 * @since      4.3
 * @package    IS
 * @author     Ivory Search <admin@ivorysearch.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Radio Image control (modified radio).
 */
class IS_Control_Radio_Image extends WP_Customize_Control {

	/**
	 * The control type.
	 *
	 * @since 4.3
	 * 
	 * @access public
	 * @var string
	 */
	public $type = 'is-radio-image';

	/**
	 * Enqueue control related scripts/styles.
	 *
	 * @since 4.3
	 *
	 * @access public
	 */
	public function enqueue() {
		wp_enqueue_script( 'is-radio-image', IS_PLUGIN_URI . 'includes/customizer/controls/radio-image/radio-image.js', array( 'jquery', 'customize-base' ), IS_VERSION, true );
		wp_enqueue_style( 'is-radio-image', IS_PLUGIN_URI . 'includes/customizer/controls/radio-image/radio-image.css', null, IS_VERSION );
	}

	/**
	 * Refresh the parameters passed to the JavaScript via JSON.
	 *
	 * @since 4.3
	 *
	 * @see WP_Customize_Control::to_json()
	 */
	public function to_json() {
		parent::to_json();

		$this->json['default'] = $this->setting->default;
		if ( isset( $this->default ) ) {
			$this->json['default'] = $this->default;
		}
		$this->json['value'] = $this->value();

		foreach ( $this->choices as $key => $value ) {
			$this->json['choices'][ $key ]        = isset( $value['path'] ) ? esc_url( $value['path'] ) : '';
			$this->json['choices_titles'][ $key ] = $value['label'];
		}

		$this->json['link'] = $this->get_link();
		$this->json['id']   = $this->id;

		$this->json['inputAttrs'] = '';
		$this->json['labelStyle'] = '';
		foreach ( $this->input_attrs as $attr => $value ) {
			if ( 'style' !== $attr ) {
				$this->json['inputAttrs'] .= $attr . '="' . esc_attr( $value ) . '" ';
			} else {
				$this->json['labelStyle'] = 'style="' . esc_attr( $value ) . '" ';
			}
		}

	}

	/**
	 * An Underscore (JS) template for this control's content (but not its container).
	 *
	 * Class variables for this control class are available in the `data` JS object;
	 * export custom variables by overriding {@see WP_Customize_Control::to_json()}.
	 *
	 * @see WP_Customize_Control::print_template()
	 * 
	 * @since 4.3
	 *
	 * @access protected
	 */
	protected function content_template() {
		?>
		<label class="customizer-text">
			<# if ( data.label ) { #>
				<span class="customize-control-title">{{{ data.label }}}</span>
			<# } #>
			<# if ( data.description ) { #>
				<span class="description customize-control-description">{{{ data.description }}}</span>
			<# } #>
		</label>
		<div id="input_{{ data.id }}" class="image">
			<# for ( key in data.choices ) { #>
				<#
				var no_image_class = '';
				if( ! data.choices[ key ] ) {
					no_image_class = 'is-no-image';
				}
				#>
				<input {{{ data.inputAttrs }}} class="image-select {{{no_image_class}}}" type="radio" value="{{ key }}" name="_customize-radio-{{ data.id }}" id="{{ data.id }}{{ key }}" {{{ data.link }}}<# if ( data.value === key ) { #> checked="checked"<# } #>>
					<label for="{{ data.id }}{{ key }}" {{{ data.labelStyle }}} class="{{{no_image_class}}}">
						<# if( no_image_class ) { #>
							{{ data.choices_titles[ key ] }}
						<# } else { #>
							<img class="wp-ui-highlight" src="{{ data.choices[ key ] }}">
							<span class="image-clickable" title="{{ data.choices_titles[ key ] }}" ></span>
						<# } #>
					</label>
				</input>
			<# } #>
		</div>
		<?php
	}
}
