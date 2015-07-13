<?php
/*
Plugin Name:Best Team Manager
Plugin URI:http://bestcareerbd.com/plugins/team/team-member/
Description:This Plugin is used for team management System
Version: 1.0
Author:bestcareerbd
Author URI:http://bestcareerbd.com/
*/

Class teamManager{
	function __construct(){
	//setup localization
	load_plugin_textdomain('team-manager',false,dirname(plugin_basename(__FILE__)).'/lang');
	
	//Register admin stylesheets
	add_action('admin_print_styles',array($this,'register_admin_style'));
	
	//Register plugin stylesheets
	add_action('wp_enqueue_scripts',array($this,'register_plugin_style'));
	
	//add custom post 
	add_action('init',array($this,'register_custom_post'));
	add_action('init',array($this,'register_custom_taxonomy'));
	
	//Set up the meta boxes
	add_action('add_meta_boxes',array($this,'add_team_metabox'));
	add_action('do_meta_boxes',array($this,'add_team_metabox_image'));

	add_action('save_post',array($this,'save_team_metabox'));

	//Display team members
	add_shortcode('team',array($this,'team_member_view'));
	
	//Enable widget shortcode 
	add_filter('widget_text', 'do_shortcode');
	
	}
	
	//admin stylesheets
	public function register_admin_style(){
		wp_enqueue_style('team-admin-style',plugins_url( '/css/admin.css', __FILE__ ));
	}
	
	//plugin stylesheets
	public function register_plugin_style(){
		wp_enqueue_style('team-font-awesome',plugins_url( '/css/font-awesome.min.css', __FILE__ ));
		wp_enqueue_style('team-plugin-team',plugins_url( '/css/plugin.css', __FILE__ ));
		wp_enqueue_style('team-plugin-responsive-team',plugins_url( '/css/plugin-responsive.css', __FILE__ ));
	}
	
	
	//add custom post 
	public function register_custom_post(){
		add_theme_support( 'post-thumbnails', array( 'post', 'team' ));
		add_image_size( 'member_img_size', 170, 170);
		add_image_size( 'member_img_size2', 215, 170);
		$labels = array(
			'name'               => _x( 'Team Members', 'post type general name', 'team-manager' ),
			'singular_name'      => _x( 'Team Member', 'post type singular name', 'team-manager' ),
			'menu_name'          => _x( 'Team Members', 'admin menu', 'team-manager' ),
			'name_admin_bar'     => _x( 'Team Member', 'add new on admin bar', 'team-manager' ),
			'add_new'            => _x( 'Add New Member', 'Team Member', 'team-manager' ),
			'add_new_item'       => __( 'Add New Member', 'team-manager' ),
			'new_item'           => __( 'New Member', 'team-manager' ),
			'edit_item'          => __( 'Edit Member', 'team-manager' ),
			'view_item'          => __( 'View Team Member', 'team-manager' ),
			'all_items'          => __( 'All Team Members', 'team-manager' ),
			'search_items'       => __( 'Search Team Member', 'team-manager' ),
			'parent_item_colon'  => __( 'Parent Team Member:', 'team-manager' ),
			'not_found'          => __( 'No Team Member found.', 'team-manager' ),
			'not_found_in_trash' => __( 'No Team Member found in Trash.', 'team-manager' )
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'team_member' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon' =>  plugins_url() . '/Best-Team-Manager/images/paint_brush_color.png' ,//16x16 size
			'supports'           => array( 'title', 'thumbnail')
		);

		register_post_type( 'team', $args );
	}
	
	// register custom taxonomy
	public function register_custom_taxonomy(){
		
		$labels = array(
			'name'              => _x( 'Select Rank', 'taxonomy general name' ),
			'singular_name'     => _x( 'Rank', 'taxonomy singular name' ),
			'search_items'      => __( 'Search Rank' ),
			'all_items'         => __( 'All Ranks' ),
			'parent_item'       => __( 'Parent Rank' ),
			'parent_item_colon' => __( 'Parent Rank:' ),
			'edit_item'         => __( 'Edit Rank' ),
			'update_item'       => __( 'Update Rank' ),
			'add_new_item'      => __( 'Add New Rank' ),
			'new_item_name'     => __( 'New Rank Name' ),
			'menu_name'         => __( 'Rank' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'rank' ),
		);

		register_taxonomy( 'rank', array( 'team' ), $args );
	}
	
	//functions for meta boxes
	public function add_team_metabox(){
	    add_meta_box( 
			'team_member',          //id
			__( 'Add Member Information', 'team-manager'),		//title
			array($this,'team_member_name_display'),	//a reference to the function for rendering the metabox
			'team',					//Where to show
			'normal',				//priority
			'high'				//where the box should be displayed.Here directly under the post editor
		);
		
	}
	//Add Meta Box image
	public function add_team_metabox_image($post_type){
	
	if ( $post_type === 'team' ) {
		//remove original featured image metabox
		remove_meta_box( 'postimagediv', 'team', 'side' );

		//add our customized metabox
		add_meta_box( 'postimagediv', __('Add Profile Picture <span style="color:#555">( Recomanded image size 170x170 for default or circle styles and 215x170 for hover style.)</span>'), 'post_thumbnail_meta_box', 'team', 'normal', 'high' );
	}

	}

	
	function team_member_name_display($post ){
	wp_nonce_field( plugin_basename(__FILE__),'team_nonce');
	
	//input fields		
	//Name
	echo '
	<div class="team_admin_form">
	
	<p><label for="team_member_name1"  class="team_member_title">';
		_e( '<b>Member Name : </b>', 'team-manager' );
	echo '</label> ';
	echo '<input type="text" id="team_member_name1" name="team_member_name1" placeholder="'.__('Member Name','team-manager').'" value="'.esc_attr(get_post_meta($post->ID,'team_member_name',true)).'" size="40"/></p>';

	//Title
	echo '	
	<p><label for="team_member_title1"  class="team_member_title">';
		_e( '<b>Member Title : </b>', 'team-manager' );
	echo '</label> ';
	echo '<input type="text" id="team_member_title1" name="team_member_title1" placeholder="'.__('Member Title','team-manager').'" value="'.esc_attr(get_post_meta($post->ID,'team_member_title',true)).'" size="40"/></p>';
	//Description
	echo '<p><label for="team_member_description1"  class="team_member_title">';
		_e( '<b>Member Description : </b>', 'team-manager' );
	echo '</label> ';
	echo '<textarea name="team_member_description1" id="team_member_description1" placeholder="'.__('Add Description','team-manager').'"cols="60" rows="5">'.esc_attr(get_post_meta($post->ID,'team_member_description',true)).'</textarea></p>';

	//socials
	echo '<h3>Social Links</h3>';
	
	//facebook
	echo '<p><label for="team_member_fb1"  class="team_member_title">';
		_e( '<b>Facebook Profile : </b>', 'team-manager' );
	echo '</label> ';
	echo '<input type="text" id="team_member_fb1" name="team_member_fb1" placeholder="'.__('Facebook Profile','team-manager').'" value="'.esc_attr(get_post_meta($post->ID,'team_member_fb',true)).'" size="40"/></p>';	
	//twitter
	echo '<p><label for="team_member_twitter1"  class="team_member_title">';
		_e( '<b>Twitter Profile : </b>', 'team-manager' );
	echo '</label> ';
	echo '<input type="text" id="team_member_twitter1" name="team_member_twitter1" placeholder="'.__('Twitter Profile','team-manager').'" value="'.esc_attr(get_post_meta($post->ID,'team_member_twitter',true)).'" size="40"/></p>';
	//linkedin
	echo '<p><label for="team_member_linkedin1"  class="team_member_title">';
		_e( '<b>Linkedin Profile : </b>', 'team-manager' );
	echo '</label> ';
	echo '<input type="text" id="team_member_linkedin1" name="team_member_linkedin1" placeholder="'.__('Linkedin Profile','team-manager').'" value="'.esc_attr(get_post_meta($post->ID,'team_member_linkedin',true)).'" size="40"/></p>';
	//youtube
	echo '<p><label for="team_member_youtube1"  class="team_member_title">';
		_e( '<b>Youtube Profile : </b>', 'team-manager' );
	echo '</label> ';
	echo '<input type="text" id="team_member_youtube1" name="team_member_youtube1" placeholder="'.__('Youtube Profile','team-manager').'" value="'.esc_attr(get_post_meta($post->ID,'team_member_youtube',true)).'" size="40"/></p>
	</div>';	
	}
	
	//save metabox
	public function save_team_metabox( $post_id ) {

		// Check if our nonce is set.
		if ( ! isset( $_POST['team_nonce'] ) )
			return $post_id;

		$nonce = $_POST['team_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, plugin_basename(__FILE__) ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
                //     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
	
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */

		// Sanitize the user input.
		$name = sanitize_text_field( $_POST['team_member_name1'] );
		$title = sanitize_text_field( $_POST['team_member_title1'] );
		$description = sanitize_text_field( $_POST['team_member_description1'] );
		$fb = sanitize_text_field( $_POST['team_member_fb1'] );
		$twitter = sanitize_text_field( $_POST['team_member_twitter1'] );
		$linkedin = sanitize_text_field( $_POST['team_member_linkedin1'] );
		$youtube = sanitize_text_field( $_POST['team_member_youtube1'] );

		// Update the meta field.
		update_post_meta( $post_id, 'team_member_name',$name);
		update_post_meta( $post_id, 'team_member_title',$title);
		update_post_meta( $post_id, 'team_member_description',$description);
		update_post_meta( $post_id, 'team_member_fb',$fb);
		update_post_meta( $post_id, 'team_member_twitter',$twitter);
		update_post_meta( $post_id, 'team_member_linkedin',$linkedin);
		update_post_meta( $post_id, 'team_member_youtube',$youtube);
	}
	
	//display view 
	//query custom post with shortcode
	public function team_member_view($atts){
	
			extract ( shortcode_atts ( array (
				'style' => 'default',
				'rank' => '',
				'show_image_no' => -1,
			), $atts, 'team') );
			
			$q = new WP_Query(
			array('posts_per_page' => $show_image_no, 'post_type' => 'team','rank'=> $rank)
			);		
				
	
		$list = '<div id="team_member_area">
					<div class="'.$style.'-style">';
		while($q->have_posts()) : $q->the_post();
		global $post;		
			if($style==='hover'){
			$image_src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID),'member_img_size2' );        
			$list .= '<div class="team_profile_wrap" style="border-color:none;">
						<div class="team_profile_pic" style="background:url('.$image_src[0].') no-repeat 0 0">
							<div class="team_profile_desc">
								<p>'.esc_attr(get_post_meta(get_the_ID(),'team_member_description',true)).'</p>
							</div>
						</div>
						<div class="team_profile_ttl">
							<p class="member_name">'.esc_attr(get_post_meta(get_the_ID(),'team_member_name',true)).'</p>
							<p class="member_rank"><i class="fa fa-chevron-circle-right"></i> '.esc_attr(get_post_meta(get_the_ID(),'team_member_title',true)).'</p>
							<div class="member_socials">
								<ul>
									<li><a target="_blank" href="'.esc_attr(get_post_meta(get_the_ID(),'team_member_linkedin',true)).'"><i class="fa fa-linkedin"></i></a></li>
									<li><a target="_blank" href="'.esc_attr(get_post_meta(get_the_ID(),'team_member_fb',true)).'"><i class="fa fa-facebook"></i></a></li>
									<li><a target="_blank" href="'.esc_attr(get_post_meta(get_the_ID(),'team_member_twitter',true)).'"><i class="fa fa-twitter"></i></a></li>
									<li><a target="_blank" href="'.esc_attr(get_post_meta(get_the_ID(),'team_member_youtube',true)).'"><i class="fa fa-youtube"></i></a></li>
								</ul>
							</div>
						</div>
					</div>	'; 
			}else{
				$image_src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID),'member_img_size' );        
				$list .= '<div class="team_profile_wrap" style="border-color:none;">
							<div class="team_profile_pic" style="background:url('.$image_src[0].') no-repeat 0 0">
								<div class="team_profile_desc">
									<p>'.esc_attr(get_post_meta(get_the_ID(),'team_member_description',true)).'</p>
								</div>
							</div>
							<div class="team_profile_ttl">
								<p class="member_name">'.esc_attr(get_post_meta(get_the_ID(),'team_member_name',true)).'</p>
								<p class="member_rank"><i class="fa fa-chevron-circle-right"></i> '.esc_attr(get_post_meta(get_the_ID(),'team_member_title',true)).'</p>
								<div class="member_socials">
									<ul>
										<li><a target="_blank" href="'.esc_attr(get_post_meta(get_the_ID(),'team_member_linkedin',true)).'"><i class="fa fa-linkedin"></i></a></li>
										<li><a target="_blank" href="'.esc_attr(get_post_meta(get_the_ID(),'team_member_fb',true)).'"><i class="fa fa-facebook"></i></a></li>
										<li><a target="_blank" href="'.esc_attr(get_post_meta(get_the_ID(),'team_member_twitter',true)).'"><i class="fa fa-twitter"></i></a></li>
										<li><a target="_blank" href="'.esc_attr(get_post_meta(get_the_ID(),'team_member_youtube',true)).'"><i class="fa fa-youtube"></i></a></li>
									</ul>
								</div>
							</div>
						</div>	'; 
			}
		endwhile;
		$list.= '</div>
			</div>';
		wp_reset_query();
		return $list;

	}

}

function team_menber_init() {	
	new teamManager;	
}

add_action('plugins_loaded', 'team_menber_init');
?>