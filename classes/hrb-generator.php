<?php
class HRB_Content_Generator extends APP_Content_Generator {

	/**
	 * Class Constructor
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function __construct() {

		$config['taxonomies'] = array(
			HRB_PROJECTS_CATEGORY,
			HRB_PROJECTS_TAG,
			HRB_PROJECTS_SKILLS,
		);

		$config['post_type'] = HRB_PROJECTS_PTYPE;
		$config['title'] = 'HireBee Content Generator';

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
	 * Adds post meta (budget, duration, featured)
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function add_post_meta( $post_id ) {
		$budget_type = array( 'fixed', 'hourly' );
		$budget_currency = array_keys( APP_Currencies::get_currencies() );

		shuffle( $budget_type );
		shuffle( $budget_currency );

		// set some default meta values
		$data = array(
			'_hrb_duration' => rand( 1, 90 ),
			'_hrb_budget_type' => $budget_type[0],
			'_hrb_budget_currency' => $budget_currency[0],
			'_hrb_budget_price' => rand( 1, 1000 ),
			'_hrb_hourly_min_hours' => rand( 1, 40 ),
		);

		foreach ( $data as $meta_key => $meta_value ) {
			update_post_meta( $post_id, $meta_key, $meta_value, true );
		}

		// set listing as featured
		if ( ! empty( $_POST['home_featured'] ) && $this->add_or_not() ) {
			update_post_meta( $post_id, '_hrb_featured-home', '1' );
			update_post_meta( $post_id, '_hrb_featured-home_duration', rand( 10, 100 ) );
			update_post_meta( $post_id, '_hrb_featured-home_start_date', date( 'Y-m-d H:i:s', strtotime( '-' . rand( 1, 10 ) . ' days' ) ) );
		}

		if ( ! empty( $_POST['cat_featured'] ) && $this->add_or_not() ) {
			update_post_meta( $post_id, '_hrb_featured-cat', '1' );
			update_post_meta( $post_id, '_hrb_featured-cat_duration', rand( 10, 100 ) );
			update_post_meta( $post_id, '_hrb_featured-cat_start_date', date( 'Y-m-d H:i:s', strtotime( '-' . rand( 1, 10 ) . ' days' ) ) );
		}

		if ( ! empty( $_POST['urgent_mark'] ) && $this->add_or_not() ) {
			update_post_meta( $post_id, '_hrb_urgent', '1' );
			update_post_meta( $post_id, '_hrb_urgent_duration', rand( 10, 100 ) );
			update_post_meta( $post_id, '_hrb_urgent_start_date', date( 'Y-m-d H:i:s', strtotime( '-' . rand( 1, 10 ) . ' days' ) ) );
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
		<tr valign="top">
			<th scope="row"><?php _e( 'Mark project as urgent randomly?', $this->textdomain ); ?></th>
			<td><input type="checkbox" name="urgent_mark" value="1" checked="checked" /></td>
		</tr>
	<?php
	}


}
