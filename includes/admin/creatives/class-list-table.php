<?php
/**
 * Creatives Admin List Table
 *
 * @package     AffiliateWP
 * @subpackage  Admin/Affiliates
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.9
 */

use AffWP\Admin\List_Table;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AffWP_Creatives_Table Class
 *
 * Renders the Affiliates table on the Affiliates page
 *
 * @since 1.2
 *
 * @see \AffWP\Admin\List_Table
 */
class AffWP_Creatives_Table extends List_Table {

	/**
	 * Default number of items to show per page
	 *
	 * @var string
	 * @since 1.2
	 */
	public $per_page = 30;

	/**
	 * Total number of creatives found
	 *
	 * @var int
	 * @since 1.0
	 */
	public $total_count;

	/**
	 * Number of active creatives found
	 *
	 * @var string
	 * @since 1.2
	 */
	public $active_count;

	/**
	 * Number of inactive creatives found
	 *
	 * @var string
	 * @since 1.2
	 */
	public $inactive_count;

	/**
	 * Get things started
	 *
	 * @access public
	 * @since  1.2
	 *
	 * @see WP_List_Table::__construct()
	 *
	 * @param array $args Optional. Arbitrary display and query arguments to pass through
	 *                    the list table. Default empty array.
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'singular' => 'creative',
			'plurla'   => 'creatives',
		) );

		parent::__construct( $args );

		$this->get_creative_counts();
	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since 1.0
	 * @return array $views All the views available
	 */
	public function get_views() {
		$base           = admin_url( 'admin.php?page=affiliate-wp-creatives' );

		$current        = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$total_count    = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
		$active_count   = '&nbsp;<span class="count">(' . $this->active_count . ')</span>';
		$inactive_count = '&nbsp;<span class="count">(' . $this->inactive_count  . ')</span>';

		$views = array(
			'all'		=> sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All', 'affiliate-wp') . $total_count ),
			'active'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'active', $base ), $current === 'active' ? ' class="current"' : '', __('Active', 'affiliate-wp') . $active_count ),
			'inactive'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'inactive', $base ), $current === 'inactive' ? ' class="current"' : '', __('Inactive', 'affiliate-wp') . $inactive_count ),
		);

		return $views;
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.2
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'name'       => __( 'Name', 'affiliate-wp' ),
			'url'        => __( 'URL', 'affiliate-wp' ),
			'shortcode'  => __( 'Shortcode', 'affiliate-wp' ),
			'status'     => __( 'Status', 'affiliate-wp' ),
			'actions'    => __( 'Actions', 'affiliate-wp' ),
		);

		return $this->prepare_columns( $columns );
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since 1.2
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'name'   => array( 'name', false ),
			'status' => array( 'status', false ),
		);
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.2
	 *
	 * @param array $creative Contains all the data of the creatives
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $creative, $column_name ) {
		switch( $column_name ){
			default:
				$value = isset( $creative->$column_name ) ? $creative->$column_name : '';
				break;
		}

		return $value;
	}

	/**
	 * Render the URL column
	 *
	 * @access public
	 * @since 1.2
	 * @return string URL
	 */
	public function column_url( $creative ) {
		return $creative->url;
	}

	/**
	 * Render the shortcode column
	 *
	 * @access public
	 * @since 1.2
	 * @return string Shortcode for creative
	 */
	public function column_shortcode( $creative ) {
		return '[affiliate_creative id="' . $creative->creative_id . '"]';
	}

	/**
	 * Render the actions column
	 *
	 * @access public
	 * @since 1.2
	 * @param array $creative Contains all the data for the creative column
	 * @return string action links
	 */
	public function column_actions( $creative ) {

		$base_query_args = array(
			'page'        => 'affiliate-wp-creatives',
			'creative_id' => $creative->ID
		);

		// Edit.
		$row_actions['edit'] = $this->get_row_action_link(
			__( 'Edit', 'affiliate-wp' ),
			array_merge( $base_query_args, array(
				'affwp_notice' => false,
				'action'       => 'edit_creative'
			) )
		);


		if ( strtolower( $creative->status ) == 'active' ) {

			// Deactivate.
			$row_actions['deactivate'] = $this->get_row_action_link(
				__( 'Deactivate', 'affiliate-wp' ),
				array_merge( $base_query_args, array(
					'affwp_notice' => 'creative_deactivated',
					'action'       => 'deactivate'
				) ),
				array( 'nonce' => 'affwp-creative-nonce' )
			);

		} else {

			// Activate.
			$row_actions['activate'] = $this->get_row_action_link(
				__( 'Activate', 'affiliate-wp' ),
				array_merge( $base_query_args, array(
					'affwp_notice' => 'creative_activated',
					'action'       => 'activate'
				) ),
				array( 'nonce' => 'affwp-creative-nonce' )
			);

		}

		// Delete.
		$row_actions['delete'] = $this->get_row_action_link(
			__( 'Delete', 'affiliate-wp' ),
			array_merge( $base_query_args, array(
				'affwp_notice' => false,
				'action'       => 'delete'
			) ),
			array( 'nonce' => 'affwp-creative-nonce' )
		);

		/**
		 * Filters the row actions array for the Creatives list table.
		 *
		 * @since 1.2
		 *
		 * @param array           $row_actions Row actions array.
		 * @param \AffWP\Creative $creative    Current creative.
		 */
		$row_actions = apply_filters( 'affwp_creative_row_actions', $row_actions, $creative );

		return $this->row_actions( $row_actions, true );

	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.2
	 * @access public
	 */
	public function no_items() {
		_e( 'No creatives found.', 'affiliate-wp' );
	}

	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since 1.2
	 * @return void
	 */
	public function process_bulk_action() {

		if( empty( $_REQUEST['_wpnonce'] ) ) {
			return;
		}

		if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'affwp-creative-nonce' ) ) {
			return;
		}

		$ids = isset( $_GET['creative_id'] ) ? $_GET['creative_id'] : false;

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		$ids = array_map( 'absint', $ids );

		if ( empty( $ids ) ) {
			return;
		}

		foreach ( $ids as $id ) {

			if ( 'delete' === $this->current_action() ) {
				affiliate_wp()->creatives->delete( $id );
			}

			if ( 'activate' === $this->current_action() ) {
				affwp_set_creative_status( $id, 'active' );
			}

			if ( 'deactivate' === $this->current_action() ) {
				affwp_set_creative_status( $id, 'inactive' );
			}

		}

	}

	/**
	 * Retrieve the creative counts
	 *
	 * @access public
	 * @since 1.2
	 * @return void
	 */
	public function get_creative_counts() {
		$this->active_count = affiliate_wp()->creatives->count(
			array_merge( $this->query_args, array( 'status' => 'active' ) )
		);

		$this->inactive_count = affiliate_wp()->creatives->count(
			array_merge( $this->query_args, array( 'status' => 'inactive' ) )
		);

		$this->total_count = $this->active_count + $this->inactive_count;
	}

	/**
	 * Retrieve all the data for all the Creatives
	 *
	 * @access public
	 * @since 1.2
	 * @return array $creatives_data Array of all the data for the Creatives
	 */
	public function creatives_data() {

		$page     = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$status   = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$per_page = $this->get_items_per_page( 'affwp_edit_creatives_per_page', $this->per_page );

		$args = wp_parse_args( $this->query_args, array(
			'number'  => $per_page,
			'offset'  => $per_page * ( $page - 1 ),
			'status'  => $status,
		) );

		$creatives = affiliate_wp()->creatives->get_creatives( $args );

		return $creatives;

	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 1.2
	 * @uses AffWP_Creatives_Table::get_columns()
	 * @uses AffWP_Creatives_Table::get_sortable_columns()
	 * @uses AffWP_Creatives_Table::process_bulk_action()
	 * @uses AffWP_Creatives_Table::creatives_data()
	 * @uses WP_List_Table::get_pagenum()
	 * @uses WP_List_Table::set_pagination_args()
	 * @return void
	 */
	public function prepare_items() {
		$per_page = $this->get_items_per_page( 'affwp_edit_creatives_per_page', $this->per_page );

		$this->get_column_info();

		$this->process_bulk_action();

		$data = $this->creatives_data();

		$current_page = $this->get_pagenum();

		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		switch( $status ) {
			case 'active':
				$total_items = $this->active_count;
				break;
			case 'inactive':
				$total_items = $this->inactive_count;
				break;
			case 'any':
				$total_items = $this->total_count;
				break;
		}

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page )
			)
		);

	}
}
