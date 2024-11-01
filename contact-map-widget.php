<?php

define( 'phone-icon-url', plugins_url( 'images/phone.png', __FILE__ ) );
define( 'email-icon-url', plugins_url( 'images/email.png', __FILE__ ) );

class gmwm1_widget extends WP_Widget {

	function __construct() {
	parent::__construct('gmwm1_widget', __('Contact Info Widget', 'gmwm1_widget_domain'), 

	array( 'description' => __( 'This widget is used to display contact information with map.', 'gmwm1_widget_domain' ), ) );
}
	
public function widget( $args, $instance ) {
$title = apply_filters( 'widget_title', $instance['title'] );

echo $args['before_widget'];
if ( ! empty( $title ) )
echo $args['before_title'] . $title . $args['after_title'];
?>

<div class="contact-info-wrap">

<?php if($instance['lat'] && $instance['lng'] ): 
      wp_print_scripts( 'google-map-script-api' );
?>
<div id="map-canvas"></div>
	<script type="text/javascript">
	var infowindow = new google.maps.InfoWindow({
			    content: '<?php echo $instance['address'] ; ?>'
			    });
	
	google.maps.event.addDomListener( window, 'load', gmwm1_gmaps_results_initialize );
	function gmwm1_gmaps_results_initialize() {

			var locations = new google.maps.LatLng(<?php echo $instance['lat']; ?>,<?php echo $instance['lng']; ?>);
			var	map = new google.maps.Map(document.getElementById('map-canvas'), { 
				zoom: 13, 
				center: locations, 
				mapTypeId: google.maps.MapTypeId.ROADMAP 
			});
		
	 
			var marker = new google.maps.Marker({
				position: locations,
				visible: true
			});
			marker.setMap(map);
			google.maps.event.addListener(marker, 'click', (function(marker) {
			  return function() {
				infowindow.open('', marker);
			  }
			})(marker));
		
	}
	</script>

<?php endif;?>

<?php if($instance['address']): ?>
<div class="address"><?php echo $instance['address']; ?></div>
<?php endif;?>

<?php if($instance['fax']): ?>
<div class="fax"><span>Fax :</span> <?php echo $instance['fax']; ?></div>
<?php endif;?>

<?php if($instance['phone']): ?>
<div class="phone"><span><img src="<?php echo PHONE_PLUGIN_PATH; ?>"></span> <?php echo $instance['phone']; ?></div>
<?php endif;?>

<?php if($instance['email']): ?>
<div class="email"><span><img src="<?php echo EMAIL_PLUGIN_PATH; ?>"></span> <a href="mailto:<?php echo $instance['email']; ?>" ><?php echo $instance['email']; ?></a></div>
<?php endif;?>

</div><!--End contact-info-wrap-->
<?php
  echo $args['after_widget'];
}

// Backend 
public function form( $instance ) {
if ( isset( $instance[ 'title' ] ) ) {
$title = $instance[ 'title' ];
}else {
$title = __( 'Contact Info', 'gmwm1_widget_domain' );
} ?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>

<label><strong><?php _e( 'Map' ); ?></strong></label>
<p>

<label for="<?php echo $this->get_field_id( 'lat' ); ?>"><?php _e( 'Latitude:' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('lat'); ?>" name="<?php echo $this->get_field_name('lat'); ?>" type="text" value="<?php echo $instance['lat']; ?>" />
</p>

<p>
<label for="<?php echo $this->get_field_id( 'lng' ); ?>"><?php _e( 'Longitude :' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('lng'); ?>" name="<?php echo $this->get_field_name('lng'); ?>" type="text" value="<?php echo $instance['lng']; ?>" />
</p>

<p>
<label for="<?php echo $this->get_field_id( 'address' ); ?>"><?php _e( 'Address:' ); ?></label> 
<textarea class="widefat" id="<?php echo $this->get_field_id('address'); ?>" name="<?php echo $this->get_field_name('address'); ?>" type="text" value="<?php echo $instance['address']; ?>" ><?php echo $instance['address']; ?></textarea>
</p>

<p>
<label for="<?php echo $this->get_field_id( 'phone' ); ?>"><?php _e( 'Phone No:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id('phone'); ?>" name="<?php echo $this->get_field_name('phone'); ?>" type="text" value="<?php echo $instance['phone']; ?>" />
</p>

<p>
<label for="<?php echo $this->get_field_id( 'fax' ); ?>"><?php _e( 'Fax No:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id('fax'); ?>" name="<?php echo $this->get_field_name('fax'); ?>" type="text" value="<?php echo $instance['fax']; ?>" />
</p>

<p>
<label for="<?php echo $this->get_field_id( 'email' ); ?>"><?php _e( 'Email:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id('email'); ?>" name="<?php echo $this->get_field_name('email'); ?>" type="text" value="<?php echo $instance['email']; ?>" />
</p>
<?php }

	public function update( $new_instance, $old_instance ) {
		
	$instance = $old_instance;
	$instance['title'] =  $new_instance['title'];
	$instance['address'] = $new_instance['address'];
	$instance['lat'] = $new_instance['lat'];
	$instance['lng'] = $new_instance['lng'];
	$instance['phone'] = $new_instance['phone'];
	$instance['fax'] = $new_instance['fax'];
	$instance['email'] = $new_instance['email'];

	return $instance;
	  }
	} 
	
// Register and load the widget
function gmwm1_load_widget() {
	register_widget( 'gmwm1_widget' );
}
add_action( 'widgets_init', 'gmwm1_load_widget' );

// Script and css
function gmwm1_load_scripts() {
	wp_register_script( 'google-map-script-api', '//maps.googleapis.com/maps/api/js?sensor=false' );
}
add_action( 'wp_enqueue_scripts', 'gmwm1_load_scripts' );

function gmwm1_css() { ?>
<style type="text/css">
#map-canvas {
	height:   195px;
	border: 2px solid #ccc;
}
.address {
    line-height: 25px;
    margin-top: 10px;
    width: 85%;
}
</style>
<?php }
add_action( 'wp_head', 'gmwm1_css' );
?>