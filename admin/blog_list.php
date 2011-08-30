<?php
global $wpdb;
$blogs = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
wp_enqueue_script("jquery");
$current_url =  $_SERVER['REQUEST_URI']; 
?>
<div class="wrap">

<div id="icon-options-general" class="icon32"></div>
<h2>
	Blog List
    <a class='button-secondary execute' name='<? echo get_blog_option( get_current_blog_id(), 'siteurl');?>/?migrate=1&action=migrateAll' href="#adminFrameAnchor" title='Migrate All'>Migrate All</a>
</h2>

<table class="widefat">
<thead>
    <tr>
        <th>Blog Name</th>
        <th>State</th>
        <th>Actions</th>
    </tr>
</thead>
<tfoot>
    <tr>
    <th>Blog Name</th>
    <th>State</th>
    <th>Actions</th>
    </tr>
</tfoot>
<tbody>
<?php foreach( $blogs as $blog ):  
switch_to_blog($blog);?>
   <tr>
     <td>
	 	<a href="<?php echo get_blog_option( $blog , 'siteurl');?>/wp-admin/admin.php?page=migrations-handle" title="<?php echo get_option('blogname');?> Migration List">
			<?php echo get_option('blogname'); ?>
        </a>
     </td>
     <td ><?php echo get_option('migration_state') ;?></td>
     <td>
     	<a  class='button-secondary execute' name='<? echo get_blog_option( get_current_blog_id() ,'siteurl');?>/?migrate=1&action=migrate&id=<?php echo $blog;?>' href="#adminFrameAnchor" title='Migrate'>Migrate</a>
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
	  
	  jQuery(".execute").click(function(){
		jQuery('#reload').css('display' , 'block');
		jQuery('#migrations-report-wrap').css('display' , 'block');
	  	var url = jQuery(this).attr('name');
		jQuery('#adminFrame').attr('src' , url);
	  
	  });
  });
  
</script>