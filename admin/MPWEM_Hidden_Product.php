<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}  // if direct access
	if ( ! class_exists( 'MPWEM_Hidden_Product' ) ) {
		class MPWEM_Hidden_Product {
			public function __construct() {
				add_action( 'wp_insert_post', array( $this, 'create_hidden_wc_product_on_publish' ), 10, 3 );
				add_action( 'save_post', array( $this, 'run_link_product_on_save' ), 99, 1 );
				add_action( 'parse_query', array( $this, 'hide_wc_hidden_product_from_product_list' ) );
				add_action( 'pre_get_posts', array( $this, 'hide_wc_hidden_product') );
				add_action( 'wp', array( $this, 'hide_hidden_wc_product_from_frontend' ) );
				//******************//
				add_action( 'admin_init', [ $this, 'mep_create_old_event_product' ] );
				//******************//
				add_action( 'wp_head', [ $this, 'url_exclude_search_engine' ] );
				//add_action( 'init', [ $this, 'get_all_hidden_product_id' ] );
				add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', [ $this, 'get_all_hidden_product_id' ] );
			}

			public function create_hidden_wc_product_on_publish( $post_id, $post ) {
				if ( $post->post_type == MPWEM_Functions::get_cpt() && $post->post_status == 'publish' && empty( MPWEM_Global_Function::get_post_info( $post_id, 'check_if_run_once' ) ) ) {
					$product_cat_ids = wp_get_post_terms( $post_id, 'product_cat', array( 'fields' => 'ids' ) );
					// ADD THE FORM INPUT TO $new_post ARRAY
					$new_post = array(
						'post_title'    => $post->post_title,
						'post_content'  => '',
						'post_name'     => uniqid(),
						'post_category' => array(),  // Usable for custom taxonomies too
						'tags_input'    => array(),
						'post_status'   => 'publish', // Choose: publish, preview, future, draft, etc.
						'post_type'     => 'product'  //'post',page' or use a custom post type if you want to
					);
					//SAVE THE POST
					$pid          = wp_insert_post( $new_post );
					$product_type = MPWEM_Global_Function::get_settings( 'single_event_setting_sec', 'mep_event_product_type', 'yes' );
					$_tax_status  = 'none';
					update_post_meta( $pid, '_tax_status', $_tax_status );
					update_post_meta( $post_id, '_tax_status', $_tax_status );
					update_post_meta( $post_id, 'link_wc_product', $pid );
					update_post_meta( $pid, 'link_mep_event', $post_id );
					update_post_meta( $pid, '_price', 0.01 );
					update_post_meta( $pid, '_sold_individually', 'yes' );
					update_post_meta( $pid, '_downloadable', $product_type );
					update_post_meta( $pid, '_virtual', $product_type );
					$terms = array( 'exclude-from-catalog', 'exclude-from-search' );
					wp_set_object_terms( $pid, $terms, 'product_visibility' );
					wp_set_post_terms( $pid, $product_cat_ids, 'product_cat' );
					update_post_meta( $post_id, 'check_if_run_once', true );
				}
			}

			public function run_link_product_on_save( $post_id ) {
				add_filter( 'wpseo_public_post_statuses', 'mepfix_sitemap_exclude_post_type', 5 );
				if ( get_post_type( $post_id ) == MPWEM_Functions::get_cpt() ) {
					if ( ! isset( $_POST['mpwem_type_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mpwem_type_nonce'] ) ), 'mpwem_type_nonce' ) ) {
						return;
					}
					if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
						return;
					}
					if ( ! current_user_can( 'edit_post', $post_id ) ) {
						return;
					}
					$event_name = get_the_title( $post_id );
					if ( $this->count_hidden_wc_product( $post_id ) == 0 || empty( MPWEM_Global_Function::get_post_info( $post_id, 'link_wc_product' ) ) ) {
						self::create_hidden_wc_product( $post_id, $event_name );
					}
					$product_cat_ids = wp_get_post_terms( $post_id, 'product_cat', array( 'fields' => 'ids' ) );
					$product_id      = MPWEM_Global_Function::get_post_info( $post_id, 'link_wc_product', $post_id );
					set_post_thumbnail( $product_id, get_post_thumbnail_id( $post_id ) );
					wp_publish_post( $product_id );
					$product_type = MPWEM_Global_Function::get_settings( 'single_event_setting_sec', 'mep_event_product_type', 'yes' );
					$_tax_status  = isset( $_POST['_tax_status'] ) ? sanitize_text_field( wp_unslash( $_POST['_tax_status'] ) ) : 'none';
					$_tax_class   = isset( $_POST['_tax_class'] ) ? sanitize_text_field( wp_unslash( $_POST['_tax_class'] ) ) : '';
					$sku          = isset( $_POST['mep_event_sku'] ) ? sanitize_text_field( wp_unslash( $_POST['mep_event_sku'] ) ) : $product_id;
					update_post_meta( $product_id, '_sku', $sku );
					update_post_meta( $product_id, '_tax_status', $_tax_status );
					update_post_meta( $product_id, '_tax_class', $_tax_class );
					update_post_meta( $product_id, '_stock_status', 'instock' );
					update_post_meta( $product_id, '_manage_stock', 'no' );
					update_post_meta( $product_id, '_virtual', $product_type );
					update_post_meta( $product_id, '_sold_individually', 'yes' );
					update_post_meta( $product_id, '_downloadable', $product_type );
					wp_set_post_terms( $product_id, $product_cat_ids, 'product_cat' );
					$terms = array( 'exclude-from-catalog', 'exclude-from-search' );
					wp_set_object_terms( $product_id, $terms, 'product_visibility' );
					// Update post
					$my_post = array(
						'ID'         => $product_id,
						'post_title' => $event_name, // new title
						'post_name'  => uniqid()// do your thing here
					);
					// unhook this function so it doesn't loop infinitely
					remove_action( 'save_post', array( $this, 'run_link_product_on_save' ), 99 );
					// update the post, which calls save_post again
					wp_update_post( $my_post );
					// re-hook this function
					add_action( 'save_post', array( $this, 'run_link_product_on_save' ), 99 );
					// Update the post into the database
				}
			}

			public static function create_hidden_wc_product( $post_id, $title ) {
				$new_post    = array(
					'post_title'    => $title,
					'post_content'  => '',
					'post_name'     => uniqid(),
					'post_category' => array(),
					'tags_input'    => array(),
					'post_status'   => 'publish',
					'post_type'     => 'product'
				);
				$_tax_status = 'none';
				$pid         = wp_insert_post( $new_post );
				update_post_meta( $post_id, 'link_wc_product', $pid );
				update_post_meta( $pid, 'link_mep_event', $post_id );
				update_post_meta( $pid, '_price', 0.01 );
				update_post_meta( $pid, '_tax_status', $_tax_status );
				update_post_meta( $pid, '_sold_individually', 'yes' );
				update_post_meta( $pid, '_virtual', 'yes' );
				$terms = array( 'exclude-from-catalog', 'exclude-from-search' );
				wp_set_object_terms( $pid, $terms, 'product_visibility' );
				update_post_meta( $post_id, 'check_if_run_once', true );
			}

			public function count_hidden_wc_product( $post_id ): int {
				$args = array(
					'post_type'      => 'product',
					'posts_per_page' => - 1,
					'meta_query'     => array(
						array(
							'key'     => 'link_mep_event',
							'value'   => $post_id,
							'compare' => '='
						)
					)
				);
				$loop = new WP_Query( $args );

				return $loop->post_count;
			}

			public function hide_wc_hidden_product( $query ) {
				if ( $query->is_search && ! is_admin() ) {
					$query->set( 'tax_query', array(
						array(
							'taxonomy' => 'product_visibility',
							'field'    => 'name',
							'terms'    => 'exclude-from-search',
							'operator' => 'NOT IN',
						)
					) );
				}
				return $query;
			}
		public function hide_wc_hidden_product_from_product_list( $query ) {
			global $pagenow;
			$q_vars = &$query->query_vars;
			if ( $pagenow == 'edit.php' && isset( $q_vars['post_type'] ) && $q_vars['post_type'] == 'product' ) {
				$show_hidden = MPWEM_Global_Function::get_settings( 'general_setting_sec', 'mep_show_hidden_wc_product', 'no' );
				if ( $show_hidden !== 'yes' ) {
					$tax_query = array(
						[
							'taxonomy' => 'product_visibility',
							'field'    => 'slug',
							'terms'    => 'exclude-from-catalog',
							'operator' => 'NOT IN',
						]
					);
					$query->set( 'tax_query', $tax_query );
				}
			}
		}

			public function hide_hidden_wc_product_from_frontend() {
				global $post, $wp_query;
				if ( is_product() ) {
					$post_id    = $post->ID;
					$visibility = get_the_terms( $post_id, 'product_visibility' ) ? get_the_terms( $post_id, 'product_visibility' ) : [ 0 ];
					if ( is_object( $visibility[0] ) ) {
						if ( $visibility[0]->name == 'exclude-from-catalog' ) {
							$check_event_hidden = get_post_meta( $post_id, 'link_mep_event', true ) ? get_post_meta( $post_id, 'link_mep_event', true ) : 0;
							if ( $check_event_hidden > 0 ) {
								$wp_query->set_404();
								status_header( 404 );
								get_template_part( 404 );
								exit();
							}
						}
					}
				}
			}

			//***********************************//
			public function mep_create_old_event_product() {
				if ( get_option( 'wbtm_create_old_bus_products_101' ) != 'completed' ) {
					$args = array(
						'post_type'      => 'mep_events',
						'posts_per_page' => - 1
					);
					$qr   = new WP_Query( $args );
					foreach ( $qr->posts as $result ) {
						$post_id = $result->ID;
						MPWEM_Hidden_product::create_hidden_wc_product( $post_id, get_the_title( $post_id ) );
					}
					update_option( 'wbtm_create_old_bus_products_101', 'completed' );
				}
			}

			//**************Google search url hidden*********************//
			public function url_exclude_search_engine() {
				global $post;
				if ( is_single() && is_product() ) {
					$post_id    = $post->ID;
					$visibility = get_the_terms( $post_id, 'product_visibility' ) ? get_the_terms( $post_id, 'product_visibility' ) : [ 0 ];
					if ( is_object( $visibility[0] ) ) {
						if ( $visibility[0]->name == 'exclude-from-catalog' ) {
							$check_event_hidden = get_post_meta( $post_id, 'link_mep_event', true ) ? get_post_meta( $post_id, 'link_mep_event', true ) : 0;
							if ( $check_event_hidden > 0 ) {
								echo '<meta name="robots" content="noindex, nofollow">';
							}
						}
					}
				}
			}

			public function get_all_hidden_product_id() {
				$product_id = [];
				$query      = MPWEM_Query::query_post_type( MPWEM_Functions::get_cpt() );
				foreach ( $query->posts as $result ) {
					$post_id      = $result->ID;
					$product_id[] = MPWEM_Global_Function::get_post_info( $post_id, 'link_wc_product' );
				}

				return array_filter( $product_id );
			}
		}
	}
	new MPWEM_Hidden_Product();
