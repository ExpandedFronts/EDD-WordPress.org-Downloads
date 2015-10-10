<?php
/**
 * widgets.php
 *
 * Widgets displayed by the plugin.
 *
 * @package     EDD\EDD_WP_Downloads\Widgets
 * @since       1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) exit;

class EDD_WP_Downloads_Widget extends WP_Widget {

	/**
	 * Register the widget
	 */
	public function __construct() {
		parent::__construct(
			'edd_wp_downloads_widget',
			'EDD WordPress.org Downloads',
			array(
				'description' => 'Display info for WordPress.org plugins or themes added through EDD.',
			)
		);
	}

	/**
	 * Output the content of the Widget.
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		global $post;

		if ( ! $post || ! isset( $post->ID ) ) {
			return;
		}

		$wp_downloads_url = get_post_meta( $post->ID, '_edd_wp_downloads_url', true );

		// Bail!
		if ( ! $wp_downloads_url ) {
			return;
		}

		$data = edd_wp_downloads_get_data( $wp_downloads_url );

		// Bail!
		if ( ! $data ) {
			return;
		}

		// Get the options we should show.
		$added 			= isset( $instance['added'] ) ? $instance['added'] : 1;
		$updated 		= isset( $instance['updated'] ) ? $instance['updated'] : 1;
		$downloaded 	= isset( $instance['downloaded'] ) ? $instance['downloaded'] : 1;
		$rating 		= isset( $instance['rating'] ) ? $instance['rating'] : 1;
		$version 		= isset( $instance['version'] ) ? $instance['version'] : 1;
		$output_css		= isset( $instance['output_css'] ) ? $instance['output_css'] : 1;

		if ( $output_css ) {
			$list_styles = 'style="list-style-type: none;"';
			$name_styles = 'style="display: block; font-weight: bold;"';
			$info_styles = 'style="display: block; margin-bottom: 10px;"';
		} else {
			$list_styles = '';
			$name_styles = '';
			$info_styles = '';
		}


		// Start outputting the content.
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}

		?>
		<ul class="edd-wp-download-details" <?php echo $list_styles; ?>>

			<?php if ( $added && isset( $data['added'] ) ): ?>

				<li>
					<span class="edd-wp-downloads-detail-name" <?php echo $name_styles; ?>><?php _e( 'Date Added: ', 'edd-wp-downloads' ); ?></span>
					<span class="edd-wp-downloads-detail-info" <?php echo $info_styles; ?>><?php echo $data['added']; ?></span>
				</li>

			<?php endif; ?>

			<?php if ( $updated && isset( $data['last_updated'] ) ): ?>

				<li>
					<span class="edd-wp-downloads-detail-name" <?php echo $name_styles; ?>><?php _e( 'Last Updated: ', 'edd-wp-downloads' ); ?></span>
					<span class="edd-wp-downloads-detail-info" <?php echo $info_styles; ?>><?php echo $data['last_updated']; ?></span>
				</li>

			<?php endif; ?>

			<?php if ( $downloaded && isset( $data['downloaded'] ) ): ?>

				<li>
					<span class="edd-wp-downloads-detail-name" <?php echo $name_styles; ?>><?php _e( 'Downloaded: ', 'edd-wp-downloads' ); ?></span>
					<span class="edd-wp-downloads-detail-info" <?php echo $info_styles; ?>><?php echo $data['downloaded'] . __( ' times', 'edd-wp-downloads' ); ?></span>
				</li>

			<?php endif; ?>

			<?php if ( $rating && isset( $data['rating'] ) ): ?>

				<?php $ratings_txt = sprintf(
					__( '%s from %d ratings', 'edd-wp-downloads' ),
					$data['rating'],
					$data['num_ratings']
				); ?>

				<li>
					<span class="edd-wp-downloads-detail-name" <?php echo $name_styles; ?>><?php _e( 'Rated: ', 'edd-wp-downloads' ); ?></span>
					<span class="edd-wp-downloads-detail-info" <?php echo $info_styles; ?>><?php echo $ratings_txt; ?></span>
				</li>

			<?php endif; ?>

			<?php if ( $version && isset( $data['version'] ) ): ?>

				<li>
					<span class="edd-wp-downloads-detail-name" <?php echo $name_styles; ?>><?php _e( 'Version: ', 'edd-wp-downloads' ); ?></span>
					<span class="edd-wp-downloads-detail-info" <?php echo $info_styles; ?>><?php echo $data['version']; ?></span>
				</li>

			<?php endif; ?>

		</ul>

		<?php

		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form.
	 *
	 * @param array $instance
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		// default settings
		$defaults = array(
			'added'  		=> 1,
			'updated'      	=> 1,
			'downloaded'   	=> 1,
			'version'    	=> 1,
			'rating' 		=> 1,
		);

		$instance   	= wp_parse_args( (array) $instance, $defaults );
		$added  		= isset( $instance['added'] )  ? (bool) $instance['added']  : true;
		$updated      	= isset( $instance['updated'] )      ? (bool) $instance['updated']      : true;
		$downloaded   	= isset( $instance['downloaded'] )   ? (bool) $instance['downloaded']   : true;
		$version    	= isset( $instance['version'] )    ? (bool) $instance['version']    : true;
		$rating 		= isset( $instance['rating'] ) ? (bool) $instance['rating'] : true;
		$output_css 	= isset( $instance['output_css'] ) ? (bool) $instance['output_css'] : true;

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'edd-wp-downloads' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
			<input <?php checked( $added ); ?> id="<?php echo esc_attr( $this->get_field_id( 'added' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'added' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'added' ) ); ?>"><?php _e( 'Show Date Added', 'edd-wp-downloads' ); ?></label>
		</p>

		<p>
			<input <?php checked( $updated ); ?> id="<?php echo esc_attr( $this->get_field_id( 'updated' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'updated' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'updated' ) ); ?>"><?php _e( 'Show Date Updated', 'edd-wp-downloads' ); ?></label>
		</p>

		<p>
			<input <?php checked( $version ); ?> id="<?php echo esc_attr( $this->get_field_id( 'version' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'version' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'version' ) ); ?>"><?php _e( 'Show Version Number', 'edd-wp-downloads' ); ?></label>
		</p>

		<p>
			<input <?php checked( $rating ); ?> id="<?php echo esc_attr( $this->get_field_id( 'rating' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'rating' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'rating' ) ); ?>"><?php _e( 'Show Rating', 'edd-wp-downloads' ); ?></label>
		</p>

		<p>
			<input <?php checked( $output_css ); ?> id="<?php echo esc_attr( $this->get_field_id( 'output_css' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'output_css' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'output_css' ) ); ?>"><?php _e( 'Output CSS', 'edd-wp-downloads' ); ?></label>
		</p>

		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance               	= $old_instance;
		$instance['title']      	= ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['added']  		= ! empty( $new_instance['added'] ) 		? 1 : 0;
		$instance['updated'] 		= ! empty( $new_instance['updated'] ) 		? 1 : 0;
		$instance['rating']       	= ! empty( $new_instance['rating'] ) 		? 1 : 0;
		$instance['version']  		= ! empty( $new_instance['version'] )  		? 1 : 0;
		$instance['output_css']		= ! empty( $new_instance['output_css'] )	? 1 :0;
		return $instance;
	}

}

/**
 * Register the widgets
 *
 * @package     EDD\EDD_WP_Downloads\Functions
 * @since       1.0.0
 */
function edd_wp_downloads_register_widgets() {
	register_widget( 'edd_wp_downloads_widget' );
}
add_action( 'widgets_init', 'edd_wp_downloads_register_widgets' );
