# W++ Core
A Wordpress Plugin acting as core for other of my plugins

Loaded with an autocreated mu-plugin it will load before other plugins so that they can always use it

It also includes the latest version of Redux Framework https://reduxframework.com

And all of it's paid extensions!

## Features
The plugin offers interfaces, traits and classes to work in an OOP way

 ### Interfaces
 - WP_Plugin: Interface to create plugins 
 - WP_Theme: Interface to create themes
 ### Objects
 - WP_Cron: An easy way to create a cron simply by calling new WP_Cron created and cleared automatically on plugin activation/deactivation
 - WP_Shortcode: A more understandable way to create a shortcode
 - WP_Ajax: An easy way to create ajax callbacks
 - ReduxConfig: An easy way to setup your Redux Panel
 ### Traits 
 - Singleton: The class becomes a singleton, that is instanced only once
 - Initializable: Allows you to define an __init function that will be called when the file is included
 - WP_VC_Shortcode: Allows you to create a WPBakery Page Builder shortcode immediately accessible trough the page builder
 