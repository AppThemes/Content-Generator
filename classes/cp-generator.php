<?php
class CP_Content_Generator extends APP_Content_Generator {

	/**
	 * Class Constructor
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function __construct() {

		$config['taxonomies'] = array(
			APP_TAX_CAT,
			APP_TAX_TAG,
		);

		$config['post_type'] = APP_POST_TYPE;
		$config['title'] = 'CP Content Generator';

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
	 * Adds post meta (details, featured, geolocation)
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function add_post_meta( $post_id ) {
		$address = $this->get_random_address();

		if ( ! empty( $_POST['slider_featured'] ) && $this->add_or_not() ) {
			stick_post( $post_id );
		}

		// set some default meta values
		$ad_duration = rand( 10, 100 );
		$ad_expire_date = appthemes_mysql_date( current_time( 'mysql' ), $ad_duration );
		$advals['cp_sys_expire_date'] = $ad_expire_date;
		$advals['cp_sys_ad_duration'] = $ad_duration;
		$advals['cp_sys_ad_conf_id'] = uniqid( rand( 10, 1000 ), false );
		$advals['cp_sys_userIP'] = appthemes_get_ip();
		$advals['cp_daily_count'] = '0';
		$advals['cp_total_count'] = '0';
		$advals['cp_price'] = rand( 10, 1000 );
		$advals['cp_street'] = $address['street'];
		$advals['cp_city'] = $address['city'];
		$advals['cp_state'] = $address['state'];
		$advals['cp_country'] = $address['country'];
		$advals['cp_zipcode'] = $address['zipcode'];
		$advals['cp_sys_total_ad_cost'] = rand( 1, 50 );
	
		foreach ( $advals as $meta_key => $meta_value ) {
			update_post_meta( $post_id, $meta_key, $meta_value, true );
		}

		// set coordinates of new ad
		$category = wp_get_post_terms( $post_id, APP_TAX_CAT );
		$category_name = ( empty( $category ) ) ? '' : $category[0]->name;
		cp_update_geocode( $post_id, $category_name, $address['lat'], $address['lng'] );

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
			<th scope="row"><?php _e( 'Make slider featured randomly?', $this->textdomain ); ?></th>
			<td><input type="checkbox" name="slider_featured" value="1" checked="checked" /></td>
		</tr>
	<?php
	}


	/**
	 * Returns random address
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_random_address() {
		$data = APP_Content_Generator_Data::get_address();

		$parts = explode( ', ', $data['address'] );
		$data_street_city = explode( ' ', $parts[0] );
		$street_parts = array_slice( $data_street_city, 0, -1 );

		$address['lat'] = $data['lat'];
		$address['lng'] = $data['lng'];

		$address['street'] = implode( ' ', $street_parts );
		$address['city'] = end( $data_street_city );
		$address['zipcode'] = trim( preg_replace( '/[^0-9-]/i', '', $parts[1] ) );
		$address['state'] = trim( preg_replace( '/[^a-z ]/i', '', $parts[1] ) );
		$address['country'] = $this->get_random_country();

		return $address;
	}


	/**
	 * Returns random country
	 *
	 * @since 1.0
	 *
	 * @return string The country name
	 */
	public function get_random_country() {
		$countries = array(
			'United States',
			'United Kingdom',
			'Australia',
			'Austria',
			'Belgium',
			'Brazil',
			'Canada',
			'Cyprus',
			'Germany',
			'Poland',
		);

		return $countries[ rand( 0, count( $countries ) - 1 ) ]; 
	}


}
