<?php
/**
 * Helper Functions
 *
 * @package     EDD\EDD_WordPress_Downloads\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Change the button text of a free download. Default is "Free - Add to Cart"
 *
 * @since 1.0.0
 */
function edd_wordpress_downloads_text_args( $args ) {
	$free_download_text = __( 'Free Download', 'edd-wordpress-downloads' );
	$variable_pricing 	= edd_has_variable_prices( $args['download_id'] );

	if ( $args['price'] && $args['price'] !== 'no' && ! $variable_pricing ) {
		$price = edd_get_download_price( $args['download_id'] );
		if ( 0 == $price ) {
			$args['text'] = $free_download_text;
		}
	}
	return $args;
}
add_filter( 'edd_purchase_link_args', 'edd_wordpress_downloads_text_args' );

/**
 * WordPress Plugin URL Field
 *
 * Adds field do the EDD Downloads meta box for specifying the "WordPress Plugin URL"
 *
 * @since 1.0.0
 * @param integer $post_id Download (Post) ID
 */
function edd_wordpress_downloads_meta_field( $post_id ) {
	$edd_wordpress_downloads_url = get_post_meta( $post_id, '_edd_wordpress_downloads_url', true );
	?>

		<p><strong><?php _e( 'WordPress.org URL:', 'edd-wordpress-downloads' ); ?></strong></p>
		<label for="edd-wordpress-downloads-url">
			<input type="text" name="_edd_wordpress_downloads_url" id="edd-wordpress-downloads-url" value="<?php echo esc_attr( $edd_wordpress_downloads_url ); ?>" size="80" placeholder="https://wordpress.org/plugins/your-plugin-slug/" />
			<br/><?php _e( 'The URL to use if this is a free plugin or theme on the WordPress.org repository. Leave blank for standard products.', 'edd-wordpress-downloads' ); ?>
		</label>

	<?php
}
add_action( 'edd_meta_box_fields', 'edd_wordpress_downloads_meta_field' );

/**
 * Add the _edd_wordpress_plugin_url field to the list of saved product fields
 *
 * @since  1.0.0
 *
 * @param  array $fields The default product fields list
 * @return array         The updated product fields list
 */
function edd_wordpress_downloads_save( $fields ) {

	// Add our field
	$fields[] = '_edd_wordpress_downloads_url';

	// Return the fields array
	return $fields;
}
add_filter( 'edd_metabox_fields_save', 'edd_wordpress_downloads_save' );


/**
 * Sanitize metabox field to only accept URLs
 *
 * @since 1.0.0
*/
function edd_wordpress_downloads_metabox_save( $new ) {

	// Convert to raw URL to save into wp_postmeta table
	$new = esc_url_raw( $_POST[ '_edd_wordpress_downloads_url' ] );

	// Return URL
	return $new;

}
add_filter( 'edd_metabox_save__edd_external_url', 'edd_wordpress_downloads_metabox_save' );

/**
 * Prevent a download linked to an external URL from being purchased with ?edd_action=add_to_cart&download_id=XXX
 *
 * @since 1.0.0
*/
function edd_wordpress_downloads_pre_add_to_cart( $download_id ) {

	$edd_plugin_url = get_post_meta( $download_id, '_edd_wordpress_plugin_url', true ) ? get_post_meta( $download_id, '_edd_wordpress_plugin_url', true ) : '';

	// Prevent user trying to purchase download using EDD purchase query string
	if ( $edd_plugin_url ) {
		wp_die( sprintf( __( 'This download can only be purchased from %s', 'edd-external-product' ), esc_url( $edd_plugin_url ) ), '', array( 'back_link' => true ) );
	}

}
add_action( 'edd_pre_add_to_cart', 'edd_wordpress_downloads_pre_add_to_cart' );

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
function edd_wordpress_downloads_link( $purchase_form, $args ) {

	// If the product has an external URL set
	if ( $edd_wordpress_downloads_url = get_post_meta( $args['download_id'], '_edd_wordpress_downloads_url', true ) ) {

		// Open up the standard containers
		$output = '<div class="edd_download_purchase_form">';
		$output .= '<div class="edd_purchase_submit_wrapper">';

		// Output an anchor tag with the same classes as the product button
		$output .= sprintf(
			'<a class="%1$s" href="%2$s" %3$s>%4$s</a>',
			implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) ),
			esc_attr( $edd_wordpress_downloads_url ),
			apply_filters( 'edd_wordpress_downloads_link_attrs', '', $args ),
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
add_filter( 'edd_purchase_download_form', 'edd_wordpress_downloads_link', 10, 2 );

/**
 * Determines if the provided URL is a WordPress.org plugin or theme and returns the slug.
 *
 * @since 1.0.0
 *
 * @param 	string $url The URL of the WordPress.org plugin or theme.
 * @return 	array
 */
function edd_wordpress_downloads_parse_url( $url ) {
	if ( strpos( $url, 'wordpress.org/plugins' ) !== false ) {
		$type = 'plugin';
		$slug = explode( 'plugins/', $url );
		$slug = str_replace( '/', '', $slug[1] );
	} elseif ( strpos( $url, 'wordpress.org/themes' ) !== false ) {
		$type = 'theme';
		$slug = explode( 'themes/', $url );
		$slug = str_replace( '/', '', $slug[1] );
	} else {
		$type = 'other';
		$slug = 'none';
	}

	return array( 'type' => $type, 'slug' => $slug );
}

/**
 * Retrieves information about the provided WordPress.org plugin.
 *
 * @since 	1.0.0
 *
 * @param 	string $url The URL of the WordPress plugin to get data for.
 * @return 	array|boolean
 */
function edd_wordpress_downloads_get_data( $url ) {
	$url 		= esc_url_raw( $url );
	$download 	= edd_wordpress_downloads_parse_url( $url );
	$cached 	= get_transient( $download['slug'] . '_edd_wordpress_downloads_data' .time() );

	$result = array(
		'added' 		=> '',
		'last_updated' 	=> '',
		'downloaded' 	=> '',
		'author' 		=> '',
		'rating' 		=> '',
		'num_ratings' 	=> '',
		'version' 		=> '',
		'name' 			=> ''
	);

	// Always return cached transients.
	if ( is_array( $cached ) ) {
		return $cached;
	}

	if ( 'plugin' === $download['type'] ) {
		$api_url 	= 'https://api.wordpress.org/plugins/info/1.0/' . $download['slug'] . '.json';
		$response 	= wp_remote_get( $api_url );
	} elseif ( 'theme' === $download['type'] ) {
		$api_url 	= 'https://api.wordpress.org/themes/info/1.1/';
		$args 		= array(
			'body' => array(
				'action' 	=> 'theme_information',
				'timeout' 	=> 15,
				'request' 	=> array( 'slug' => $download['slug'] )
			)
		);
		$response = wp_remote_post( $api_url, $args );
	} else {
		return false;
	}

	if ( ! is_array( $response ) || is_wp_error( $response ) ) {
		return false;
	}

	// Grab/decode the json.
	$body = $response['body'];
	$info = json_decode( $body );

	if ( isset( $info->added ) ) {
		$result['added'] = $info->added;
	}

	if ( isset( $info->last_updated ) ) {
		$updated = explode( ' ', $info->last_updated );
		$result['last_updated'] = $updated[0];
	}

	if ( isset( $info->downloaded ) ) {
		$result['downloaded'] = number_format_i18n( $info->downloaded );
	}

	if ( isset( $info->rating ) ) {
		$result['rating'] = round( $info->rating / 20, 1 ). '/5';
	}

	if ( isset( $info->author ) ) {
		$result['author'] = $info->author;
	}

	if ( isset( $info->num_ratings ) ) {
		$result['num_ratings'] = $info->num_ratings;
	}

	if ( isset( $info->version ) ) {
		$result['version'] = $info->version;
	}

	if ( isset( $info->name ) ) {
		$result['name'] = $info->name;
	}

	$result = apply_filters( 'edd_wordpress_downloads_data', array_filter( $result ) );
	$length = apply_filters( 'edd_wordpress_downloads_transient_length', HOUR_IN_SECONDS );

	set_transient( $download['slug'] . '_edd_wordpress_downloads_data', $result, $length );

	return $result;
}
