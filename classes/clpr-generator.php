<?php
class CLPR_Content_Generator extends APP_Content_Generator {

	/**
	 * Class Constructor
	 *
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {

		$config['taxonomies'] = array(
			APP_TAX_CAT,
			APP_TAX_STORE,
			APP_TAX_TYPE,
			APP_TAX_TAG,
		);

		$config['post_type'] = APP_POST_TYPE;
		$config['title'] = 'CLPR Content Generator';

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

		$coupon_type = wp_get_object_terms( $post_id, APP_TAX_TYPE );
		if ( is_wp_error( $coupon_type ) || empty( $coupon_type ) )
			wp_set_object_terms( $post_id, 'promotion', APP_TAX_TYPE, false );

		$this->add_post_meta( $post_id );
	}


	/**
	 * Adds post meta (details, featured)
	 *
	 * @since 1.0
	 * @return void
	 */
	public function add_post_meta( $post_id ) {
		global $app_version;

		$coupon_type = wp_get_object_terms( $post_id, APP_TAX_TYPE );
		$coupon_type = $coupon_type[0]->slug;

		// expire date
		$duration = rand( 10, 100 );
		$expire_date_format = ( version_compare( $app_version, '1.4', '>' ) ) ? 'Y-m-d' : 'm-d-Y';
		$expire_date = appthemes_mysql_date( current_time( 'mysql' ), $duration );
		$expire_date = date( $expire_date_format, strtotime( $expire_date ) );

		$meta_fields = array(
			'clpr_id' => uniqid( rand( 10, 1000 ), false ),
			'clpr_sys_userIP' => appthemes_get_ip(),
			'clpr_daily_count' => '0',
			'clpr_total_count' => '0',
			'clpr_coupon_aff_clicks' => '0',
			'clpr_votes_up' => '0',
			'clpr_votes_down' => '0',
			'clpr_votes_percent' => '100',
			'clpr_coupon_code' => ( $coupon_type == 'coupon-code' ) ? $this->get_random_coupon_code() : '',
			'clpr_print_url' => '',
			'clpr_expire_date' => $expire_date,
			'clpr_featured' => ( ! empty( $_POST['slider_featured'] ) && $this->add_or_not() ) ? '1' : '0',
			'clpr_print_imageid' => ( $coupon_type == 'printable-coupon' ) ? $this->add_printable_coupon( $post_id ) : '',
			'clpr_coupon_aff_url' => $this->get_random_affiliate_url(),
		);

		foreach ( $meta_fields as $meta_key => $meta_value )
			update_post_meta( $post_id, $meta_key, $meta_value, true );

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
			<th scope="row"><?php _e( 'Make slider featured randomly?', $this->textdomain ); ?></th>
			<td><input type="checkbox" name="slider_featured" value="1" checked="checked" /></td>
		</tr>
	<?php
	}


	/**
	 * Returns random coupon code
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_random_coupon_code() {
		$rand_name = uniqid( rand( 10, 1000 ), false );
		return substr( $rand_name, 0, rand( 5, 10 ) );
	}


	/**
	 * Returns random affiliate url
	 *
	 * @since 1.0
	 * @return string
	 */
	public function get_random_affiliate_url() {
		$websites = array(
			'http://www.appthemes.com/',
			'http://marketplace.appthemes.com/',
			'http://www.amazon.com/',
			'http://www.amazon.co.uk/',
			'http://www.amazon.de/',
			'http://www.ebay.com/',
			'http://www.ebay.co.uk/',
			'http://www.ebay.de/',
			'http://www.etsy.com/',
			'http://www.alibaba.com/',
			'http://www.rakuten.com/',
		);

		$aff_params = array(
			'affiliate',
			'aff_id',
			'ad',
			'ad_id',
			'ref',
			'ref_id',
			'partner',
			'partner_id',
			'campaign',
			'campaign_id',
		);

		$aff_param = $aff_params[ rand( 0, count( $aff_params ) - 1 ) ];
		$website = $websites[ rand( 0, count( $websites ) - 1 ) ];

		return add_query_arg( $aff_param, rand( 10, 1000 ), $website );
	}


	/**
	 * Adds printable coupon
	 *
	 * @since 1.0
	 * @return int|bool
	 */
	public function add_printable_coupon( $post_id ) {
		$image = APP_Content_Generator_Data::get_image();
		if ( ! $image )
			return false;

		$image_id = $this->import_attachment( $post_id, $image );
		if ( ! $image_id )
			return false;

		wp_set_object_terms( $image_id, 'printable-coupon', APP_TAX_IMAGE, false );

		return $image_id;
	}


}
