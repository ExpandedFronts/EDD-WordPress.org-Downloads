<?php
/**
 * Helper Functions
 *
 * @package     EDD\EDD_WordPress_Plugins\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Change the button text of a free download. Default is "Free - Add to Cart"
 */
function edd_wordpress_plugins_text_args( $args ) {
	$free_download_text = 'Free Download';
	$variable_pricing 	= edd_has_variable_prices( $args['download_id'] );

	if ( $args['price'] && $args['price'] !== 'no' && ! $variable_pricing ) {
		$price = edd_get_download_price( $args['download_id'] );
		if ( 0 == $price ) {
			$args['text'] = $free_download_text;
		}
	}
	return $args;
}
add_filter( 'edd_purchase_link_args', 'edd_wordpress_plugins_text_args' );

/**
 * WordPress Plugin URL Field
 *
 * Adds field do the EDD Downloads meta box for specifying the "WordPress Plugin URL"
 *
 * @since 1.0.0
 * @param integer $post_id Download (Post) ID
 */
function edd_wordpress_plugins_meta_field( $post_id ) {
	$edd_plugin_url = get_post_meta( $post_id, '_edd_wordpress_plugin_url', true );
	?>

		<p><strong><?php _e( 'WordPress Plugin URL:', 'edd-wordpress-plugin' ); ?></strong></p>
		<label for="edd-wordpress-plugin-url">
			<input type="text" name="_edd_wordpress_plugin_url" id="edd-wordpress-plugin-url" value="<?php echo esc_attr( $edd_plugin_url ); ?>" size="80" placeholder="https://wordpress.org/plugins/your-plugin-slug" />
			<br/><?php _e( 'The WordPress plugin URL to use if this is a free plugin on the WordPress.org repository. Leave blank for standard products.', 'edd-wordpress-plugin' ); ?>
		</label>
	<?php
}
add_action( 'edd_meta_box_fields', 'edd_wordpress_plugins_meta_field' );

/**
 * Add the _edd_wordpress_plugin_url field to the list of saved product fields
 *
 * @since  1.0.0
 *
 * @param  array $fields The default product fields list
 * @return array         The updated product fields list
 */
function edd_wordpress_plugins_save( $fields ) {

	// Add our field
	$fields[] = '_edd_wordpress_plugin_url';

	// Return the fields array
	return $fields;
}
add_filter( 'edd_metabox_fields_save', 'edd_wordpress_plugins_save' );


/**
 * Sanitize metabox field to only accept URLs
 *
 * @since 1.0.0
*/
function edd_wordpress_plugins_metabox_save( $new ) {

	// Convert to raw URL to save into wp_postmeta table
	$new = esc_url_raw( $_POST[ '_edd_wordpress_plugin_url' ] );

	// Return URL
	return $new;

}
add_filter( 'edd_metabox_save__edd_external_url', 'edd_wordpress_plugins_metabox_save' );

/**
 * Prevent a download linked to an external URL from being purchased with ?edd_action=add_to_cart&download_id=XXX
 *
 * @since 1.0.0
*/
function edd_wordpress_plugins_pre_add_to_cart( $download_id ) {

	$edd_plugin_url = get_post_meta( $download_id, '_edd_wordpress_plugin_url', true ) ? get_post_meta( $download_id, '_edd_wordpress_plugin_url', true ) : '';

	// Prevent user trying to purchase download using EDD purchase query string
	if ( $edd_plugin_url ) {
		wp_die( sprintf( __( 'This download can only be purchased from %s', 'edd-external-product' ), esc_url( $edd_plugin_url ) ), '', array( 'back_link' => true ) );
	}

}
add_action( 'edd_pre_add_to_cart', 'edd_wordpress_plugins_pre_add_to_cart' );

/**
 * Override the default product purchase button with an external anchor
 *
 * Only affects products that have an external URL stored
 *
 * @since  1.0.0
 *
 * @param  string    $purchase_form The concatenated markup for the purchase area
 * @param  array    $args           Args passed from {@see edd_get_purchase_link()}
 * @return string                   The potentially modified purchase area markup
 */
function edd_wordpress_plugins_link( $purchase_form, $args ) {

	// If the product has an external URL set
	if ( $edd_plugin_url = get_post_meta( $args['download_id'], '_edd_wordpress_plugin_url', true ) ) {

		// Open up the standard containers
		$output = '<div class="edd_download_purchase_form">';
		$output .= '<div class="edd_purchase_submit_wrapper">';

		// Output an anchor tag with the same classes as the product button
		$output .= sprintf(
			'<a class="%1$s" href="%2$s" %3$s>%4$s</a>',
			implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) ),
			esc_attr( $edd_plugin_url ),
			apply_filters( 'edd_wordpress_plugin_link_attrs', '', $args ),
			esc_attr( $args['text'] )
		);

		// Close the containers
		$output .= '</div><!-- .edd_purchase_submit_wrapper -->';
		$output .= '</div><!-- .edd_download_purchase_form -->';

		// Replace the form output with our own output
		$purchase_form = $output;
	}

	// Return the possibly modified purchase form
	return $purchase_form;
}
add_filter( 'edd_purchase_download_form', 'edd_wordpress_plugins_link', 10, 2 );

/**
 * Retrieves information about the provided WordPress.org plugin.
 *
 * @since 	1.0.0
 *
 * @param 	string $url The URL of the WordPress plugin to get data for.
 * @return 	array
 */
function edd_get_wordpress_plugin_data( $url ) {
	$url 		= esc_url_raw( $url );
	$slug 		= explode( 'plugins/', $url );
	$slug 		= str_replace( '/', '', $slug[1] );
	$data 		= get_transient( $slug . '_wp_plugin_data' );

	if ( is_array( $data ) ) {
		return $data;
	} else {

		$api_url 	= 'https://api.wordpress.org/plugins/info/1.0/' . $slug . '.json';
		$response 	= wp_remote_get( $api_url );

		if ( is_array( $response ) ) {
			$json 	= $response['body'];
			$obj 	= json_decode( $json );

			$last_updated 	= explode( ' ', $obj->last_updated );
			$last_updated 	= $last_updated[0];
			$downloaded 	= number_format_i18n( $obj->downloaded );
			$rating 		= round( $obj->rating / 20, 1 ). '/5';

			$want = array(
				'added' 		=> $obj->added,
				'last_updated' 	=> $last_updated,
				'downloaded' 	=> $downloaded,
				'author' 		=> $obj->author,
				'rating' 		=> $rating,
				'num_ratings' 	=> $obj->num_ratings,
				'version'		=> $obj->version,
				'name'			=> $obj->name
			);

			set_transient( $slug . '_wp_plugin_data', $want, DAY_IN_SECONDS );
			return $want;
		} else {
			return array();
		}

	}

}
