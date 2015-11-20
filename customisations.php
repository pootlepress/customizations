<?php
/**
 * Plugin Name: Customisations
 * Description: A very simple plugin to house custom css and functions.
 * Version: 	1.0.0
 * Author: 		pootlepress
 * Author URI: 	http://www.pootlepress.com/
 * @developer Shramee <shramee.srivastav@gmail.com>
 * @package Customisations
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Pootle_Customisations {

	public function __construct () {
		add_action( 'admin_menu', array( $this, 'menu' ), 999 );
		add_action( 'admin_init', array( $this, 'fields' ) );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
	}

	public function plugins_loaded() {
		$php = get_option( 'pootle_custo_php' );
		$code = str_replace( array( '<?php', '<?', '?>', ), '', $php['php'] );
		if (  ! is_admin() || ! empty( $php['admin'] ) ) {
			eval( $code );
		}
	}

	public function scripts() {
		wp_enqueue_style( 'customizations_css', plugin_dir_url( __FILE__ ) . '/data/main.css' );
		wp_enqueue_script( 'customizations_javascript', plugin_dir_url( __FILE__ ) . '/data/main.js' );
		if ( apply_filters( 'pootle_customizations_dev', false ) ) {
			wp_enqueue_style( 'customizations_mob_css', plugin_dir_url( __FILE__ ) . '/data/mobile.css' );
			wp_enqueue_style( 'customizations_desk_css', plugin_dir_url( __FILE__ ) . '/data/desktop.css' );
		}

	}

	public function menu() {
    add_theme_page(
        'Customizations',
        'Customizations',
        'edit_theme_options',
        'pootle_customizations',
        array( $this, 'render_page' )
    );
	}

	public function render_page() {
		?>
		<!-- Create a header in the default WordPress 'wrap' container -->
		<div class="wrap">

			<style>
				#editor{
					position: relative;
					width: 750px;
					height: 520px;
				}
				.large img.emoji {
					height: 1.6em!important;
					width: 1.6em!important;
					vertical-align: middle!important;
				}
			</style>

			<div id="icon-themes" class="icon32"></div>
			<h2>Customizations</h2>
			<?php settings_errors(); ?>

			<?php
			$active_tab = 'css';
			if( isset( $_GET[ 'tab' ] ) ) {
				$active_tab = $_GET[ 'tab' ];
			} // end if
			/** @var string Editor type */
			$editor_type = in_array( $active_tab, array( 'php', 'javascript' ) ) ? $active_tab : 'css'
			?>

			<h2 class="nav-tab-wrapper">
				<a href="?page=pootle_customizations&tab=css" class="nav-tab <?php echo $active_tab == 'css' ? 'nav-tab-active' : ''; ?>">CSS</a>
				<a href="?page=pootle_customizations&tab=javascript" class="nav-tab <?php echo $active_tab == 'javascript' ? 'nav-tab-active' : ''; ?>">JS</a>
				<?php if ( apply_filters( 'pootle_customizations_dev', false ) ) { ?>
					<a href="?page=pootle_customizations&tab=mob_css" class="nav-tab <?php echo $active_tab == 'mob_css' ? 'nav-tab-active' : ''; ?>">Mobile CSS</a>
					<a href="?page=pootle_customizations&tab=desk_css" class="nav-tab <?php echo $active_tab == 'desk_css' ? 'nav-tab-active' : ''; ?>">Desktop CSS</a>
				<?php } ?>
				<a href="?page=pootle_customizations&tab=php" class="nav-tab <?php echo $active_tab == 'php' ? 'nav-tab-active' : ''; ?>">Functions</a>
			</h2>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'pootle_custo_' . $active_tab );
				do_settings_sections( 'pootle_customizations_' . $active_tab );
				submit_button();
				?>
			</form>
			<script src="//cdnjs.cloudflare.com/ajax/libs/ace/1.2.2/ace.js"></script>
			<script src="//cdnjs.cloudflare.com/ajax/libs/ace/1.2.2/mode-<?php echo $editor_type ?>.js"></script>
			<script>
				(
					function ( $ ) {
						var editor = ace.edit( "editor" ),
							EditorMode = ace.require("ace/mode/<?php echo $editor_type ?>").Mode;
						editor.session.setMode( new EditorMode() );
						editor.on( 'change', function ( e ) {
							$('textarea.hidden').val( editor.getValue() );
						} );
					}
				)( jQuery );
			</script>
		</div><!-- /.wrap -->
		<?php
	}

	public function fields() {

		// First, we register a section. This is necessary since all future options must belong to a
		add_settings_section(
			'pootle_custo_css',
			__( 'Custom CSS Styles', 'sandbox' ),
			array( $this, 'render_section_css' ),
			'pootle_customizations_css'
		);

		add_settings_section(
			'pootle_custo_javascript',
			__( 'Custom Javascript', 'sandbox' ),
			array( $this, 'render_section_javascript' ),
			'pootle_customizations_javascript'
		);

		add_settings_section(
			'pootle_custo_mob_css',
			__( 'Custom Mobile CSS Styles', 'sandbox' ),
			array( $this, 'render_section_mob_css' ),
			'pootle_customizations_mob_css'
		);

		add_settings_section(
			'pootle_custo_desk_css',
			__( 'Custom Mobile CSS Styles', 'sandbox' ),
			array( $this, 'render_section_desk_css' ),
			'pootle_customizations_desk_css'
		);

		add_settings_section(
			'pootle_custo_php',
			__( 'Custom PHP code', 'sandbox' ),
			array( $this, 'render_section_php' ),
			'pootle_customizations_php'
		);

		register_setting(
			'pootle_custo_css',
			'pootle_custo_css',
			array( $this, 'make_file_css' )
		);

		register_setting(
			'pootle_custo_javascript',
			'pootle_custo_javascript',
			array( $this, 'make_file_javascript' )
		);

		register_setting(
			'pootle_custo_mob_css',
			'pootle_custo_mob_css',
			array( $this, 'make_file_mob_css' )
		);

		register_setting(
			'pootle_custo_desk_css',
			'pootle_custo_desk_css',
			array( $this, 'make_file_desk_css' )
		);

		register_setting(
			'pootle_custo_php',
			'pootle_custo_php',
			array( $this, 'make_file_php' )
		);
	}

	public function render_section_css() {
		$value = get_option( 'pootle_custo_css' );
		?>
		<p>Head over to <a href="http://www.pootlepress.com/customizations">http://www.pootlepress.com/customizations</a> to grab some awesome media query snippets <span class="large"><?php echo convert_smilies( ";)" ); ?></span>.</p>
		<textarea class="hidden" name="pootle_custo_css"><?php echo $value; ?></textarea>
		<div id="editor"><?php echo $value ?></div>
		<?php
	}

	public function render_section_javascript() {
		$value = get_option( 'pootle_custo_javascript' );
		?>
		<textarea class="hidden" name="pootle_custo_javascript"><?php echo $value; ?></textarea>
		<div id="editor"><?php echo $value ?></div>
		<?php
	}

	public function render_section_mob_css() {
		$value = get_option( 'pootle_custo_mob_css' );
		?>
		<p>CSS you put here will be applied to your website only on iPhones and other small mobile devices.</p>
		<textarea class="hidden" name="pootle_custo_mob_css"><?php echo $value; ?></textarea>
		<div id="editor"><?php echo $value ?></div>
		<?php
	}

	public function render_section_desk_css() {
		$value = get_option( 'pootle_custo_desk_css' );
		?>
		<p>CSS you put here will be applied to your website on iPads and other larger mobile devices and desktops.</p>
		<textarea class="hidden" name="pootle_custo_desk_css"><?php echo $value; ?></textarea>
		<div id="editor"><?php echo $value ?></div>
		<?php
	}

	public function render_section_php() {
		$value = wp_parse_args( get_option( 'pootle_custo_php', array() ), array(
			'php' => "<?php\n\n\n?>",
			'admin' => false,
		) );
		?>
		<textarea class="hidden" name="pootle_custo_php[php]"><?php echo $value['php']; ?></textarea>
		<div id="editor"><?php esc_html_e( $value['php'] ) ?></div>
		<br><br>
		<b><span class="attention">Warning:</span> If you are a young padawan in php, don't enable the setting below.</b><br>
		<b>Jedi's in php consider checking the code on frontend before applying on the admin end.</b><br>
		<label>
			<input type="checkbox" value="1" name="pootle_custo_php[admin]" <?php checked( $value['admin'], 1 ) ?>>
			Apply php on admin end
		</label>
		<?php
	}

	public function theme_customisations_template( $template ) {

		return $template;
	}

	/**
	 * Create file for css
	 * @param string $val Current value of the field
	 * @return string Field data
	 * Since 1.0.0
	 */
	public function make_file_css ( $val ) {
		return $this->make_file( 'main.css', $val );
	}

	/**
	 * Create file for javascript
	 * @param string $val Current value of the field
	 * @return string Field data
	 * Since 1.0.0
	 */
	public function make_file_javascript ( $val ) {
		return $this->make_file( 'main.js', $val );
	}

	/**
	 * Create file for mob_css
	 * @param string $val Current value of the field
	 * @return string Field data
	 * Since 1.0.0
	 */
	public function make_file_mob_css ( $val ) {
		return $this->make_file( 'mobile.css', "@media only screen and (max-width:767px) {\n$val\n}" );
	}

	/**
	 * Create file for desk_css
	 * @param string $val Current value of the field
	 * @return string Field data
	 * Since 1.0.0
	 */
	public function make_file_desk_css ( $val ) {
		return $this->make_file( 'desktop.css', "@media only screen and (min-width:768px) {\n$val\n}" );
	}

	/**
	 * Create file for php
	 * @param string $val Current value of the field
	 * @return array Field data
	 * Since 1.0.0
	 */
	public function make_file_php ( $val ) {
		if( $this->make_file( 'main.php', $val['php'] ) ){
			return $val;
		}
	}

	/**
	 * Creates the file
	 * @param string $file_name
	 * @param string $data
	 * @return true
	 */
	public function make_file( $file_name, $data ) {
		$file = fopen( dirname( __FILE__ ) . '/data/' . $file_name, "w" ) or die( "Unable to access/create file" );
		fwrite( $file, $data );
		fclose( $file );
		return $data;
	}
} // End Class

new Pootle_Customisations();