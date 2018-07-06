<?php

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'reduxLoadSocialWidget' ) ) {
	class reduxLoadSocialWidget {
		public $field_id = '';
		public $parent = null;

		public function __construct( $parent, $field_id ) {
			$this->parent   = $parent;
			$this->field_id = $field_id;


			$this->params = array(
				'parent'   => $this->parent,
				'field_id' => $this->field_id
			);

			add_action( 'widgets_init', array( $this, 'load_widget' ), 0 );
		}

		function load_widget() {
			$x = new Extend_WP_Widget_Factory();
			$x->register( 'reduxSocialWidgetDisplay', $this->params );
		}
	}

	class Extend_WP_Widget_Factory extends WP_Widget_Factory {
		function register( $widget_class, $param = null ) {
			$this->widgets[ $widget_class ] = new $widget_class( $param );
		}
	}

	class reduxSocialWidgetDisplay extends WP_Widget {

		public function __construct( $params ) {

			extract( $params );

			$this->parent   = $parent;
			$this->field_id = $field_id;

			$widget_ops = array(
				'classname'   => 'redux-social-icons-display',
				'description' => __( 'Display social media links', 'redux-framework' )
			);

			$control_ops = array(
				'width'   => 250,
				'height'  => 200,
				'id_base' => 'redux-social-icons-display'
			); //default width = 250
			parent::__construct( 'redux-social-icons-display', 'Redux Social Widget', $widget_ops, $control_ops );
		}

		public function widget( $args, $instance ) {
			include_once( 'class.functions.php' );

			extract( $args, EXTR_SKIP );

			$title         = $instance['title'];
			$redux_options = get_option( $this->parent->args['opt_name'] );

			$social_items = $redux_options[ $this->field_id ];

			echo $before_widget;

			if ( $title ) {
				echo $before_title . $title . $after_title;
			}
			?>
			<ul class="redux-social-media-list clearfix">
				<?php
				if ( is_array( $social_items ) ) {
					foreach ( $social_items as $key => $social_item ) {
						if ( $social_item['enabled'] ) {
							$icon       = $social_item['icon'];
							$color      = $social_item['color'];
							$background = $social_item['background'];
							$base_url   = $social_item['url'];
							$id         = $social_item['id'];

							$url = apply_filters( 'redux/extensions/social_profiles/' . $this->parent->args['opt_name'] . '/icon_url', $id, $base_url );

							echo "<li>";
							echo "<a href='" . $url . "'>";
							reduxSocialProfilesFunctions::render_icon( $icon, $color, $background, '' );
							echo "</a>";
							echo "</li>";
						}
					}
				}
				?>
			</ul>
			<?php
			echo $after_widget;
		}

		public function update( $new_instance, $old_instance ) {
			$instance          = $old_instance;
			$instance['title'] = strip_tags( $new_instance['title'] );

			return $instance;
		}

		public function form( $instance ) {
			$defaults = array(
				'title' => __( 'Social', 'redux-framework' ),
			);

			$instance = wp_parse_args( (array) $instance, $defaults );
			?>
			<p>
				<label for="<?php echo $this->get_field_id( "title" ); ?>">
					<?php _e( 'Title', 'redux-framework' ); ?>
					:
					<input class="widefat" id="<?php echo $this->get_field_id( "title" ); ?>"
						   name="<?php echo $this->get_field_name( "title" ); ?>" type="text"
						   value="<?php echo esc_attr( $instance["title"] ); ?>"/>
				</label>
				<label for="redux-social-icons-info">
					<?php
					$tab  = Redux_Helpers::tabFromField( $this->parent, 'social_profiles' );
					$slug = $this->parent->args['page_slug'];

					printf( __( 'Control which icons are displayed and their urls on the %ssettings page%s', 'redux-framework' ), '<a href="' . admin_url( 'admin.php?page=' . $slug . '&tab=' . $tab ) . '">', '</a>' );
					?>
				</label>
			</p>
			<?php
		}
	}
}