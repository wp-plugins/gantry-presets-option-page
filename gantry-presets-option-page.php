<?php
/*
Plugin Name: Gantry Presets Option Page
Description: Enable your Gantry-powered theme users or client to choose their prefered 'Preset'.
Version: 0.1
Author: Hassan Derakhshandeh

		* 	Copyright (C) 2011  Hassan Derakhshandeh
		*	http://tween.ir/
		*	hassan.derakhshandeh@gmail.com

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation; either version 2 of the License, or
		(at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Gantry_Presets_Option_Page {

	private $textdomain;

	function Gantry_Presets_Option_Page() {
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
		register_activation_hook( __FILE__, array( &$this, 'check_for_gantry' ) );
	}

	function admin_menu() {
		$page = add_theme_page(
			__( 'Skin', $this->textdomain ),   // Name of page
			__( 'Skin', $this->textdomain ),   // Label in menu
			$this->required_capability(),  	   // Capability required
			'skin',                         // Menu slug, used to uniquely identify the page
			array( &$this, 'options_page' ) // Function that renders the options page
		);
		add_action( "load-{$page}", array( &$this, 'save_options' ) );
		add_action( "admin_head-{$page}", array( &$this, 'admin_css' ) );
		add_action( "admin_head-{$page}", array( &$this, 'admin_scripts' ) );
	}

	function admin_css() { ?>
		<style>
		.presets { margin-top: 20px; padding-left: 15px; }
		.presets .block { width: 180px; height: 100px; background: url('<?php echo plugins_url() ?>/gantry/admin/widgets/preset/images/preset-bg.png') no-repeat center center; float: left; margin: 7px 0px; margin-right: 15px; text-align: center; position: relative; cursor: pointer; }
		.presets .block img { position: absolute; top: 0; left: 0; }
		.presets .block span { color: #333; text-shadow: 1px 1px white; top: 73px; position: relative; font-size: 115%; line-height: 27px; display: block; height: 27px; margin: 0 9px; background: #fff; background: rgba(255,255,255,.8) }
		.block.active { outline: 5px solid rgb(0,200,255); }
		.rtl .presets { padding-left: 0; padding-right: 15px; }
		.rtl .presets .block { float: right; margin-right: 0; margin-left: 15px; }
		</style>
	<?php }

	function admin_scripts() { ?>
		<script>
		jQuery(function($){
			$('.block').live('click', function(){
				$('div.block').removeClass('active');
				$(this).addClass('active');
				$('#preset').val($(this).data('key'));
			});
		});
		</script>
	<?php }

	function options_page() {
		global $gantry;

		$preset = get_option( $gantry->templateName . '-preset' );
	?>
		<div class="wrap">
			<?php screen_icon() ?>
			<h2><?php _e( 'Select skin', $this->textdomain ) ?></h2>
			<form action="" method="POST">
				<input type="hidden" name="save_preset_options" value="1" />
				<input type="hidden" name="preset" id="preset" value="<?php echo $preset ?>" />
				<?php
				$presets = $this->_get_presets();
				echo '<div class="presets">';
				foreach( $presets as $key => $value ) {
					echo '<div class="block';
					if( $preset == $key ) echo ' active';
					echo '" data-key="'. $key .'">';
					if( file_exists( TEMPLATEPATH . '/admin/presets/' . $key . '.png' ) ) {
						echo '<img src="'. get_template_directory_uri() . '/admin/presets/' . $key . '.png' . '" alt="'. $value['name'] . '" />';
					}
					echo "<span>{$value[name]}</span>";
					echo '</div>';
				}
				echo '</div><!-- .presets -->';
				echo '<div class="clear"></div>';

				submit_button();
				?>
			</form>
		</div><!-- .wrap -->
	<?php }

	function save_options() {
		global $gantry;

		if( isset( $_POST ) && $_POST['save_preset_options'] == 1 ) {
			$currentPreset = $_POST['preset'];
			$theme_options = get_option( $gantry->templateName . '-template-options' );
			$presets = $this->_get_presets();
			unset($presets[$currentPreset]['name']);
			update_option( $gantry->templateName . '-template-options', array_merge( $theme_options, $presets[$currentPreset] ) );
			update_option( $gantry->templateName . '-preset', $currentPreset );
		}
	}

	function required_capability() {
		return apply_filters( 'gantry_presets_option_page_cap', 'edit_theme_options' );
	}

	/**
	 * Check if Gantry is installed, otherwise deactivate the plugin
	 *
	 * @since 0.1
	 */
	function check_for_gantry() {
		if( ! defined( 'GANTRY_VERSION' ) ) {
			deactivate_plugins( basename( __FILE__ ) );
		}
	}

	/**
	 * Get a list of Gantry Presets
	 * This function should not be called before init hook.
	 *
	 * @since 0.1
	 * @return array all presets
	 */
	function _get_presets() {
		$ini = new GantryINI();
		$custom_presets = $ini->read( TEMPLATEPATH . '/custom/presets.ini' );

		require( TEMPLATEPATH . '/gantry.config.php' );
		$presets = $gantry_presets['presets'];
		if( ! empty( $custom_presets['presets'] ) )
			$presets = array_merge( $gantry_presets['presets'], $custom_presets['presets'] );
		return $presets;
	}
}
new Gantry_Presets_Option_Page();