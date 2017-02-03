<?php 
/*
* Meta Box
*/

function project_add_meta_box() {
	add_meta_box('productGallery', __( 'Gallery', 'ABM Water' ), 'product_gallery_callback', 'product', 'side', 'low'); //product is a post type, so you need to change it with your post name, also 'ABM Water' is theme name
}
add_action( 'add_meta_boxes', 'project_add_meta_box' );


/*
* Gallery Meta 
*/
if(!function_exists('product_gallery_callback')){
	function product_gallery_callback($post){
		// Add a nonce field so we can check for it later.
		wp_nonce_field( 'product_meta_box', 'product_meta_box_nonce' );
		wp_nonce_field( basename( __FILE__ ), 'prfx_nonce' );
	    $prfx_stored_meta = get_post_meta( $post->ID );

	    $gallery 	= get_post_meta( $post->ID, 'gallery', true );

	    $g_prev 	= '';
	    $count 		= 1;
	    if(!empty($gallery)){
	    	$exGallery = explode(',', $gallery);
	    	foreach($exGallery as $g){
	    		$mr = ($count%4 == 0)?'style="margin-right:0;"':'';
	    		$g_prev .= '<li '.$mr.' class="image"><img src="'.wp_get_attachment_thumb_url($g).'"/><ul class="actions"><li><a data-delete="'.$g.'" class="delete tips" href="#"><div alt="f153" class="dashicons dashicons-dismiss"></div></a></li></ul></li>';
	    		//$g_prev .= '<div class="g_img"><img style="max-width:100%;" src="'.wp_get_attachment_thumb_url( $g ).'"/></div>';
	    		$count++;
	    	}
		}

	    echo '<div id="galleryPrivew"><ul class="galleryPriview">'.$g_prev.'</ul></div>';
		echo '<input id="gallery" type="hidden" value="'.$gallery.'" name="gallery" />';
		if(empty($gallery)){
			echo '<a id="addGalleryImage" href="javascript:void(0)" style="color:#00a0d2;" >Set Gallery Image</a>';	
		}else{
			echo '<a id="editGalleryImages" href="javascript:void(0)" style="color:#00a0d2; display:block; margin-top:10px;" >Edit Gallery Image</a>';	
		}

		?>
		<script type="text/javascript">
	    	jQuery(document).ready(function($){
	    		$(document.body).on('click', '#addGalleryImage, #editGalleryImages', function(e){
	    		//$('#bannerImgButton').click(function(e) {
				var mediaUploader;
				e.preventDefault();
				// If the uploader object has already been created, reopen the dialog
				  if (mediaUploader) {
				  mediaUploader.open();
				  return;
				}
				// Extend the wp.media object
				mediaUploader = wp.media.frames.file_frame = wp.media({
				  title: 'Choose Banner Image',
				  button: {
				  text: 'Choose Banner Image'
				}, multiple: true });
			 
				// When a file is selected, grab the URL and set it as the text field's value
				mediaUploader.on('select', function() {
				  attachmentdrawing = mediaUploader.state().get('selection').toJSON();
				  var imgId = [];
		  		  var imgUrl = '';
		  		  var count = 1;
				  $.each(attachmentdrawing, function(index, value){
				  	imgId.push(value['id']);
				  	$mr = (count%4 == 0)?'style="margin-right:0;"':'';
				  	console.log(count%4);
				  	
				  	imgUrl += '<li '+$mr+' class="image"><img src="'+value['sizes']['thumbnail']['url']+'"/><ul class="actions"><li><a data-delete="'+value['id']+'" class="delete tips" href="#"><div alt="f153" class="dashicons dashicons-dismiss"></div></a></li></ul></li>';
				  	console.log(value['id']);	
				  	count++;
				  });
				  
				  $('#gallery').val(imgId.join(','));
				  $('#galleryPrivew').html('<ul class="galleryPriview">'+imgUrl+'</ul>');
				});
				// Open the uploader dialog
				mediaUploader.open();

				$(this).attr('id', 'editGalleryImages');
	    		$(this).text('Edit Banner Image');
			  });

	    	// Delete Gallery Items
	    	$(document.body).on('click', 'ul.galleryPriview li.image ul.actions li a', function(){
	    		$allData 	= $('input#gallery').val();
	    		$delete 	= $(this).data('delete');
	    		$finalafterDelete = $allData.split($delete+',').join('').split($delete).join('').replace(/,\s*$/, "");
	    		$('input#gallery').val($finalafterDelete);
	    		$(this).closest('li.image').remove();
	    		return false;
	    	});
	    	}); // End Document Ready 
	    </script>
		<?php
	}
}


/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function save_meta_box_data( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['product_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['product_meta_box_nonce'], 'product_meta_box' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	// Sanitize user input.
	$my_data_gallery = sanitize_text_field( $_POST['gallery'] ); //gallery



	// Update the meta field in the database.
	update_post_meta( $post_id, 'gallery', $my_data_gallery ); //gallery
}
add_action( 'save_post', 'save_meta_box_data' );
