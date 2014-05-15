<?php
class QC_Content_Generator extends APP_Content_Generator {

	/**
	 * Class Constructor
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function __construct() {

		$config['taxonomies'] = array(
			'category',
			'post_tag',
			'ticket_milestone',
			'ticket_priority',
			'ticket_status',
		);

		$config['post_type'] = QC_TICKET_PTYPE;
		$config['title'] = 'QC Content Generator';

		parent::__construct( $config );

	}


	/**
	 * Assigns user and creates changeset
	 *
	 * @since 1.0
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function extra_post_data( $post_id ) {
		if ( $this->add_or_not() ) {
			$this->assign_user( $post_id );
		}

		if ( $this->add_or_not() ) {
			$this->create_changeset( $post_id );
		}
	}


	/**
	 * Assigns user to ticket
	 *
	 * @since 1.0
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function assign_user( $post_id ) {
		if ( isset( $_POST['authors'] ) && is_array( $_POST['authors'] ) ) {
			$author[] = (int) $_POST['authors'][ rand( 0, count( $_POST['authors'] ) - 1 ) ];
			QC_Assignment::set_assigned( $author, $post_id );
		}
	}


	/**
	 * Creates changeset
	 *
	 * @since 1.0
	 * @param int $post_id
	 *
	 * @return int
	 */
	public function create_changeset( $post_id ) {
		$rand_name = uniqid( rand( 10, 1000 ), false );

		$post = array(
			'post_title' => $rand_name,
			'post_excerpt' => $this->commit_message( $post_id ),
			'post_status' => 'publish',
			'post_type' => QC_CHANGESET_PTYPE,
		);

		return wp_insert_post( $post );
	}


	/**
	 * Creates commit message
	 *
	 * @since 1.0
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function commit_message( $post_id ) {
		$relations = array(
			'tagged' => 'post_tag',
			'category' => 'category',
			'milestone' => 'ticket_milestone',
			'priority' => 'ticket_priority',
			'status' => 'ticket_status',
		);

		$message = APP_Content_Generator_Data::get_title() . ' [#' . $post_id;
		foreach ( $relations as $key => $taxonomy ) {
			if ( empty( $_POST['tax_input'][ $taxonomy ] ) ) {
				continue;
			}

			if ( ! $this->add_or_not() ) {
				continue;
			}

			$terms = $_POST['tax_input'][ $taxonomy ];

			if ( ! is_array( $terms ) ) {
				$terms = explode( ',', $terms );
				$terms = array_map( 'trim', $terms );
				$term_name = $terms[ rand( 0, count( $terms ) - 1 ) ];
				$term = get_term_by( 'name', $term_name, $taxonomy );
			} else {
				$terms = array_map( 'intval', $terms );
				$term_id = $terms[ rand( 0, count( $terms ) - 1 ) ];
				$term = get_term_by( 'id', $term_id, $taxonomy );
			}

			if ( $term ) {
				$message .= ' ' . $key . ':' . $term->slug;
			}
		}
		$message .= ']';

		return $message;
	}

}
