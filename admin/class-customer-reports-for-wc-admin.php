<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Customer_Reports_For_Wc
 * @subpackage Customer_Reports_For_Wc/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Customer_Reports_For_Wc
 * @subpackage Customer_Reports_For_Wc/admin
 */
class Customer_Reports_For_Wc_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Check if WooCommerce plugin is installed and active.
	 * If not active, deactivate the dependent plugin and display a notice.
	 */
	public function check_woocommerce_and_deactivate_plugin() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			deactivate_plugins( 'customer-reports-for-wc/customer-reports-for-wc.php' );
			$this->show_woocommerce_dependency_notice();

		}
	}

	/**
	 * Display an activation notice if WooCommerce is not installed or active.
	 */
	public function show_woocommerce_dependency_notice() {

		echo '<div class="error"><p><strong>' . sprintf( esc_html__( 'WooCommerce Customer List plugin requires WooCommerce to be installed and active. You can download %s here.', 'customer-reports-for-wc' ), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>' ) . '</strong></p></div>';
	}

	/**
	 * Create a menu item for WooCommerce customers.
	 */
	public function woo_customer_menu() {
		add_menu_page( esc_html__( 'Customers', 'customer-reports-for-wc' ), esc_html__( 'Customers', 'customer-reports-for-wc' ), 'manage_options', 'crfwc', array( $this, 'customer_list_callback' ), 'dashicons-groups', 55 );
	}

	/**
	 * Display customer information in a table.
	 *
	 * Retrieves customer data including full name, status, email, registration date, average order rate,
	 * last order details, total orders, and total spend. Displays the data in a table format.
	 *
	 * @since 1.0.0
	 */
	public function customer_list_callback() {
		$allowd_escape_tags = array(
			'a' => array(
				'href'  => array(),
				'title' => array(),
			),
		);
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Customers', 'customer-reports-for-wc' ) . '</h1>';

		$args = array(
			'role'    => 'customer',
			'orderby' => 'registered',
			'order'   => 'DESC',
		);

		$customers = get_users( $args );
		$orders    = $this->crfwc_get_all_orders();

		if ( ! empty( $customers ) && ! empty( $orders ) ) {
			echo '<table id="woo_customer_info" class="table table-striped table-bordered" style="width:100%">';
			echo '<thead>';
			echo '<tr>';
				echo '<th>' . esc_html__( 'Full Name', 'customer-reports-for-wc' ) . '</th>';
				echo '<th class="no-sort" >' . esc_html__( 'Customer Status', 'customer-reports-for-wc' ) . '</th>';
				echo '<th class="no-sort" >' . esc_html__( 'Email', 'customer-reports-for-wc' ) . '</th>';
				echo '<th>' . esc_html__( 'Registration Date', 'customer-reports-for-wc' ) . '</th>';
				echo '<th class="no-sort" >' . esc_html__( 'Avg Order Rate', 'customer-reports-for-wc' ) . '</th>';
				echo '<th class="no-sort" >' . esc_html__( 'Last Order', 'customer-reports-for-wc' ) . '</th>';
				echo '<th class="no-sort" >' . esc_html__( 'Total Orders', 'customer-reports-for-wc' ) . '</th>';
				echo '<th class="no-sort" >' . esc_html__( 'Total Spend', 'customer-reports-for-wc' ) . '</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

			foreach ( $customers as $_cuskey => $_cusval ) {
				$customer_id = $_cusval->ID;

				$cusorders = array();

				foreach ( $orders as $order_id ) {
					$order = wc_get_order( $order_id );
					if ( $customer_id == $order->get_user_id() ) {
						$cusorders[ $order_id ] = array(
							'postdate' => $order->get_date_created(),
							'ID'       => $order_id,
						);
					}
				}
				$i              = 1;
				$len            = count( $cusorders );
				$firstorderdate = '';
				$lastorderdate  = '';
				$lastordertext  = '';
				$ordersspend    = get_woocommerce_currency_symbol() . '0';
				$orderstotals   = 0;
				$cusstatus      = __( 'Inactive', 'customer-reports-for-wc' );
				$otcal          = 0;
				$oscal          = 0;
				foreach ( $cusorders as $co ) {

					if ( $i == 1 ) {
						$cusstatus = $this->get_customer_status( $co['postdate'] );

						$lastorderdate = $co['postdate'];

						$lod       = strtotime( $lastorderdate );
						$lodformat = gmdate( 'j F Y', $lod );

						$lastordertext = '<a href="' . esc_url( admin_url( 'post.php?post=' . $co['ID'] . '&action=edit' ) ) . '" target="_blank"> #' . $co['ID'] . '</a> - ' . $lodformat;

					} elseif ( $i == $len ) {
						$firstorderdate = $co['postdate'];
					}

					++$i;
				}
				$order_count = wc_get_customer_order_count( $customer_id );
				$total_spent = wc_get_customer_total_spent( $customer_id );

				$average = $this->get_order_average( $firstorderdate, $lastorderdate, $order_count, $customer_id );

				if ( empty( $lastordertext ) ) {
					$lastordertext = esc_html__( 'No Orders', 'customer-reports-for-wc' );
				}

				if ( '' === $order_count ) {
					$orderstotal = esc_html__( 'Calculating', 'customer-reports-for-wc' );

				} else {
					$orderstotal = $order_count;
				}

				if ( '' === $total_spent ) {
					$ordersspend = esc_html__( 'Calculating', 'customer-reports-for-wc' );
				} else {
					$ordersspend = get_woocommerce_currency_symbol() . wc_format_decimal( $total_spent, 2 );
				}

				$firstname = get_user_meta( $customer_id, 'billing_first_name', true );
				$lastname  = get_user_meta( $customer_id, 'billing_last_name', true );

				if ( isset( $_cusval->first_name ) && ! empty( $_cusval->first_name ) ) {
					$fullname = $_cusval->first_name . ' ' . $_cusval->last_name;
				} elseif ( isset( $firstname ) && ! empty( $firstname ) ) {
					$fullname = $firstname . ' ' . $lastname;
				} else {
					$fullname = $_cusval->user_login;
				}

				update_user_meta( $customer_id, 'customer_status', $cusstatus );

				echo '<tr >';
					echo '<td>' . esc_html( $fullname ) . '</td>';
					echo '<td>' . esc_html( $cusstatus ) . '</td>';
					echo '<td>' . esc_html( $_cusval->data->user_email ) . '</td>';
					echo '<td>' . esc_html( gmdate( 'j F Y', strtotime( $_cusval->data->user_registered ) ) ) . '</td>';
					echo '<td>' . esc_html( $average ) . '</td>';
					echo '<td>' . wp_kses( $lastordertext, $allowd_escape_tags ) . '</td>';
					echo '<td>' . wp_kses( $orderstotal, $allowd_escape_tags ) . '</td>';
					echo '<td>' . esc_html( $ordersspend ) . '</td>';
				echo '</tr>';

			}
			echo '<tbody>';
			echo '</table>';
		} else {

			echo '<h3>' . esc_html__( 'No Customers/Orders Found!', 'customer-reports-for-wc' ) . '</h3>';

		}

		echo '</div>';
	}

	/**
	 * Fetch all orders data.
	 *
	 * Retrieves a list of distinct orders along with their post date, ID, and customer user meta value.
	 * The orders are selected from the WordPress database tables based on specific criteria.
	 *
	 * @since 1.0.0
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * @return array|null Array of order data if found, or null if no orders are found.
	 */
	public function crfwc_get_all_orders() {
		$orders = array();
		$query  = new WC_Order_Query(
			array(
				'limit'   => -1,
				'orderby' => 'date',
				'order'   => 'DESC',
				'return'  => 'ids',
			)
		);
		$orders = $query->get_orders();
		if ( isset( $orders ) && ! empty( $orders ) ) {
			return $orders;
		}
		return $orders;
	}

	/**
	 * Fetches the customer status based on the last order date.
	 *
	 * Determines the status of a customer based on the provided last order date
	 * and the configured threshold for considering customers as active or inactive.
	 *
	 * @since 1.0.0
	 *
	 * @param string $lastorderdate The last order date.
	 * @return string The customer status, either "Active" or "Inactive".
	 */
	public function get_customer_status( $lastorderdate ) {
		$settingscusstatus = 31;

		$dt   = $lastorderdate;
		$date = new DateTime( $dt );
		$now  = new DateTime();
		$diff = $now->diff( $date );

		if ( $diff->days < $settingscusstatus ) {
			return __( 'Active', 'customer-reports-for-wc' );
		} else {
			return __( 'Inactive', 'customer-reports-for-wc' );
		}
	}

	/**
	 * Fetches the order average for a customer.
	 *
	 * Calculates the average time between the first and last orders for a customer,
	 * based on the provided first and last order dates, and the total number of orders.
	 *
	 * @since 1.0.0
	 *
	 * @param string $firstorderdate The date of the first order.
	 * @param string $lastorderdate The date of the last order.
	 * @param int    $totalcusorders The total number of orders for the customer.
	 * @param int    $customer_id The ID of the customer.
	 * @return string The order average, in days, or "No Average" if there are no orders.
	 */
	public function get_order_average( $firstorderdate, $lastorderdate, $totalcusorders, $customer_id ) {
		if ( ! empty( $firstorderdate ) && ! empty( $lastorderdate ) ) {

			$date1 = new DateTime( $firstorderdate );
			$date2 = new DateTime( $lastorderdate );

			$diff = $date2->diff( $date1 )->format( '%a' );
		} else {
			$diff = 0;
		}

		if ( $diff > 0 ) {
			$countco          = $totalcusorders - 1;
			$customer_average = round( $diff / $countco );
			update_user_meta( $customer_id, 'customer_average', $customer_average );
			$customer_average_txt = sprintf( 'Every %u days', $customer_average );
			return esc_html__( $customer_average_txt, 'customer-reports-for-wc' );
		} else {
			update_user_meta( $customer_id, 'customer_average', 0 );
			return esc_html__( 'No Average', 'customer-reports-for-wc' );
		}
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		$screen = get_current_screen();

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Customer_Reports_For_Wc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Customer_Reports_For_Wc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( $screen->id == 'toplevel_page_crfwc' ) {
			/*
			wp_enqueue_style( 'datatables-css', plugin_dir_url( __FILE__ ) . 'css/jquery.dataTables.min.css', array(), $this->version, 'all' );

			wp_enqueue_style( 'datatables-bootstrapmin-css', plugin_dir_url( __FILE__ ) . 'css/dataTables.bootstrap4.min.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'buttons.dataTables-css', plugin_dir_url( __FILE__ ) . 'css/buttons.dataTables.min.css', array(), $this->version, 'all' ); */

			wp_enqueue_style( 'datatables-jquery-ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'datatables', plugin_dir_url( __FILE__ ) . 'css/datatables.min.css', array(), $this->version, 'all' );

			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/customer-reports-for-wc-admin.css', array(), $this->version, 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Customer_Reports_For_Wc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Customer_Reports_For_Wc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$screen = get_current_screen();
		if ( $screen->id == 'toplevel_page_crfwc' ) {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'jquery-ui' );
			/*
			wp_enqueue_script( 'datatables', plugin_dir_url( __FILE__ ) . 'js/jquery.dataTables.min.js', array( 'jquery' ), '1.13.7', true );
			wp_enqueue_script( 'datatables-btn', plugin_dir_url( __FILE__ ) . 'js/dataTables.buttons.min.js', array( 'jquery' ), '2.4.2', true );
			wp_enqueue_script( 'datatables-jszip', plugin_dir_url( __FILE__ ) . 'js/jszip.min.js', array( 'jquery' ), '3.10.1', true );
			wp_enqueue_script( 'datatables-pdf', plugin_dir_url( __FILE__ ) . 'js/pdfmake.min.js', array( 'jquery' ), '0.1.53', true );
			wp_enqueue_script( 'datatables-vfs_fonts', plugin_dir_url( __FILE__ ) . 'js/vfs_fonts.js', array( 'jquery' ), '0.1.53', true );
			wp_enqueue_script( 'datatables-html5', plugin_dir_url( __FILE__ ) . 'js/buttons.html5.min.js', array( 'jquery' ), '2.4.2', true ); */

			wp_enqueue_script( 'datatables-pdfmake', plugin_dir_url( __FILE__ ) . 'js/pdfmake.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( 'datatables-vfs_fonts', plugin_dir_url( __FILE__ ) . 'js/vfs_fonts.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( 'datatables', plugin_dir_url( __FILE__ ) . 'js/datatables.min.js', array( 'jquery' ), $this->version, true );

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/customer-reports-for-wc-admin.js', array( 'jquery' ), $this->version, true );
		}
	}
}
