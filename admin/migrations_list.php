<?php 
wp_enqueue_script("jquery");

$current_url = $_SERVER['REQUEST_URI']; 
  preg_match( "/wp-admin\/network/" , $current_url , $matches );
  if (empty($matches)) $url_string = "/wp-admin/admin.php?page=migrations-handle";
  else $url_string = "/wp-admin/network/admin.php?page=migrations-handle";
  
?>
<div class="wrap">
<div id="icon-options-general" class="icon32"></div>
<h2>
	Migrations
	<a class='button-secondary' href='
		<?php 
			echo get_blog_option( get_current_blog_id(), 'site_url');
			if(empty($matches)) echo "/wp-admin/admin.php?page=add-new-handle";  //if in network, set add new url to the network one.
			else echo "/wp-admin/network/admin.php?page=add-new-handle";
		?>
    ' title='Add New'>Add New</a>
    <a class='button-secondary execute' name="<?php echo get_blog_option( get_current_blog_id(),'site_url');?>/?migrate=1&action=migrate&backend=true" href="#adminFrameAnchor" title='Run All'>Run All</a>
</h2>
<h3>
	Current State: <?php echo get_blog_option( get_current_blog_id() , 'migration_state'); ?>
</h3>
<h4 style="color:#AAAAAA;">Grey migrations are up to date</h4>


<table class="widefat">
<thead>
    <tr>
        <th>Name</th>
        <th>Date</th>
        <th>State</th>
        <th>Actions</th>
    </tr>
</thead>
<tfoot>
    <tr>
    <th>Name</th>
    <th>Date</th>
    <th>State</th>
    <th>Actions</th>
    </tr>
</tfoot>
<tbody>
<?php foreach( array_reverse($migrations) as $key => $migration ): ?>
   <tr>
     <td class="name"<?php if( strtotime($migration['date']) <= strtotime(get_blog_option( get_current_blog_id() , 'migration_state'))) echo " style='color: #AAAAAA;'"; ?>><?php echo $migration['name']; ?></td>
     <td>
	 	<?php echo $migration['formatted_date']; ?>
     </td>
     <td > <?php echo $migration['date']; ?></td>
     <td>
     	<a  class='button-secondary execute'  name='<?php echo get_blog_option(get_current_blog_id(), 'site_url');?>/?migrate=1&action=migrateSingle&file=<? echo $migration['file']?>&id=<?php echo get_current_blog_id();?>' href="#adminFrameAnchor" title='Run'>Run</a>
        <a  class='button-secondary rename' href='#' title='Rename'>Rename</a>
        <form style="display:none;" class="rename-form" method="POST" action="">
        	<input class="fname" size="20" type="text" name="fname" value="" />
        	<input class='button-primary' type='submit' name='Save' value='<?php _e('OK'); ?>' id='submitbutton' />
		</form>
        <a class='button-secondary delete' name="<?php echo $migration['file']?>" href='#' title='Delete'>Delete</a>
     </td>
   </tr>
   <?php endforeach; ?>
</tbody>
</table>

<div id="migrations-report-wrap" style="display:none;">
    <h3 id="reload" style="display:none;">
        Page must be reloaded to see changes from migrating
        <a class='button-secondary' href="<?php echo $current_url; ?>" >Reload</a>
    </h3>
    <table class="widefat">
        <thead>
            <th>Migrations Report</th>
        </thead>
        <tbody>
            <tr>
                <td>
                    <a name="adminFrameAnchor">
                        <iframe id="adminFrame" src="" width="100%" height="100%" >Your browser does not support iframes. Output from migrations scripts will not be shown</iframe>
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function(){
		 
				
		jQuery('.rename').click(function(){
			jQuery(this).next().css( 'display' , 'inline');
		});
		
		
		jQuery('.rename-form').submit(function(e){
			e.preventDefault();
			var newName = jQuery(this).parent().find('.fname').val();
			var oldName = jQuery(this).next().attr('name');
			
			var url ="<?php echo get_blog_option( get_current_blog_id() ,'siteurl');?>/?migrate=1&action=renameMigration&file=" + oldName + "&newname=" + newName;
			jQuery('#reload').css('display' , 'block');
			jQuery('#migrations-report-wrap').css( 'display' , 'block');
			jQuery('#adminFrame').attr('src' , url);
			jQuery(this).css("display" , "none");
			document.location.href = "#adminFrameAnchor";
			return false;
		});
	
	   jQuery('.delete').click(function(){
		  
		  var file = jQuery(this).attr('name');
		  if( confirm("Delete File?") ) document.location.href="<?php echo get_blog_option( get_current_blog_id() ,'siteurl');?>/?migrate=1&action=deleteMigration&file=" + file + "&backend=true$&callingurl=<?php  echo get_bloginfo('siteurl') .$url_string?>";
	  });
	  
	  jQuery(".execute").click(function(){
		jQuery('#reload').css('display' , 'block');
	  	var url = jQuery(this).attr('name');
		jQuery('#adminFrame').attr('src' , url);
		jQuery('#migrations-report-wrap').css('display' , 'block');
	  
	  });
  });
  
</script>
			


