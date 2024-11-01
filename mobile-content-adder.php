<?php


add_filter('manage_edit-msdav_content_columns','msdav_column_headers');

//register custom columns data

add_filter('manage_msdav_content_posts_custom_column','msdav_column_data',1,2);

//advanced custom fields settings 

add_filter('acf/settings/path', 'msdav_acf_settings_path');
add_filter('acf/settings/dir', 'msdav_acf_settings_dir');
add_filter('acf/settings/show_admin', 'msdav_acf_show_admin');
if( !defined('ACF_LITE') ) define('ACF_LITE',true);

//register custom menus

add_action('admin_menu', 'msdav_admin_menus');

//load js in the admin area

add_action('admin_enqueue_scripts', 'msdav_admin_scripts'); 

//add added content to the existing content 

add_filter('the_content', 'msdav_add_to_content',20);

//add added content to the footer 

add_action('wp_footer', 'msdav_add_to_footer');


/* 2. FILTERS */

//creating custom columns header titles

function msdav_column_headers ( $columns ) {
    
    $columns = array ( 
        'cb' => '<input type="checkbox" />',
        'title' => __('Added Content Title'),
        'position' => __('Position'),
        'included' => __('Included In'),
        'customclass' => __('Custom Class')
        );
    
    return $columns;
    
}

//creating return text for custom columns  

function msdav_column_data( $column, $post_id ) {
	
	$output = '';
	
	switch( $column ) {
            
        case 'included':
			// get the included in data 
			$included = get_field('msdav_include', $post_id );
            
            $allIncluded = implode(", ", $included);
            
			$output .= $allIncluded;
			break;
		
		case 'position':
			// get the position data 
			$position = get_field('msdav_position', $post_id);
            
            $allPosition = implode(", ", $position);
            
            //fix position data for footer 
            if ($allPosition === 'FixedPosition') {
                $allPosition = 'Footer';
            }
            //fix position data for Below Content 
            if (strpos($allPosition, 'BelowContent') !== false) {
                $allPosition = str_replace("BelowContent", "Below Content", $allPosition);
                
                
            }
            
            $output .= $allPosition;
			break;
            
        case 'customclass':
			// get the class data if exists 
			$scaclassWS = get_field('msdav_class', $post_id );
            $scaclass = str_replace(' ', '', $scaclassWS);
            
            if (strlen($scaclass)>0) {
                $output .= $scaclass;
            } else {
                $output .= '<i>None</i>';
            }
                
			break;
		
	}
	
	// echo the output
	echo $output;
	
}

//custom plugin admin menus 

function msdav_admin_menus() {
	
		$top_menu_item = 'msdav_dashboard_admin_page'; 
	    
	    add_menu_page( '', 'Added Content', 'manage_options', 'msdav_dashboard_admin_page', 'msdav_dashboard_admin_page', 'dashicons-welcome-add-page' );
    
	    add_submenu_page( $top_menu_item, '', 'Dashboard', 'manage_options', $top_menu_item, $top_menu_item );
	    
	    add_submenu_page( $top_menu_item, '', 'Added Content', 'manage_options', 'edit.php?post_type=msdav_content' );
	    
	    add_submenu_page( $top_menu_item, '', 'Add New', 'manage_options', 'post-new.php?post_type=msdav_content' );
    
}


/* 3. EXTERNAL SCRIPTS */

//include ACF - the script will only run if the ACF plugin is NOT already installed as defined in the acf.php on line 5 

include_once(plugin_dir_path(__FILE__) . 'lib/advanced-custom-fields/acf.php'); 

//include js and css for admin menus

function msdav_admin_scripts() {
    
    wp_register_script('simple-content-adder-js', plugins_url('/js/simple-content-adder.js',__FILE__),array('jquery'),'',true);
    
    wp_enqueue_script('simple-content-adder-js');
    
    wp_register_style('simple-content-adder-css', plugins_url('/css/simple-content-adder.css',__FILE__));
    
    wp_enqueue_style('simple-content-adder-css');
    
}

/* 4. ACTIONS */    

//adds added content to posts/pages 

function msdav_add_to_content ( $content ) {
         
         $content_query = new WP_Query( 
			array(
				'post_type' => 'msdav_content',
				'published' => true,
				'orderby'=>'post_date',
				'order'=>'DESC',
			)
		);
    
        //Set variables which will store added content
         
        $belowPostContent = ''; 
        $abovePostContent = ''; 
        $belowPageContent = '';
        $abovePageContent = '';
        $belowPageContentFront = '';
        $abovePageContentFront = '';
    
            //content added to the footer has a fixed position thus below/above content is not required 
    
        global $FooterContent;
        $FooterContent = '';
	
		
	// IF content query isset and query returns results
	if( isset($content_query) && $content_query->have_posts() ):
		
		// loop over results
		while ($content_query->have_posts() ) : 
		
			// get the post object
			$content_query->the_post();
			
            //get added content posts ids 
			$post_id = get_the_ID();
         
            //get and sort the field data from each added content post 
         
            $addedContentContent = get_field( "msdav_content", $post_id );
         
            $addedContentPosition = get_field( "msdav_position", $post_id );
    
            $addedContentInclude = get_field( "msdav_include", $post_id ); 
    
            $addedContentClassWS = get_field( "msdav_class", $post_id ); 
    
            $addedContentFront = get_field( "msdav_front", $post_id );
    
    //strip the whitespaces to avoid empty class names 
    
            $addedContentClass = str_replace(' ', '', $addedContentClassWS);
    
    //define for which post types the added content is meant 
    
            foreach ($addedContentInclude as $inc) {
             switch ($inc) {
                 case 'Posts':
                     
                     //if POSTS define what goes above the content and what below the content 
                     
                     foreach ($addedContentPosition as $pos) {
                        switch ($pos) {
                            case 'BelowContent':
                                //Check for class
                                if (strlen($addedContentClass)>0) {
                                    $belowPostContent .= '<div class="'. $addedContentClass . '">' . $addedContentContent . '</div>'; } else {
                                    $belowPostContent .= '<div>' . $addedContentContent . '</div>';
                                }
                                
                            break;
                            case 'Above Content':
                                //Check for class
                                if (strlen($addedContentClass)>0) {
                                    $abovePostContent .= '<div class="'. $addedContentClass . '">' . $addedContentContent . '</div>'; } else {
                                    $abovePostContent .= '<div>' . $addedContentContent . '</div>';
                                }
                           
                                break;
                        }
                     } 
                     break;
                     
                 case 'Pages':
                     
                     //if PAGES define what goes above the content and what below the content 
                     
                      foreach ($addedContentPosition as $pos) {
                        switch ($pos) {
                            case 'BelowContent':
                                //Check for class
                                if (strlen($addedContentClass)>0) {
                                    $belowPageContent .= '<div class="'. $addedContentClass . '">' . $addedContentContent . '</div>'; } else {
                                    $belowPageContent .= '<div>' . $addedContentContent . '</div>';
                                }
                                
                                //front page
                                
                                if ($addedContentFront[0] == 'Yes') {
                                    $belowPageContentFront .= $addedContentContent;
                                    
                                };
                            
                                break;
                            
                            case 'Above Content':
                                //Check for class
                                if (strlen($addedContentClass)>0) {

                                    $abovePageContent .= '<div class="'. $addedContentClass . '">' . $addedContentContent . '</div>'; } else {
                                    $abovePageContent .= '<div>' . $addedContentContent . '</div>';
                                }
                                
                                //front page
                                
                                if ($addedContentFront[0] == 'Yes') {
                                    $abovePageContentFront .= $addedContentContent;
                                    
                                };
                            
                                break;
                        }
                     } 
                     break;
                     
                     case 'Footer':
                     
                     //if FOOTER there is no need to define what goes above the content or below the content   
                     
                      foreach ($addedContentPosition as $pos) {
                                //Check for class
                                if (strlen($addedContentClass)>0) {
                                    $FooterContent .= '<div class="'. $addedContentClass . '">' . $addedContentContent . '</div>'; } else {
                                    $FooterContent .= '<div>' . $addedContentContent . '</div>';
                                }
                            
                     } 
                     break;
                     
                    } 
                }  
		
		endwhile;
	
	endif;
	
	// reset wp query/postdata
	wp_reset_query();
	wp_reset_postdata();
         
    //append added content to content 
    
    //append to posts 
    
    if (is_singular( 'post' ) and !is_front_page() and in_the_loop() and is_main_query()) {
        
        $content = $abovePostContent . $content . $belowPostContent;
        return $content;
        
    //append to pages 
        
    } elseif (is_singular( 'page' ) and !is_front_page() and in_the_loop() and is_main_query()) {
        
        $content = $abovePageContent . $content . $belowPageContent;
        return $content;
        
    //append to front page 
        
    } elseif (is_front_page() and in_the_loop() and is_main_query()) {
        
        $content = $abovePageContentFront . $content . $belowPageContentFront;
        return $content;
        
    } else { 
        
        return $content;
        
    }    
 
}

//add added content to the footer 

function msdav_add_to_footer () {

    global $FooterContent;
    
    echo $FooterContent;
    
} 

/* 5. CUSTOM POST TYPES */

//custom fields for add new added content

include_once( plugin_dir_path( __FILE__ ) . 'cpt/sca-fields.php');


/* 6. ADMIN PAGES */ 

//dashboard content 

function msdav_dashboard_admin_page() {
	
	
	$output = '
		<div class="wrap">
        
        <div class="sca-adder">
			
			<h1>Simple Content Adder</h2>
			
			 <p><b>Hi there and thank you for installing the Simple Content Adder!</b></p>
             
             <p>This plugin allows you to easily add custom content to your posts, pages and to the footer of your website, without the need to update each post or page. The content is created through the Wordpress text editor and therefore you can easily insert not only text but also images, videos etc.</p> 
             
            <h3>How to start?</h3>
                <ul>
                    <li>1. Click Add New under the Added Content in the left sidebar.</li>
                    <li>2. Enter the title (only you will see the title). </li>
                    <li>3. Select the position for custom content – available options are above content, below content and footer. </li>
                    <li>4. Under Include In select where do you want your content to show – available options are posts, pages and everywhere. Please note that everywhere option is only compatible with the footer position. </li>
                    <li>5. Added Content Class allows you to give the custom content div a class for later CSS styling. </li>
                    <li>6. Add your cool content and hit the publish button.  </li>
                    </ul>
                    
            <h3>What can you do with this plugin?</h3>
                <ul>
                    <li>1. Show disclaimers</li>
                    <li>2. Show banners</li>
                    <li>3. Show author info </li>
                    <li>4. Show ads (as long as they do not contain JavaScript)</li>
                    <li>5. Display other cool things by your wishes</li>
                    </ul>
                    
            <h3>Support and further development</h3> 
<p>For support as well as for further development ideas please use the support forum. </p> 

		
        </div> 
		</div>
	';
	
	echo $output;
	
}