Strategery Migrations
=====================

Contributors: Gabriel Somoza (me@gabrielsomoza.com) , Joseph Fitzgibbons(jfitzy87@gmail.com)  
Donate link: http://usestrategery.com  
Tags: migration , multisite  
Requires at least: 3.0  
Tested up to: 3.0  
Stable tag: 1.0  

Easily run and manage migrations scripts to assist in updating blogs on your site.


Description
-----------

This plugins was designed to speed up and manage the process of creating a running migrations scripts. Migrations scripts are used to edit content on a blog to blog basis by running php files which manipulate your Wordpress database. This could be adding or removing a page or post to certain blogs. Strategery Migrations also provides an interface to manage your migrations scripts. You can view a list of all migrations files and see a timestamp indicating when a migration file was created. You can see individual blogs' migration state so that you can tell what files have been run or if a certain blog has been migrated. You can look at a list of all your blogs and see what their migration states are compared to each other.

This plugin was made for Wordpress implementations using a multisite network with sib-directories.

Installation
------------

1. Download and copy the Strategery Migrations folder into ‘/wp-content/plugins/’ directory
2. Network activate the plugin in the ‘Plugins’ menu in the network administration panel

Frequently Asked Questions
--------------------------

### Why doesn’t the migration report have anything in it?

IE and Safari generate a file that you must open to view the migration report. There should be a prompt after running a migration where you can open the file.

### What type of Wordpress implementation is this plugin compatible with?

To use this plugin you must have it setup with a multisite network using sub-directories. Not sub-domains.

### I made a new migration file using the Add New form. Where is that file located? =

Migration files are located in ‘/wp-content/plugins/strategery-migrations/migrations/’.

### I created a new migration file... Now what? =

Open that file you created and put any code you need in the up method. Put any code you need to undo whatever the up method does in the down method. (The down method is optional)

### I’m trying to run a migration script and it says it’s already up to date.

The plugin will only run migration scrips with a timestamp later than the current blogs migration state timestamp. If you run a single migration in a blog and it has a later timestamp than another script that was made earlier it will make that earlier script unable to be executed. Only run scripts  from earliest to latest if you run them individually. If you have multiple scripts tat need to be run, it is better to run them all at once to avoid mistakes.

Screenshots
-----------

1. ![screenshot-1.png](/screenshot-1.png "Migrations List")This image shows an example migrations list for a certain blog. The schedule_page.php is black because it is newer than the current blog’s state indicating that it hasn’t been run. All files with an older timestamp are grey to show that they are migrations from an earlier state. Also note the buttons at the top and for each file which will run a certain action.
2. ![screenshot-2.png](/screenshot-2.png "Migrations Report")his image shows the report that appears after running a migration script. Notice the reload button that appears right above it. To see the updated migrations list you must reload the page after running a migration script. If you are using  IE or Safari this report will be empty and a file will be generated with the migration report. View this file to view the report.

Usage Instructions
------------------

### Creating a Migration Script

1. Click on the Add New link in the Migrations admin options tab
2. Enter a the script’s filename using underscores instead of spaces (file_name)
3. Click Create File

You are then redirected to the list or migration files.

At this point the file has been generated and exists in the ‘/strategery-migrations/migrations/’ folder in your ‘plugins’ directory.  

The filename is broken up into 2 parts. The first part before the dash(-) is the timestamp in YYYYMMDDHHMMSS format. This number is used to compare the sate of a blog compared to when this file was created. When a migration is run and completed, it will update the blog that ran the migration to reflect its new state. The sate for a blog is the same timestamp of the most recent migrations run on it. Only migrations with a later timestamp than the blog’s state timestamp will be run. The second part of the filename is the actual name of the migration script file. The format uses underscores as spaces and will be read by the plugin to create the classname in the file using camel case.

Open the file that was created and start coding the migration script. You will notice that the file contains a class that extends the main migration class. Each migration script defines a new class with two methods. The up method is used for creating pages or posts. It is the creation method. The down method is used for undoing the up method. It Is a way to revert the migration. This method is optional.

The methods are run on a blog to blog basis and work within the context of each blog. To get the ID of the current blog, for example, you can use the get_current_blog_id() function.

### Models

Models are abstracts that are used to query your database. Models are used to save or delete that model type into your database. A model could be a post or a page, etc. To use models you need to make use of the getModel function.  

	$this->getModel( $type , $data );  

$type - A string that picks wether the model is a page or a post, etc.  

$data - An array that contains information related to the model type. 
 
_Example_

	$data = array('post_type' => 'page' , 'post_name' => 'menu-add' , 'post_title' => 'Add Menu Item' )

	$post = $this->getModel('Post', $data );

You can also use the shortcut getPost($data).

The commands above query your database for existing posts that match the given data in the current blog and return an object of the post. You can then access that post’s information using the object. For example $post->ID will return the id of the post.

To create a page you use the same function call and add ->save() to the end.

	$post = $this->getModel('Post', array('post_type' => 'page' , 'post_name' => 'menu-add' , 'post_title' => 'Add Menu Item' ))->save();

The command above queries your database for existing posts that match the given data in the current blog. If there is no match in the database it will create that post in the database and return an object of it. The save() command will not create duplicates.

If you want to delete a given post you simply need to replace save() with delete().

### Your Database

To run queries on your database without using models you have to use $this->db(). It will return the WordPress database. To run an insert for example you would use the following code.
 _Example_

	$this->db()->insert($table, $data);

If you want to insert into each blog’s post table you would set the $table as $this->db()->posts

The command would then look like this

	$this->db()->insert( $this->db()->posts , $data );

### Excluding Pages

This migration plugin comes with a helper class that allows you to remove a page from the navigation menu. Pages that should not appear in your navigation menu can easily be excluded with one line of code. These pages are still reachable and still exist but they will not be seen by users with all your other navigation links.

This exclude functionality requires having another [plugin](http://wordpress.org/extend/plugins/exclude-pages/) active.

To use the helper function you need to pass it a path to helper class you are looking for.

	$this->getHelper($path);

The getHelper function takes a string that contains the path to the specific helper class you are looking for.

	$this->getHelper($path)->method($args);

This is the full call to a helper class method which has the helper function that takes the path to the class you are hooking into. getHelper returns an instance of that class and can be used to execute methods. All helper classes are contained in the ‘/lib/Helpers/Plugins/’ directory. It contains .php files with names in camel case. The class that is defined in each file is named the same as the file name except with underscores separating each word. For the exclude from navigation class, it is contained in the ExcludeFromNav.php file, but the class name is exclude_from_nav. This name is the one you need for the helper function.

Below is an example of how to call the exclude from navigation helper class.

_Example_

	$this->helper('plugins/exclude_from_nav')->exclude($post->ID);

exclude() takes the id of the page being excluded as an argument.

### Logging

While the migration is running, there will be output in the migrations report showing you the progress of your migration. It will let you know if there was an error finding a specific post or if the migration was already done and it is already up to date.

You can add your own logging information inside your up and down methods for each migration script. This could be a simple message saying you did whatever you were trying to do. You do this by using the log() function. It simply echos the string passed to it.

	$this->log($message , $nl);

Above is how it is called. It doesn’t format the string, so newlines and tabs must be added in as usual. $nl is an optional parameter that is of type bool which indicates whether to add a newline to the end of the $message string.

### Running Migrations Scripts

There are several ways to run migrations scripts depending on how you want the migrations to be done. 

The options are:

* Run a single migration file a certain blog
* Run all migration files for a single blog
* Run all migration files for all blogs

To run a single migration file for a certain blog you can go to that blog in the Wordpress backend and click on the Migrations link in the Migrations admin menu tab. Once you are at the migrations list there is a run button with to each list item on the right. Clicking this will run that script for the current blog. 

To run all migration files for a certain blog you can either click the run all button at the top of that blog’s migrations list or click the migrate button that is in the blog list page. To get to this page you must be in the network administration panel and click on the Blog List link in the 

Migrations admin menu tab. There is a migrate button for each blog in the list on the right side or the table.

To run all migrations for all blogs click the Migrate All button at the top of the Blog List page.

Changelog
---------


### 1.0

Original release
=======
Strategery Migrations

=====================



Contributors: Gabriel Somoza (me@gabrielsomoza.com) , Joseph Fitzgibbons(jfitzy87@gmail.com)  

Donate link: http://usestrategery.com  

Tags: migration , multisite  

Requires at least: 3.0  

Tested up to: 3.0  

Stable tag: 1.0  



Easily run and manage migrations scripts to assist in updating blogs on your site.



Description

-----------



This plugins was designed to speed up and manage the process of creating a running migrations scripts. Migrations scripts are used to edit content on a blog to blog basis by running php files which manipulate your Wordpress database. This could be adding or removing a page or post to certain blogs. Strategery Migrations also provides an interface to manage your migrations scripts. You can view a list of all migrations files and see a timestamp indicating when a migration file was created. You can see individual blogs' migration state so that you can tell what files have been run or if a certain blog has been migrated. You can look at a list of all your blogs and see what their migration states are compared to each other.



This plugin was made for Wordpress implementations using a multisite network with sib-directories.



Installation

------------



1. Download and copy the Strategery Migrations folder into ‘/wp-content/plugins/’ directory

2. Network activate the plugin in the ‘Plugins’ menu in the network administration panel



Frequently Asked Questions

--------------------------



### Why doesn’t the migration report have anything in it?



IE and Safari generate a file that you must open to view the migration report. There should be a prompt after running a migration where you can open the file.



### What type of Wordpress implementation is this plugin compatible with?



To use this plugin you must have it setup with a multisite network using sub-directories. Not sub-domains.



### I made a new migration file using the Add New form. Where is that file located?



Migration files are located in ‘/wp-content/plugins/strategery-migrations/migrations/’.



### I created a new migration file... Now what?



Open that file you created and put any code you need in the up method. Put any code you need to undo whatever the up method does in the down method. (The down method is optional)



### I’m trying to run a migration script and it says it’s already up to date.



The plugin will only run migration scrips with a timestamp later than the current blogs migration state timestamp. If you run a single migration in a blog and it has a later timestamp than another script that was made earlier it will make that earlier script unable to be executed. Only run scripts  from earliest to latest if you run them individually. If you have multiple scripts tat need to be run, it is better to run them all at once to avoid mistakes.



Screenshots

-----------



Found in the root folder



1. screenshot-1.png shows an example migrations list for a certain blog. The schedule_page.php is black because it is newer than the current blog’s state indicating that it hasn’t been run. All files with an older timestamp are grey to show that they are migrations from an earlier state. Also note the buttons at the top and for each file which will run a certain action.

2. screenshot-2.png shows the report that appears after running a migration script. Notice the reload button that appears right above it. To see the updated migrations list you must reload the page after running a migration script. If you are using  IE or Safari this report will be empty and a file will be generated with the migration report. View this file to view the report.



Usage Instructions

------------------



### Creating a Migration Script



1. Click on the Add New link in the Migrations admin options tab

2. Enter a the script’s filename using underscores instead of spaces (file_name)

3. Click Create File



You are then redirected to the list or migration files.



At this point the file has been generated and exists in the ‘/strategery-migrations/migrations/’ folder in your ‘plugins’ directory.  



The filename is broken up into 2 parts. The first part before the dash(-) is the timestamp in YYYYMMDDHHMMSS format. This number is used to compare the sate of a blog compared to when this file was created. When a migration is run and completed, it will update the blog that ran the migration to reflect its new state. The sate for a blog is the same timestamp of the most recent migrations run on it. Only migrations with a later timestamp than the blog’s state timestamp will be run. The second part of the filename is the actual name of the migration script file. The format uses underscores as spaces and will be read by the plugin to create the classname in the file using camel case.



Open the file that was created and start coding the migration script. You will notice that the file contains a class that extends the main migration class. Each migration script defines a new class with two methods. The up method is used for creating pages or posts. It is the creation method. The down method is used for undoing the up method. It Is a way to revert the migration. This method is optional.



The methods are run on a blog to blog basis and work within the context of each blog. To get the ID of the current blog, for example, you can use the get_current_blog_id() function.



### Models



Models are abstracts that are used to query your database. Models are used to save or delete that model type into your database. A model could be a post or a page, etc. To use models you need to make use of the getModel function.  



	$this->getModel( $type , $data );  



$type - A string that picks wether the model is a page or a post, etc.  



$data - An array that contains information related to the model type.

 

__Example__



	$data = array('post_type' => 'page' , 'post_name' => 'menu-add' , 'post_title' => 'Add Menu Item' )



	$post = $this->getModel('Post', $data );



You can also use the shortcut getPost($data).



The commands above query your database for existing posts that match the given data in the current blog and return an object of the post. You can then access that post’s information using the object. For example $post->ID will return the id of the post.



To create a page you use the same function call and add ->save() to the end.



	$post = $this->getModel('Post', array('post_type' => 'page' , 'post_name' => 'menu-add' , 'post_title' => 'Add Menu Item' ))->save();



The command above queries your database for existing posts that match the given data in the current blog. If there is no match in the database it will create that post in the database and return an object of it. The save() command will not create duplicates.



If you want to delete a given post you simply need to replace save() with delete().



### Your Database



To run queries on your database without using models you have to use $this->db(). It will return the WordPress database. To run an insert for example you would use the following code.  

 __Example__



	$this->db()->insert($table, $data);



If you want to insert into each blog’s post table you would set the $table as $this->db()->posts



The command would then look like this



	$this->db()->insert( $this->db()->posts , $data );



### Excluding Pages



This migration plugin comes with a helper class that allows you to remove a page from the navigation menu. Pages that should not appear in your navigation menu can easily be excluded with one line of code. These pages are still reachable and still exist but they will not be seen by users with all your other navigation links.



This exclude functionality requires having another [plugin](http://wordpress.org/extend/plugins/exclude-pages/) active.



To use the helper function you need to pass it a path to helper class you are looking for.



	$this->getHelper($path);



The getHelper function takes a string that contains the path to the specific helper class you are looking for.



	$this->getHelper($path)->method($args);



This is the full call to a helper class method which has the helper function that takes the path to the class you are hooking into. getHelper returns an instance of that class and can be used to execute methods. All helper classes are contained in the ‘/lib/Helpers/Plugins/’ directory. It contains .php files with names in camel case. The class that is defined in each file is named the same as the file name except with underscores separating each word. For the exclude from navigation class, it is contained in the ExcludeFromNav.php file, but the class name is exclude_from_nav. This name is the one you need for the helper function.



Below is an example of how to call the exclude from navigation helper class.



__Example__



	$this->getHelper('plugins/exclude_from_nav')->exclude($post->ID);



exclude() takes the id of the page being excluded as an argument.



### Logging



While the migration is running, there will be output in the migrations report showing you the progress of your migration. It will let you know if there was an error finding a specific post or if the migration was already done and it is already up to date.



You can add your own logging information inside your up and down methods for each migration script. This could be a simple message saying you did whatever you were trying to do. You do this by using the log() function. It simply echos the string passed to it.



	$this->log($message , $nl);



Above is how it is called. It doesn’t format the string, so newlines and tabs must be added in as usual. $nl is an optional parameter that is of type bool which indicates whether to add a newline to the end of the $message string.



### Running Migrations Scripts



There are several ways to run migrations scripts depending on how you want the migrations to be done. 



The options are:



* Run a single migration file a certain blog

* Run all migration files for a single blog

* Run all migration files for all blogs



To run a single migration file for a certain blog you can go to that blog in the Wordpress backend and click on the Migrations link in the Migrations admin menu tab. Once you are at the migrations list there is a run button with to each list item on the right. Clicking this will run that script for the current blog. 



To run all migration files for a certain blog you can either click the run all button at the top of that blog’s migrations list or click the migrate button that is in the blog list page. To get to this page you must be in the network administration panel and click on the Blog List link in the 



Migrations admin menu tab. There is a migrate button for each blog in the list on the right side or the table.



To run all migrations for all blogs click the Migrate All button at the top of the Blog List page.



Changelog

---------


### 1.0


Original release