<?php
class APP_Content_Generator {


	protected $config;

	protected $post_type;
	protected $taxonomies;
	protected $textdomain;


	/**
	 * Class Constructor
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function __construct( $config = array() ) {

		$defaults = array(
			'post_type' => 'post',
			'taxonomies' => array( 'post_tag', 'category' ),
			'textdomain' => 'app-content-generator',
			'title' => 'Content Generator',
		);

		$this->config = wp_parse_args( $config, $defaults );

		$this->post_type = $this->config['post_type'];
		$this->taxonomies = $this->config['taxonomies'];
		$this->textdomain = $this->config['textdomain'];

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}


	/**
	 * Adds plugin admin menu
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	function add_admin_menu() {
		add_management_page( $this->config['title'], $this->config['title'], 'manage_options', 'content_generator', array( $this, 'display_page' ) );
	}


	/**
	 * Loads plugin localization
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	function load_textdomain() {
		load_plugin_textdomain( $this->textdomain, false, basename( dirname( __FILE__ ) ) . '/languages' );
	}


	/**
	 * Generates settings page for plugin
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	function display_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', $this->textdomain ) );
		}

		if ( ! empty( $_POST['generate_random_content'] ) ) {
			$this->generate_content();
			echo '<div class="updated fade"><p>' . __( 'Items generated!', $this->textdomain ) . '</p></div>';
		}

		?>
		<style>
			ul.children {
				margin-left: 15px;
			}
			div.refine_wrap {
				max-height: 200px;
				overflow: auto;
				width: 300px;
			}
		</style>
		<div class="wrap"><?php screen_icon(); ?>
			<h2><?php _e( 'Content Generator', $this->textdomain ); ?></h2>
			<div>
				<form method="post" action="">
					<p><?php _e( 'Please specify how many items to create and what to include:', $this->textdomain ); ?></p>
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e( 'Create how many items?', $this->textdomain ); ?></th>
							<td><input type="text" name="items_count" value="10" /></td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Which authors to include?', $this->textdomain ); ?></th>
							<td><?php echo $this->get_authors_dropdown(); ?></td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Attach images randomly?', $this->textdomain ); ?></th>
							<td><input type="checkbox" name="images" value="1" /></td>
						</tr>
						<?php
							foreach ( $this->taxonomies as $taxonomy ) {
								if ( ! taxonomy_exists( $taxonomy ) ) {
									continue;
								}

								$tax_object = get_taxonomy( $taxonomy );
								if ( substr( $taxonomy, -4 ) != '_tag' ) {
									$title = sprintf( __( 'Which %s to include?', $this->textdomain ), $tax_object->labels->name );
									$field = '<div class="refine_wrap"><ul>' . $this->get_tax_checklist( $taxonomy ) . '</ul></div>';
								} else {
									$title = sprintf( __( 'Comma seperated list of %s to randomly use.', $this->textdomain ), $tax_object->labels->name );
									$field = '<input type="text" name="tax_input[' . $taxonomy . ']" value="' . $this->get_tax_commalist( $taxonomy ) . '" />';
								}
								echo '<tr valign="top">';
								echo '<th scope="row">' . $title . '</th>';
								echo '<td>' . $field . '</td>';
								echo '</tr>';
							}
						?>

						<?php $this->extra_form_fields(); ?>

					</table>
					<p class="submit"><input type="submit" name="generate_random_content" class="button-primary" value="<?php _e( 'Generate', $this->textdomain ); ?>" /></p>
				</form>
			</div>
		</div>
<?php
	}


	/**
	 * Creates authors dropdown
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	public function get_authors_dropdown() {
		$args = array( 'multi' => true );

		ob_start();
		wp_dropdown_users( $args );
		$dropdown = ob_get_clean();

		$dropdown = str_replace( "<select name='user'", '<select multiple="multiple" name="authors[]"', $dropdown );

		return $dropdown;
	}


	/**
	 * Creates checklist for taxonomies
	 *
	 * @since 1.0
	 * @param string $taxonomy
	 *
	 * @return string
	 */
	public function get_tax_checklist( $taxonomy ) {
		require_once ABSPATH . '/wp-admin/includes/template.php';

		ob_start();
		wp_terms_checklist( 0, array(
			'taxonomy' => $taxonomy,
			'checked_ontop' => false,
		) );
		$checklist = ob_get_clean();
		$checklist = str_replace( 'post_category[]', 'tax_input[' . $taxonomy . '][]', $checklist );

		return $checklist;
	}


	/**
	 * Creates comma separated list of taxonomy terms, limits to 5
	 *
	 * @since 1.0
	 * @param string $taxonomy
	 *
	 * @return string
	 */
	public function get_tax_commalist( $taxonomy ) {
		$terms = get_terms( $taxonomy, array( 'fields' => 'names', 'hide_empty' => false, 'number' => 5 ) );

		return implode( ', ', $terms );
	}


	/**
	 * Returns randomly true or false
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function add_or_not() {
		$rand = rand( 0, 9 );
		return ( $rand > 4 );
	}


	/**
	 * Returns randomly true or false
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public function generate_content() {
		$number = isset( $_POST['items_count'] ) ? (int) $_POST['items_count'] : 10;

		for ( $i = 1; $i <= $number; $i++ ) {
			$post_id = $this->create_post();
			if ( ! $post_id ) {
				continue;
			}

			if ( ! empty( $_POST['images'] ) ) {
				$image = APP_Content_Generator_Data::get_image();
				$image_id = ( $image && $this->add_or_not() ) ? $this->import_attachment( $post_id, $image ) : false;
				if ( $image_id ) {
					$this->extra_image_data( $image_id, $post_id );
				}
			}

			$this->assign_taxonomies( $post_id );

			$this->extra_post_data( $post_id );
		}
	}


	/**
	 * Attach image to post
	 *
	 * @since 1.0
	 * @param int $post_id
	 * @param string $file
	 *
	 * @return bool|int
	 */
	public function import_attachment( $post_id, $file ) {
		$file_info = wp_check_filetype( $file );

		$rand_name = uniqid( rand( 10, 1000 ), false );
		$file_name = $rand_name . '.' . $file_info['ext'];

		$upload_dir = wp_upload_dir();
		$new_path = $upload_dir['path'] . '/' . $file_name;

		if ( file_exists( $new_path ) ) {
			return false;
		}

		if ( ! copy( $file, $new_path ) ) {
			return false;
		}

		$post = array(
			'post_title' => $rand_name,
			'post_content' => '',
			'post_status' => 'publish',
			'post_mime_type' => $file_info['type'],
		);

		$attachment_id = wp_insert_attachment( $post, $new_path, $post_id );
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $new_path ) );

		return $attachment_id;
	}


	/**
	 * Creates post
	 *
	 * @since 1.0
	 *
	 * @return int|bool
	 */
	public function create_post() {
		if ( isset( $_POST['authors'] ) && is_array( $_POST['authors'] ) ) {
			$author = (int) $_POST['authors'][ rand( 0, count( $_POST['authors'] ) - 1 ) ];
		} else {
			$author = 1;
		}

		$post = array(
			'post_title' => APP_Content_Generator_Data::get_title(),
			'post_content' => APP_Content_Generator_Data::get_description(),
			'post_status' => 'publish',
			'post_author' => $author,
			'post_type' => $this->post_type,
		);

		return wp_insert_post( $post );
	}


	/**
	 * Assigns taxonomies
	 *
	 * @since 1.0
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function assign_taxonomies( $post_id ) {
		if ( empty( $_POST['tax_input'] ) || ! is_array( $_POST['tax_input'] ) ) {
			return;
		}

		foreach ( $_POST['tax_input'] as $taxonomy => $terms ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}

			if ( ! is_array( $terms ) ) {
				$terms = explode( ',', $terms );
				$terms = array_map( 'trim', $terms );
			} else {
				$terms = array_map( 'intval', $terms );
			}

			if ( substr( $taxonomy, -4 ) == '_tag' ) {
				shuffle( $terms );
				$terms = array_slice( $terms, 0, rand( 0, count( $terms ) - 1 ) );
			} else {
				$terms = $terms[ rand( 0, count( $terms ) - 1 ) ];
			}

			wp_set_object_terms( $post_id, $terms, $taxonomy );
		}
	}


	/**
	 * Allows to add additional informations to post
	 *
	 * @since 1.0
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function extra_post_data( $post_id ) {

	}


	/**
	 * Allows to add additional informations to image
	 *
	 * @since 1.0
	 * @param int $image_id
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function extra_image_data( $image_id, $post_id ) {

	}


	/**
	 * Allows to add new form fields
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function extra_form_fields() {

	}


}
