
<div class="wrap">

<div id="icon-options-general" class="icon32"></div>
<h2>
	Add New
</h2>
<h4>Use underscores ( _ ) instead of scpaces ***</h4>

<form id="new-form" method="POST" action="">
   <label>File Name</label>
        <input id="fname"  type="text" size="20" name="fname" value="" />
        
        <input class='button-primary' type='submit' name='Save' value='<?php _e('Create File'); ?>' id='submitbutton' />
</form>



</div>


<?php $current_url = $_SERVER['REQUEST_URI']; 
	preg_match( "/wp-admin\/network/" , $current_url , $matches );
	if (empty($matches)) $url_string = "/wp-admin/admin.php?page=migrations-handle&backend=true";
	else $url_string = "/wp-admin/network/admin.php?page=migrations-handle&backend=true";
?>

<script type="text/javascript">

	jQuery(document).ready(function(){
			jQuery('#new-form').submit(function(e){
				e.preventDefault();
				var name = jQuery('#fname').val();
				document.location.href = "<?php  echo get_bloginfo('siteurl');?>/?migrate=1&action=new&name=" + name + 
				"&callingurl=<?php  echo get_bloginfo('siteurl') . $url_string?>";
				return false;
			});
	});
</script>

