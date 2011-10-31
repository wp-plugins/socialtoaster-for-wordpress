<?php

  /*
  Plugin Name: SocialToaster Wordpress Integration
  Plugin URI: https://my.socialtoaster.com/
  Description: Allows you to integrate SocialToaster with a wordpress blog.
  Author: SocialToaster - Josh Glazer
  Version: 2.0
  Author URI: https://my.socialtoaster.com/
  */

  global $table_prefix;

  //Define SocialToaster tables
  define("SOCIALTOASTER_TABLE_POSTS", $table_prefix . "socialtoaster_posts");

  //Installation Functions
  register_activation_hook(__FILE__,'socialtoaster_activate');
  register_deactivation_hook(__FILE__,'socialtoaster_deactivate');

  function socialtoaster_activate() {
    global $wpdb;

    add_option("socialtoaster_name", "SocialToaster", "", "no");
    add_option("socialtoaster_domain", "https://my.socialtoaster.com/", "", "no");
    add_option("socialtoaster_path_share", "st/protected_post/", "", "no");
    add_option("socialtoaster_path_nonce", "st/protected_get_nonce/", "", "no");

    $wpdb->query("CREATE TABLE IF NOT EXISTS " . SOCIALTOASTER_TABLE_POSTS . " (
                  `id` bigint(20) NOT NULL auto_increment,
                  `short_summary` varchar(110) NOT NULL,
                  `label` varchar(110) NOT NULL,
                  `posted` tinyint(1) NOT NULL,
                  `post_id` bigint(20) NOT NULL,
                  UNIQUE KEY id (id)
                  );");

  }

  function socialtoaster_deactivate() {
    global $wpdb;

    delete_option('socialtoaster_name');
    delete_option('socialtoaster_key');
    delete_option('socialtoaster_secret');
    delete_option('socialtoaster_domain');    
    delete_option('socialtoaster_path_share');
    delete_option('socialtoaster_path_nonce');    
    delete_option('socialtoaster_debug');

    $sql = 'DROP TABLE `' . 
            SOCIALTOASTER_TABLE_POSTS . '`;';
    $wpdb->query($sql);

  }

  //User Interface
  add_action('admin_print_scripts', 'socialtoaster_add_javascript');
  add_action('wp_print_scripts', 'socialtoaster_add_javascript');

  function socialtoaster_add_javascript() {

    global $nonce;

    socialtoaster_generate_nonce();

    $api_url = get_option('socialtoaster_domain'). "api-full/" . get_option('socialtoaster_key') . ".js";
    print "<script type='text/javascript'>(function() { function async_load(){ var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = '" . $api_url . "'; var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(s, x); } if (window.attachEvent) window.attachEvent('onload', async_load); else window.addEventListener('load', async_load, false); })();</script>";
    wp_enqueue_script('socialtoaster_script', WP_PLUGIN_URL . '/socialtoaster/socialtoaster.js', array(), "1.0" );
  }


   //check for Required Functions
   if(required_functions()){
    add_action('publish_post', 'socialtoaster_post');
   }

  function socialtoaster_post($post_id) {

    global $nonce, $wpdb;

    if( !$nonce ) {
      socialtoaster_generate_nonce();
    }

    $post = get_post($post_id);

    $sql = "SELECT * FROM " . SOCIALTOASTER_TABLE_POSTS . " WHERE post_id = '" . $post_id . "';";
    $results = $wpdb->get_results($sql, ARRAY_A);
    if($results) {
      $postDetails = $results[0];
    }

    if( $postDetails['posted'] == 0 && $_POST['socialtoaster_approve'] == 1 ) {

      $url = get_option('socialtoaster_domain');
      $url .= get_option('socialtoaster_path_share');
      $url .= "?key=" . get_option('socialtoaster_key');
      $url .= "&sec=" . get_option('socialtoaster_secret');
      $url .= "&eva=";
      $url .= "&pub=";
      $url .= "&url=" . urlencode( $post->guid );
      $url .= "&tsr=" . urlencode( stripslashes($postDetails['short_summary'] == "" ? $post->post_title : $postDetails['short_summary']) );
      $url .= "&lbl=" . urlencode( stripslashes($postDetails['label'] == "" ? $post->post_title : $postDetails['label']) );
      $url .= "&nonce=" . $nonce;

      $post_result = socialtoaster_curl_to_xml($url);

      if ($post_result->code == 1) {
        if($results)
        {
          //update row
          $pageDetails = $results[0];
          $pageDetails['posted'] = addslashes(1);
          $pageDetails['post_id'] = addslashes($post_id);
          $wpdb->update(SOCIALTOASTER_TABLE_POSTS, 
            $pageDetails, 
            array('id'=>$pageDetails['id'])
          );

        } else {
          //insert row
          $pageDetails['posted'] = addslashes(1);
          $pageDetails['post_id'] = addslashes($post_id);
          $wpdb->insert(SOCIALTOASTER_TABLE_POSTS, $pageDetails);
        }
      }
      return $post_result->code;

    }

  }
  
  add_action('admin_menu', 'socialtoaster_add_custom_box');

  function socialtoaster_add_custom_box()
  {
    // If we are capable of using meta boxes, use it.
    if( function_exists( 'add_meta_box' )) {

      add_meta_box( 'socialtoaster_sectionid', get_option('socialtoaster_name') . " Options",
                    'socialtoaster_post_options', 'post', 'normal', 'high' );

    } else {

       // Otherwise just use the old functions
       add_action( 'simple_edit_form', 'socialtoaster_post_options' );

    }
  }

  function socialtoaster_post_options()
  {
    global $post, $wpdb;

    $post_id = ( $post->post_parent ? $post->post_parent : $post->ID );

    // If the post already has an id, determine whether or not there is a form already linked to it.
    if($post_id)
    {
      // Determine if the post/page has a linked form.
      $sql = "SELECT * FROM " . SOCIALTOASTER_TABLE_POSTS . " WHERE post_id = '" . $post_id . "';";
      $results = $wpdb->get_results($sql, ARRAY_A);
      if($results) {
        $postDetails = $results[0];
      }
    }

    //check for cURL
    if(required_functions()){

    echo "<p id='socialtoasterContactShortSummary'>\n" .
         "<label for='socialtoaster_short_summary'>" . get_option('socialtoaster_name') . " Short Summary: </label>" . 
         "<input name='socialtoaster_short_summary' id='socialtoaster_short_summary' type='text' value='" . socialtoaster_escape($postDetails['short_summary']) . "' maxlength='110' style='width: 98%'>" . 
         __("<em>Write a short summary of the post. This is the text that will appear in your social media account pages.</em>") . 
         "</p>\n";

    echo "<p id='socialtoasterContactLabel'>\n" .
         "<label for='socialtoaster_label'>" . get_option('socialtoaster_name') . " Label: </label>" . 
         "<input name='socialtoaster_label' id='socialtoaster_label' type='text' value='" . socialtoaster_escape($postDetails['label']) . "' maxlength='110' style='width: 98%'>" . 
         __("<em>Write a label for your post.  This is the text that will appear in your " . get_option('socialtoaster_name') . " reports.</em>") . 
         "</p>\n";

    if( $postDetails['posted'] == 0 ) {
      echo "<p id='socialtoasterContactApprovePost'>\n" .
           "<input type='checkbox' name='socialtoaster_approve' id='socialtoaster_approve' value='1'> " .
           "<label for='socialtoaster_approve'>I would like to post this blog to SocialToaster.</label>" . 
           "</p>\n";
    }

   }else{
        echo '<p>To share links with Social Toaster, please ask your hosting provider to enable: <br /><ul style="padding-left: 10px;">';   

        if(!function_exists('json_decode')){
         echo '<li>json_decode</li>';  
        }
        if(!function_exists('simplexml_load_string')){
         echo '<li>simplexml_load_string</li>';
        }
        if(!function_exists('curl_init')){
         echo '<li style="color: red">cURL</li>';
        }
      echo '</ul>';
   }

  }

  function socialtoaster_escape($text) {
    $text = str_replace("'", "&apos;", $text);
    $text = stripslashes($text);
    return $text;
  }

  add_action( 'save_post', 'socialtoaster_save_post' );

  function socialtoaster_save_post($id)
  {
    global $wpdb;



    if(isset($_POST['socialtoaster_short_summary']) || isset($_POST['socialtoaster_label']))
    {
      $post = get_post($id);

      $post_id = ( $post->post_parent ? $post->post_parent : $post->ID );

      $pageDetails['post_id'] = $post_id;

      $sql = "SELECT * FROM " . SOCIALTOASTER_TABLE_POSTS . " WHERE post_id = '" . $post_id . "';";
      $results = $wpdb->get_results($sql, ARRAY_A);
      if($results)
      {
        //update row
        $pageDetails = $results[0];
        $pageDetails['short_summary'] = $_POST['socialtoaster_short_summary'];
        $pageDetails['label'] = $_POST['socialtoaster_label'];
        $pageDetails['post_id'] = $post_id;
        $wpdb->update(SOCIALTOASTER_TABLE_POSTS, 
          $pageDetails, 
          array('id'=>$pageDetails['id'])
        );

      } else {
        //insert row
        $pageDetails['short_summary'] = $_POST['socialtoaster_short_summary'];
        $pageDetails['label'] = $_POST['socialtoaster_label'];
        $pageDetails['post_id'] = $post_id;
        $wpdb->insert(SOCIALTOASTER_TABLE_POSTS, $pageDetails);
      }

    }
  }

  //Administration
  add_action('admin_menu', 'socialtoaster_menu');

  function socialtoaster_menu() {
    add_options_page('SocialToaster Options', 'SocialToaster Settings', 'manage_options', 'socialtoaster_wordpress_integration_settings', 'socialtoaster_options');
  }

  function socialtoaster_options() {
 
    if(!empty($_POST)){
      update_option('socialtoaster_name',$_POST['socialtoaster_name']);
      update_option('socialtoaster_key',$_POST['socialtoaster_key']);
      update_option('socialtoaster_secret',$_POST['socialtoaster_secret']);
      update_option('socialtoaster_domain',$_POST['socialtoaster_domain']);
      update_option('socialtoaster_debug',$_POST['socialtoaster_debug']);

      echo "<div id='message' class='updated fade'>
              <p>Your SocialToaster settings have been saved.</p>
            </div>";
    }

    echo "<form action='' method='post'>
          <div class='wrap'><div class=icon32 id=icon-options-general><br /></div>
          <a name='socialtoaster_integration'></a>
          <h2>SocialToaster Integration Settings</h2>";
    if(get_option('socialtoaster_key') == '' && get_option('socialtoaster_secret') == '') {
      echo "<p>If you already have a SocialToaster account, enter your information in the form below<p>";
    }
    echo "</div>
          <table class='form-table'>

            <tr valign='top'>
              <th scope='row'>SocialToaster Display Name:</th>
              <td>
                <input type='text' class='regular-text' name='socialtoaster_name' value='" . stripslashes(get_option('socialtoaster_name')) . "'><br />
                <small>
                  <em>
                    The name that SocialToaster will be displayed as throughout the site.
                    For example, if you would like ambassadors to share posts
                    through a system called \"My SocialToaster\", you should enter that
                    name in the field above.
                  </em>
                </small>
              </td>
            </tr>

            <tr valign='top'>
              <th scope='row'>SocialToaster Key:</th>
              <td>
                <input type='text' class='regular-text' name='socialtoaster_key' value='" . stripslashes(get_option('socialtoaster_key')) . "'><br />
                <small>
                  <em>
                    The SocialToaster key that is assigned to this website.
                  </em>
                </small>
              </td>
            </tr>

            <tr valign='top'>
              <th scope='row'>SocialToaster Secret Key:</th>
              <td>
                <input type='text' class='regular-text' name='socialtoaster_secret' value='" . stripslashes(get_option('socialtoaster_secret')) . "'><br />
                <small>
                  <em>
                    The SocialToaster secret key that is assigned to this website.
                  </em>
                </small>
              </td>
            </tr>

            <tr valign='top'>
              <th scope='row'>SocialToaster Domain:</th>
              <td>
                <input type='text' class='regular-text' name='socialtoaster_domain' value='" . stripslashes(get_option('socialtoaster_domain')) . "'><br />
                <small>
                  <em>
                    The domain name where SocialToaster is being accessed.  For
                    example, if the full register URL is
                    https://my.socialtoaster.com/st/ajax_register/?key=foo&user=bar...
                    then you will enter https://my.socialtoaster.com/ into this field. 
                    Make sure you include a trailing slash.
                  </em>
                </small>
              </td>
            </tr>


            <tr valign='top'>
              <th scope='row'>SocialToaster Debug mode:</th>
              <td>
                <input type='checkbox' value='1' name='socialtoaster_debug' " . ( stripslashes(get_option('socialtoaster_debug')) == 1 ? "checked='checked'" : "" ) . "><br />
                <small>
                  <em>
                    Disables some authentication.  Do not use for production
                    sites.
                  </em>
                </small>
              </td>
            </tr>

          </table>

          <p class='submit'>
            <input class='button-primary' name='submit' type='submit' value='Update Settings' /></td>
          </p>
          
          </form>";
  }
  
  //Utilities
  
  /**
   * Function that takes in a url, pulls json information from that url,
   * parses the json information, and returns an associative array
   */

  function socialtoaster_curl_to_json($url) {
   //check cURL
   if(required_functions()){
      return socialtoaster_parse_json(socialtoaster_curl($url));
   }
 } 

  /**
   * Function that takes in a url, pulls xml information from that url,
   * parses the xml information, and returns an associative array
   */
  function socialtoaster_curl_to_xml($url) {
    //check cURL
    if(required_functions()){
      return simplexml_load_string(socialtoaster_curl($url));
    }
  }

  /**
   * Wrapper function that uses curl to get contents of a url
   */
  function socialtoaster_curl($url) {
    $curl_handle=curl_init();
    curl_setopt($curl_handle,CURLOPT_URL,$url);
    curl_setopt($curl_handle,CURLOPT_VERBOSE,0);
    curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,10);
    curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
    $result = curl_exec($curl_handle);
    curl_close($curl_handle);

    return $result;
  }
    
  /**
   * Function that parses json
   * Note: the function trims parentheses from the json content
   */
  function socialtoaster_parse_json($string) {
    $string = rtrim(ltrim($string, "("),")\r\n");
    $json_obj = json_decode($string);
    return $json_obj;
  }
  
  function socialtoaster_generate_nonce() {
    global $current_user, $nonce;

    get_currentuserinfo();
    $userid = $current_user->ID ;

    if( strstr( $_SERVER['SCRIPT_NAME'], "/wp-admin/user-edit.php" ) ) { 
      global $user_id;
      $userid = $user_id;
    }

    $url  = get_option('socialtoaster_domain');
    $url .= "/";
    $url .= get_option('socialtoaster_path_nonce');
    $url .= "?key=" . get_option('socialtoaster_key');
    $url .= "&sec=" . get_option('socialtoaster_secret');
    $url .= "&eva=" . $userid;

    if (get_option('socialtoaster_debug')) {
      $url .= "&ipa=";
    } else {
      $url .= "&ipa=" . urlencode($_SERVER['REMOTE_ADDR']);
    }
  
    $result = socialtoaster_curl_to_xml($url);
    $nonce = $result->value;

  }

  wp_register_sidebar_widget('socialtoaster_ambassador_signup', 'Socialtoaster Ambassador Signup', 'socialtoaster_widget_ambassador_signup');

  function socialtoaster_widget_ambassador_signup() {
    print '<div id="st-inline-small"></div>';
  }


function required_functions(){
  if(!function_exists('json_decode')){
   return FALSE;
  }
  if(!function_exists('simplexml_load_string')){
   return FALSE;
  }
  if(!function_exists('curl_init')){
  return FALSE;
  }
return TRUE;
}

