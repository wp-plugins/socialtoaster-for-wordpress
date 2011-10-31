=== Plugin Name ===
Contributors: jglazer
Tags: socialtoaster
Requires at least: 2.0
Tested up to: 3.1.2
Stable tag: trunk

== Description ==

SocialToaster for Wordpress is a plugin that allows a Wordpress site to seamlessly
integrate with the SocialToaster social marketing automation solution. This plugin
provides for the following functionality:

SocialToaster for Wordpress allows site owners to recruit site users to act as
"Ambassadors" for the site, whereby they can assist in promoting content published
on the site to their own personal social networks including Facebook, Twitter, LinkedIn,
and MySpace. Ambassadors are simply website users that have a relationship to the
organization the site belongs to and who want to see that organization succeed. They
can be employees, customers, partners, volunteers, donors, or anybody that wants to
help promote site content.  Ambassadors to go through a one-time sign-up process.

SocialToaster for Wordpress allows posts that are published to be seamlessly promoted to
the Ambassadors' networks via the SocialToaster marketing automation solution. The plugin
adds fields to the post submission form to allow the publisher to specify whether or not
content should be promoted through SocialToaster, to indicate what the Ambassadors' status
updates will be changed to be, and to give the content a title to be referenced in
SocialToaster's advanced reporting and analytics.

SocialToaster for Wordpress Allows site administrators to maintain and administer the plugin.

== Installation ==

1.  Extract the SocialToaster plugin to your plugins directory
      wp-content/plugins

2.  Activate the plugin throught the 'Plugins' menu in wordpress
      wp-admin/plugins.php

3.  You'll now find a SocialToaster link in the Settings menu
      wp-admin/options-general.php?page=socialtoaster_wordpress_integration_settings

4.  Click the link below to sign up for an account and receive your keys if you do not
    already have them.
       http://www.socialtoaster.com/get-socialtoaster-now?platform=wordpress

5.  Enter your SocialToaster keys.  The rest of the advanced settings should stay the same.

6.  Go to the widget page
      wp-admin/widgets.php
      
7.  Add the SocialToaster Ambassador Signup widget to an appropriate sidebar.

8.  That's it!  Ambassadors will be able to sign up and manage their sharing settings 
    using the "SocialToaster Ambassador Signup" widget.  Any time you add a new blog post
    to your site, your ambassadors will get an email notification with instructions on how
    to approve or deny the new content.

== Formbuilder Integration ==

You can use this plugin to create a form, and then attach it to a post.
In order for the information to get stored correctly on our end, the name
field must be have the value "full_name" in the "FIELD NAME" field, and
the email field must have the value "email".  There are plans to make this
more flexible in the future
