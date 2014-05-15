<?php
class RAVE_Content_Generator extends APP_Content_Generator {

	/**
	 * Class Constructor
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function __construct() {

		$config['taxonomies'] = array(
			RAVE_REVIEW_CATEGORY,
			RAVE_REVIEW_TAG,
		);

		$config['post_type'] = RAVE_REVIEW_PTYPE;
		$config['title'] = 'Rave Content Generator';

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
		$data = array(
			RAVE_REVIEW_RATING_AVG => rand( 1, 5 ),
			RAVE_USER_REVIEW_RATING_AVG => rand( 1, 5 ),
			RAVE_REVIEW_SPECS_META_KEY => $this->get_review_specs(),
			RAVE_REVIEW_GOOD_POINTS_META_KEY => $this->get_review_points(),
			RAVE_REVIEW_BAD_POINTS_META_KEY => $this->get_review_points(),
			RAVE_REVIEW_SUMMARY_META_KEY => APP_Content_Generator_Data::get_description(),
		);

		foreach ( $data as $meta_key => $meta_value ) {
			update_post_meta( $post_id, $meta_key, $meta_value, true );
		}

		// set listing as featured
		if ( ! empty( $_POST['home_featured'] ) && $this->add_or_not() ) {
			update_post_meta( $post_id, 'featured-home', '1' );
		}

		if ( ! empty( $_POST['cat_featured'] ) && $this->add_or_not() ) {
			update_post_meta( $post_id, 'featured-cat', '1' );
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


	/**
	 * Returns an array of random review points
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_review_points() {
		$points = array();
		$number = rand( 1, 5 );
		$adj = APP_Content_Generator_Data::data_words_adj();

		for ( $i = 1; $i <= $number; $i++ ) {			
			$point = $adj[ rand( 0, count( $adj ) - 1 ) ];
			$points[] = array( 'name' => $point );
		}

		return $points;
	}


	/**
	 * Returns an array of random review specification
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_review_specs() {
		$specs = array();
		$number = rand( 1, 10 );
		$adj = APP_Content_Generator_Data::data_words_adj();
		$things = APP_Content_Generator_Data::data_words_things();

		for ( $i = 1; $i <= $number; $i++ ) {			
			$spec_name = $things[ rand( 0, count( $things ) - 1 ) ];
			$spec_value = $adj[ rand( 0, count( $adj ) - 1 ) ];
			$specs[] = array(
				'name' => $spec_name,
				'value' => $spec_value,
			);
		}

		return $specs;
	}


}
