<?php
class VA_Content_Generator extends APP_Content_Generator {

	/**
	 * Class Constructor
	 *
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {

		$config['taxonomies'] = array(
			VA_LISTING_CATEGORY,
			VA_LISTING_TAG,
		);

		$config['post_type'] = VA_LISTING_PTYPE;
		$config['title'] = 'VA Content Generator';

		parent::__construct( $config );

	}


	/**
	 * Adds post meta
	 *
	 * @since 1.0
	 * @param int $post_id
	 * @return void
	 */
	public function extra_post_data( $post_id ) {

		$this->add_post_meta( $post_id );

	}


	/**
	 * Allows to add additional informations to image
	 *
	 * @since 1.0
	 * @param int $image_id
	 * @param int $post_id
	 * @return void
	 */
	public function extra_image_data( $image_id, $post_id ) {
		update_post_meta( $post_id, '_thumbnail_id', $image_id );
	}


	/**
	 * Adds post meta (contact, featured, geolocation)
	 *
	 * @since 1.0
	 * @return void
	 */
	public function add_post_meta( $post_id ) {
		$address = APP_Content_Generator_Data::get_address();

		foreach ( va_get_listing_contact_fields() as $field ) {
			if ( 'address' == $field ) {
				update_post_meta( $post_id, $field, $address['address'] );
			} elseif ( 'phone' == $field ) {
				update_post_meta( $post_id, $field, $address['phone'] );
			} else {
				update_post_meta( $post_id, $field, '' );
			}
		}

		appthemes_set_coordinates( $post_id, $address['lat'], $address['lng'] );

		if ( ! empty( $_POST['home_featured'] ) && $this->add_or_not() ) {
			update_post_meta( $post_id, 'featured-home', '1' );
			update_post_meta( $post_id, 'featured-home_duration', rand( 10, 100 ) );
			update_post_meta( $post_id, 'featured-home_start_date', date( 'Y-m-d H:i:s', strtotime( '-' . rand( 1, 10 ) . ' days' ) ) );
		}

		if ( ! empty( $_POST['cat_featured'] ) && $this->add_or_not() ) {
			update_post_meta( $post_id, 'featured-cat', '1' );
			update_post_meta( $post_id, 'featured-cat_duration', rand( 10, 100 ) );
			update_post_meta( $post_id, 'featured-cat_start_date', date( 'Y-m-d H:i:s', strtotime( '-' . rand( 1, 10 ) . ' days' ) ) );
		}

	}


	/**
	 * Adds fields to mark listing as featured
	 *
	 * @since 1.0
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
