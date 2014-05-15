<?php
class TR_Content_Generator extends APP_Content_Generator {

	/**
	 * Class Constructor
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function __construct() {

		$config['taxonomies'] = array(
			TR_SERVICE_CATEGORY,
			TR_SERVICE_TAG,
		);

		$config['post_type'] = TR_SERVICE_PTYPE;
		$config['title'] = 'Taskerr Content Generator';

		parent::__construct( $config );

	}


	/**
	 * Adds post meta
	 *
	 * @since 1.0
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function extra_post_data( $post_id ) {

		$this->add_post_meta( $post_id );

	}


	/**
	 * Adds post meta (price, delivery, featured)
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function add_post_meta( $post_id ) {

		// set some default meta values
		$service_data = array(
			'price' => rand( 1, 1000 ),
			'delivery_time' => rand( 0, 30 ),
		);

		foreach ( $service_data as $meta_key => $meta_value ) {
			update_post_meta( $post_id, $meta_key, $meta_value, true );
		}

		// set listing as featured
		if ( ! empty( $_POST['home_featured'] ) && $this->add_or_not() ) {
			update_post_meta( $post_id, '_featured-home', '1' );
			update_post_meta( $post_id, '_featured-home_duration', rand( 10, 100 ) );
			update_post_meta( $post_id, '_featured-home_start_date', date( 'Y-m-d H:i:s', strtotime( '-' . rand( 1, 10 ) . ' days' ) ) );
		}

		if ( ! empty( $_POST['cat_featured'] ) && $this->add_or_not() ) {
			update_post_meta( $post_id, '_featured-cat', '1' );
			update_post_meta( $post_id, '_featured-cat_duration', rand( 10, 100 ) );
			update_post_meta( $post_id, '_featured-cat_start_date', date( 'Y-m-d H:i:s', strtotime( '-' . rand( 1, 10 ) . ' days' ) ) );
		}

	}


	/**
	 * Adds fields to mark listing as featured
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function extra_form_fields() {
	?>
		<tr valign="top">
			<th scope="row"><?php _e( 'Make home featured randomly?', $this->textdomain ); ?></th>
			<td><input type="checkbox" name="home_featured" value="1" checked="checked" /></td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e( 'Make category featured randomly?', $this->textdomain ); ?></th>
			<td><input type="checkbox" name="cat_featured" value="1" checked="checked" /></td>
		</tr>
	<?php
	}


}
