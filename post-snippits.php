<?php
/*
Plugin Name: Post Snippits
Plugin URI: http://sandjam.co.uk/sandjam/2012/08/post-snippits-wordpress-plugin/
Description: Insert common snippits of text or code in to posts using shortcodes. Define your snippits in the <a href="options-general.php?page=postsnippits_settings">Post Snippits Settings</a>
Version: 1.0
Author: Peter Smith
Author URI: http://www.sandjam.co.uk
License: GPL2
Installation:
Place this file in your /wp-content/plugins/ directory, then activate through the administration panel. 
*/
 
class WPSnippit {
	private $defaultOptions = array('my_first_snippit'=>'This is the text that will appear in your post');
	
	public function __construct() {  
		// add install/uninstall hooks
		register_activation_hook(__FILE__, array($this, 'install'));
		register_uninstall_hook( __FILE__, array($this, 'uninstall'));

		// add the admin options page
		add_action('admin_menu', array($this, 'admin_add_page'));
		
		// add the hook to render the snippit in posts
		add_shortcode('snippit', array($this, 'show_snippit'));
	}  
	
	// ------------------------------------------------------------
	// INSTALLATION
	// ------------------------------------------------------------
	public function install() {
		$options = $this->defaultOptions;
		add_option('post_snippit_options', $options);
	}
	
	public function uninstall() {
		delete_option('post_snippit_options');
	}
	
	// ------------------------------------------------------------
	// ADMIN OPTIONS PAGES 
	// ------------------------------------------------------------	
	function admin_add_page() {
		add_options_page('Post Snippits', 'Post Snippits', 'manage_options', 'postsnippits_settings', array($this, 'settings_page'));
	}
	
	// display the admin options page
	function settings_page() {
		$options = get_option('post_snippit_options');
		if ($options==''){
			$options = $this->default_options;
		}
		
		if (isset($_POST['keys'])) {
			$options = array();
			foreach ($_POST['keys'] as $i=>$key) {
				$key = str_replace(' ', '_', $key);
				$key = preg_replace("/[^a-zA-Z0-9\_\s]/", "", $key);
				
				$_POST['keys'][$i] = $key;
				if ($key!='' && $_POST['values'][$i]!='') {
					$options[$key] = stripslashes($_POST['values'][$i]);
				}
			}
			
			update_option('post_snippit_options', $options);
		}
		
		// delete a snippit
		if (isset($_GET['delete']) && isset($options[$_GET['delete']])) {
			unset($options[$_GET['delete']]);
			update_option('post_snippit_options', $options);
		}
		
		// add new snippit
		if (isset($_POST['add_snippit'])) {
			$key='';
			while(isset($options['enter_name'.$key])) {
				if ($key=='') { $key = 1; }
				else { $key++; };
			}
			$options['enter_name'.$key] = 'enter value here';
			update_option('post_snippit_options', $options);
		}
		?>
		<div>
			<h2>Post Snippits</h2>
			<p>
				Use the following shortcode format in your post to insert the relevant snippit:<br />
				[snippit name]
			</p>
			<p>
				Snippit names must have no spaces or special characters.
			</p>
			
			<form action="" method="post">
			<table cellspacing="0" class="wp-list-table widefat fixed posts">
				<thead>
					<tr valign="top">
						<th>Name</th>
						<th>Snippit</th>
						<th>Shortcode</th>
					</tr>
				</thead>
				<tbody>
				<?php
				foreach($options as $k=>$v) {
					?>
					<tr valign="top">
						<td>
							<input type="text" value="<?=$k?>" size="40" name="keys[]"><br />
							<a href="?page=postsnippits_settings&delete=<?=$k?>">Delete</a>
						</td>
						<td><textarea rows="3" name="values[]" cols="50" ><?=$v?></textarea></td>
						<td>
							<?php if ($k!='') { ?>
								[snippit <?=$k?>]
							<?php } ?>
						</td>
					</tr>
					<?php
				}
				?>
				
				</tbody>
				</table>
				<p>
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
					<input name="add_snippit" type="submit" class="button-primary" value="Add Snippit" />
				</p>
			</form>
		</div>
		<?php
	}
	
	// ------------------------------------------------------------
	// USE TAG TO RENDER SNIPPIT ON PAGE 
	// ------------------------------------------------------------
	public function show_snippit($vars) {
		$options = get_option('post_snippit_options');
		$html = '';
		
		foreach ($vars as $k) {
			if (isset($options[$k])) {
				// remove linebreaks to avoid wordpress replacing them with <br /> or <p>
				$html .= preg_replace("/[\\n\\r]/", '', $options[$k]);
			}
		}
		
		return $html;
	}
}  
  
$wpSnippit = new WPSnippit();  
?>