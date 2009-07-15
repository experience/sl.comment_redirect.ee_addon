<?php

/**
 * @package SL Comment Redirect
 * @version 1.0.0
 * @author Stephen Lewis (http://www.experienceinternet.co.uk/)
 * @copyright Copyright (c) 2009, Stephen Lewis
 * @license http://creativecommons.org/licenses/by-sa/3.0/ Creative Commons Attribution-Share Alike 3.0 Unported
 * @link http://www.experienceinternet.co.uk/resources/details/sl-comment-redirect/
*/

if ( ! defined('EXT'))
{
	exit('Invalid file request');
}

if ( ! defined('SL_CRD_version'))
{
	define('SL_CRD_version', '1.0.0');
	define('SL_CRD_docs_url', 'http://www.experienceinternet.co.uk/resources/details/sl-comment-redirect/');
	define('SL_CRD_name', 'SL Comment Redirect');
}


class Sl_comment_redirect {
	
	/**
	 * Extension settings.
	 * @var array
	 */
   var $settings        = array();

	/**
	 * Extension name.
	 * @var string
	 */
   var $name            = SL_CRD_name;

	/**
	 * Extension version.
	 * @var string
	 */
   var $version         = SL_CRD_version;

	/**
	 * Extension description.
	 * @var string
	 */
   var $description     = 'Redirect to a custom URL after comment submission.';

	/**
	 * If $settings_exist = 'y', the settings page will be displayed in EE admin.
	 * @var string
	 */
   var $settings_exist  = 'y';

	/**
	 * Link to extension documentation.
	 * @var string
	 */
   var $docs_url = SL_CRD_docs_url;

	/**
	 * We store all our assets (CSS, JS, and images) in a folder in "themes", so
	 * people can theoretically reskin everything, and even change the behaviour
	 * of the extension, should they find themselves with nothing better to do.
	 */
	var $asset_dir	= '';
	var $css_dir		= '';
	var $js_dir			= '';
	var $img_dir		= '';
	var $utils_dir	= '';

	/**
	 * PHP4 constructor.
	 *
	 * @access  public
	 * @see     __construct
	 */
	function Sl_comment_redirect($settings = '')
	{
		$this->__construct($settings);
	}
	
	
	/**
	 * PHP5 constructor
	 *
	 * @access  public
	 * @param 	array|string 		$settings 	Extension settings; associative array or empty string.
	 */
	function __construct($settings = '')
	{
		global $PREFS, $DB, $REGX;

		$settings = FALSE;

		// Retrieve the settings from the database.
		$query = $DB->query("SELECT settings FROM exp_extensions WHERE enabled = 'y' AND class = '" . get_class($this) . "' LIMIT 1");
		if ($query->num_rows == 1 && $query->row['settings'] != '')
		{
			$settings = $REGX->array_stripslashes(unserialize($query->row['settings']));
		}

		$this->settings = $settings;
	}


	/**
	 * Registers a new addon.
	 *
	 * @access    public
	 * @param			array 		$addons			The existing addons.
	 * @return 		array 		The new addons list.
	 */
	function lg_addon_update_register_addon($addons)
	{
		global $EXT;

		// Retrieve the data from the previous call, if applicable.
		if ($EXT->last_call !== FALSE)
		{
			$addons = $EXT->last_call;
		}

		// Register a new addon.
		if ($this->settings['update_check'] == 'y')
		{
			$addons[SL_CRD_name] = $this->version;
		}

		return $addons;
	}


	/**
	 * Registers a new addon source.
	 *
	 * @access    public
	 * @param			array 		$sources		The existing sources.
	 * @return		array 		The new source list.
	 */
	function lg_addon_update_register_source($sources)
	{
		global $EXT;

		// Retrieve the data from the previous call, if applicable.
		if ($EXT->last_call !== FALSE)
		{
			$sources = $EXT->last_call;
		}

		// Register a new source.
		if ($this->settings['update_check'] == 'y')
		{
			$sources[] = 'http://www.experienceinternet.co.uk/addon-versions.xml';
		}

		return $sources;
	}
	
	
	/**
	 * Redirects to the specified URL after comment submission.
	 *
	 * @access  public
	 * @param   array   $comment_data   An array of data for the new comment.
	 * @param   string  $moderate       Whether the comment is subject to moderation.
	 * @param   int     $comment_id     The ID of the newly-created comment.
	 */
	function insert_comment_end($comment_data = array(), $moderate = 'n', $comment_id = 0)
	{
	  global $EXT, $FNS;
	  
	  if (array_key_exists('sl_comment_redirect', $_POST))
	  {
	    $FNS->redirect($_POST['sl_comment_redirect']);
	    $EXT->end_script = TRUE;
	  }
	}


	/**
	 * Builds the breadcrumbs part of the settings form.
	 *
	 * @access	private
	 * @return  string    The "Breadcrumbs" HTML.
	 */
	function _settings_form_breadcrumbs()
	{
		global $DSP, $LANG;

		$r = '';
		$r .= $DSP->anchor(BASE . AMP . 'C=admin' . AMP . 'P=utilities', $LANG->line('utilities'));
		$r .= $DSP->crumb_item($DSP->anchor(BASE . AMP . 'C=admin' . AMP . 'M=utilities' . AMP . 'P=extensions_manager', $LANG->line('extensions_manager')));
		$r .= $DSP->crumb_item($LANG->line('extension_name'));

		$r .= $DSP->right_crumb(
			$LANG->line('disable_extension'),
			BASE . AMP . 'C=admin' . AMP . 'M=utilities' . AMP . 'P=toggle_extension' . AMP . 'which=disable' . AMP . 'name=' . strtolower(get_class($this))
		);

		return $r;
	}


	/**
	 * Builds the "Check for Updates" part of the settings form.
	 *
	 * @access	private
	 * @return  string    The "Check for Updates" HTML.
	 */	
	function _settings_form_updates()
	{
		global $DSP, $LANG;

		$r  = '';

		// Automatic updates.
		$r .= $DSP->table_open(
			array(
				'class' 	=> 'tableBorder',
				'border' 	=> '0',
				'style' 	=> 'width : 100%; margin-top : 1em;',
				)
			);

		$r .= $DSP->tr();
		$r .= $DSP->td('tableHeading', '', '2');
		$r .= $LANG->line('update_check_title');
		$r .= $DSP->td_c();
		$r .= $DSP->tr_c();

		$r .= $DSP->tr();
		$r .= $DSP->td('', '', '2');
		$r .= "<div class='box' style='border-width : 0 0 1px 0; margin : 0; padding : 10px 5px'><p>" . $LANG->line('update_check_info'). "</p></div>";
		$r .= $DSP->td_c();
		$r .= $DSP->tr_c();	

		$r .= $DSP->tr();
		$r .= $DSP->td('tableCellOne', '40%');
		$r .= $DSP->qdiv('defaultBold', $LANG->line('update_check_label'));
		$r .= $DSP->td_c();

		$update_check = isset($this->settings['update_check']) ? $this->settings['update_check'] : 'y';

		$r .= $DSP->td('tableCellOne', '60%');
		$r .= $DSP->input_select_header('update_check', '', 3, '', 'id="update_check"');
		$r .= $DSP->input_select_option('y', 'Yes', ($update_check == 'y' ? 'selected' : ''));
		$r .= $DSP->input_select_option('n', 'No', ($update_check == 'n' ? 'selected' : ''));
		$r .= $DSP->input_select_footer();
		$r .= $DSP->td_c();

		$r .= $DSP->tr_c();
		$r .= $DSP->table_c();

		return $r;
	}


	/**
	 * Builds the "Save Settings" part of the settings form.
	 *
	 * @access  private
	 * @return  string    The "Save Settings" HTML.
	 */
	function _settings_form_save()
	{
	  global $DSP, $LANG;

		return $DSP->qdiv('itemWrapperTop', $DSP->input_submit($LANG->line('save_settings'), 'save_settings', 'id="save_settings"'));
	}


	/**
	 * Renders the settings screen.
	 *
	 * @access  public
	 */
	function settings_form()
	{
		global $DSP, $LANG;

		// Start building the page.
		// $headers 				= $this->_settings_form_headers();			  // Additional CSS and JS headers.		
		$breadcrumbs 		= $this->_settings_form_breadcrumbs();			  // Breadcrumbs.
		$browser_title 	= $LANG->line('extension_settings');		// Browser title.

		// Body
		$body  = '';
		$body .= $DSP->heading($LANG->line('extension_name') . " <small>v{$this->version}</small>");		// Main title.

		// Open the form.
		$body .= $DSP->form_open(
			array(
				'action'	=> 'C=admin' . AMP . 'M=utilities' . AMP . 'P=save_extension_settings',
				'id'			=> 'form_save_settings',
				'name'		=> 'form_save_settings'
				),
			array(
				'name' 			=> strtolower(get_class($this)),		// Must be lowercase.
				'action'		=> 'save_settings',
				)
			);

		// Check for updates / save settings.
		$body .= $this->_settings_form_updates() . $this->_settings_form_save();

		// Close the form.
		$body .= $DSP->form_c();

		// Output everything.
		// $DSP->extra_header	.= $headers;
		$DSP->title 				= $browser_title;
		$DSP->crumbline 		= TRUE;
		$DSP->crumb 				= $breadcrumbs;
		$DSP->body 					= $body;
	}


	/**
	 * Saves the extension settings.
	 *
	 * @access  public
	 */
	function save_settings()
	{
		global $DB, $REGX;

		// Retrieve the post data.
		$this->settings = array(
			'update_check'	=> isset($_POST['update_check']) ? $_POST['update_check'] : ''
			);

		// Serialise the settings, and save them to the database.
		$sql = "UPDATE exp_extensions SET settings = '" . addslashes(serialize($this->settings)) . "' WHERE class = '" . get_class($this) . "'";
		$DB->query($sql);
	}


	/**
	 * Activate the extension.
	 *
	 * @access  public
	 */
	function activate_extension()
	{
		global $DB;

		$hooks = array(
			'lg_addon_update_register_source'	=> 'lg_addon_update_register_source',
			'lg_addon_update_register_addon'	=> 'lg_addon_update_register_addon',
			'insert_comment_end'              => 'insert_comment_end'
			);

		foreach ($hooks AS $hook => $method)
		{
			$sql[] = $DB->insert_string('exp_extensions', array(
					'extension_id' => '',
					'class'        => get_class($this),
					'method'       => $method,
					'hook'         => $hook,
					'settings'     => '',
					'priority'     => 10,
					'version'      => $this->version,
					'enabled'      => 'y'
					));
		}

		// Run all the SQL queries.
		foreach ($sql AS $query)
		{
			$DB->query($query);
		}		
	}


	/**
	 * Updates the extension.
	 *
	 * @access  public
	 * @param   string    $current    Contains the current version if the extension is already installed, otherwise empty.
	 * @return  bool      FALSE if the extension is not installed, or is the current version.
	 */
	function update_extension($current='')
	{
		global $DB;

		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}

		if ($current < $this->version)
		{
			$DB->query("UPDATE exp_extensions
				SET version = '" . $DB->escape_str($this->version) . "' 
				WHERE class = '" . get_class($this) . "'");
		}
	}


	/**
	 * Disables the extension, and deletes settings from the database.
	 *
	 * @access  public
	 */
	function disable_extension()
	{
		global $DB;	
		$DB->query("DELETE FROM exp_extensions WHERE class = '" . get_class($this) . "'");
	}

}

?>