<?php
/**
 * widgets.php
 *
 * Custom widgets for Easy Digital Downloads.
 *
 * @package     EDD\EDD_WordPress_Plugins\Functions
 * @since       1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) exit;

class EDD_WordPress_Plugins_Widget extends WP_Widget {

	/**
	 * Register the widget
	 */
	public function __construct() {
		parent::__construct(
			'edd_wordpress_plugins_widget',
			'EDD WordPress Plugin Info',
			array(
				'description' => 'Display details for WordPress plugins added through EDD.',
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

		$wordpress_plugin = get_post_meta( $post->ID, '_edd_wordpress_plugin_url', true );

		// Bail!
		if ( ! $wordpress_plugin ) {
			return;
		}

		$data = edd_get_wordpress_plugin_data( $wordpress_plugin );

		// Get the options we should show.
		$added 			= isset( $instance['added'] ) ? $instance['added'] : 1;
		$updated 		= isset( $instance['updated'] ) ? $instance['updated'] : 1;
		$downloaded 	= isset( $instance['downloaded'] ) ? $instance['downloaded'] : 1;
		$rating 		= isset( $instance['rating'] ) ? $instance['rating'] : 1;
		$version 		= isset( $instance['version'] ) ? $instance['version'] : 1;

		// Start outputting the content.
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}

		?>
		<ul class="edd-wordpress-plugin-details">

			<?php if ( $added ): ?>

				<li>
					<span class="edd-detail-name"><?php _e( 'Added: ', 'edd-wordpress-plugins' ); ?></span>
					<span class="edd-detail-info"><?php echo $data['added']; ?></span>
				</li>

			<?php endif; ?>

			<?php if ( $updated ): ?>

				<li>
					<span class="edd-detail-name"><?php _e( 'Updated: ', 'edd-wordpress-plugins' ); ?></span>
					<span class="edd-detail-info"><?php echo $data['last_updated']; ?></span>
				</li>

			<?php endif; ?>

			<?php if ( $downloaded ): ?>

				<li>
					<span class="edd-detail-name"><?php _e( 'Downloaded: ', 'edd-wordpress-plugins' ); ?></span>
					<span class="edd-detail-info"><?php echo $data['downloaded']; ?></span>
				</li>

			<?php endif; ?>

			<?php if ( $rating ): ?>

				<li>
					<span class="edd-detail-name"><?php _e( 'Rated: ', 'edd-wordpress-plugins' ); ?></span>
					<span class="edd-detail-info"><?php echo $data['rating']; ?></span>
				</li>

			<?php endif; ?>

			<?php if ( $version ): ?>

				<li>
					<span class="edd-detail-name"><?php _e( 'Version: ', 'edd-wordpress-plugins' ); ?></span>
					<span class="edd-detail-info"><?php echo $data['version']; ?></span>
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

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'edd-wordpress-plugins' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<p>
			<input <?php checked( $added ); ?> id="<?php echo esc_attr( $this->get_field_id( 'added' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'added' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'added' ) ); ?>"><?php _e( 'Show Date Added', 'edd-wordpress-plugins' ); ?></label>
		</p>

		<p>
			<input <?php checked( $updated ); ?> id="<?php echo esc_attr( $this->get_field_id( 'updated' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'updated' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'updated' ) ); ?>"><?php _e( 'Show Date Updated', 'edd-wordpress-plugins' ); ?></label>
		</p>

		<p>
			<input <?php checked( $version ); ?> id="<?php echo esc_attr( $this->get_field_id( 'version' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'version' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'version' ) ); ?>"><?php _e( 'Show Version Number', 'edd-wordpress-plugins' ); ?></label>
		</p>

		<p>
			<input <?php checked( $rating ); ?> id="<?php echo esc_attr( $this->get_field_id( 'rating' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'rating' ) ); ?>" type="checkbox" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'rating' ) ); ?>"><?php _e( 'Show Rating', 'edd-wordpress-plugins' ); ?></label>
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
		$instance['added']  		= ! empty( $new_instance['added'] ) 	? 1 : 0;
		$instance['updated'] 		= ! empty( $new_instance['updated'] ) 	? 1 : 0;
		$instance['rating']       	= ! empty( $new_instance['rating'] ) 	? 1 : 0;
		$instance['version']  		= ! empty( $new_instance['version'] )  	? 1 : 0;
		return $instance;
	}

}

/**
 * Register the widgets
 *
 * @package     EDD\EDD_WordPress_Plugins\Functions
 * @since       1.0.0
 */
function edd_wordpress_plugins_register_widgets() {
	register_widget( 'edd_wordpress_plugins_widget' );
}
add_action( 'widgets_init', 'edd_wordpress_plugins_register_widgets' );
