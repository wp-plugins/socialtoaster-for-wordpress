SocialToaster for Drupal
====================
This module allows drupal to integrate with SocialToaster. Users with adminstrative
access can turn on and off SocialToaster settings for specific node types.
A block with SocialToaster's Signup widget will become available in the blocks
administration page.  Ambassadors will be able to sign up to promote your content by
clicking the links in that block
SocialToaster is a revolutionary hosted platform that allows your supporters to automatically
drive traffic to your website via their social network accounts like Facebook & Twitter, while
providing you with advanced real-time marketing analytics. 
For more information about SocialToaster, please visit http://www.socialtoaster.com

INSTALLATION
------------

1.  Extract the SocialToaster plugin to your plugins directory
      wp-content/plugins


CONFIGURATION
------------

1.  Enable SocialToaster module in:
      wp-admin/plugins.php

2.  If you would like to integrate with the FormBuilder plugin to capture leads
    through online forms, make sure that plugin is activated.

3.  You'll now find a SocialToaster link in the Settings menu
      wp-admin/options-general.php?page=socialtoaster_wordpress_integration_settings

4.  Click the link below to sign up for an account and receive your keys if you do not
    already have them.
       http://www.socialtoaster.com/get-socialtoaster-now?platform=wordpress

5.  Enter your SocialToaster keys.  Also, check the "Enable Lead Capturing" checkbox
    if the FormBuilder plugin is activated.  The rest of the advanced settings should stay
    the same.

6.  Go to the widget page
      wp-admin/widgets.php
      
7.  Add the SocialToaster Ambassador Signup widget to an appropriate sidebar.

8.  That's it!  Ambassadors will be able to sign up and manage their sharing settings 
    using the "SocialToaster Ambassador Signup" widget.  Any time you add a new blog post
    to your site, your ambassadors will get an email notification with instructions on how
    to approve or deny the new content.


FORMBUILDER INTEGRATION
---------------------

You can use this plugin to create a form, and then attach it to a post.
In order for the information to get stored correctly on our end, the name
field must be have the value "full_name" in the "FIELD NAME" field, and
the email field must have the value "email".  There are plans to make this
more flexible in the future
