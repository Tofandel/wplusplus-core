### This project has been moved to https://gitlab.tukan.hu/wplusplus/wplusplus-core

# W++ Core
A Wordpress Plugin acting as core for other of my plugins

Loaded with an autocreated mu-plugin it will load before other plugins so that they can always use it

It also includes the latest version of Redux Framework https://reduxframework.com

And all of it's premium extensions!

## Features
The plugin offers interfaces, traits and classes to work in an OOP way

 ### Interfaces
 - WP_Plugin: Interface to create plugins 
 - WP_Theme: Interface to create themes
 ### Objects
 - WP_Cron: An easy way to create a cron simply by calling new WP_Cron created and created/cleared automatically on plugin activation/deactivation
 - WP_Shortcode: A more straightforward way to create a shortcode
 - WP_Ajax: An easy way to create ajax callbacks
 ### Traits 
 - Singleton: The class becomes a singleton, that is instanced only once
 - Initializable: Allows you to define a __StaticInit function that you should call after the class is defined or when you include the file (Up to you)
 - WP_VC_Shortcode: Allows you to create a WPBakery Page Builder shortcode immediately accessible trough the page builder
 ### Modules
 - ReduxFramework: Usefull to easily create a redux panel or redux metaboxes
 - LicenceManager: Create very simply a licence manager for your plugin, it will create a section for it automatically at the end of your redux panel
