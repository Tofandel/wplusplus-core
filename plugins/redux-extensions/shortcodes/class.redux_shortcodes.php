<?php

/**
 * Redux Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Redux Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Redux Framework. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     Redux_Framework
 * @subpackage  Extensions
 * @author      Dovy Paukstys (dovy)
 * @version 1.0.0
 * @since 3.1.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't duplicate me!
if ( ! class_exists( 'Redux_Shortcodes' ) ) :

	/**
	 * Redux Framework shortcode extension class. Takes the common Wordpress functions `wp_get_theme()` and `bloginfo()` and a few other functions and makes them accessible via shortcodes. Below you will find a table for the possible shortcodes and their values.
	 *
	 * | shortcode | Function | Description |
	 * |-----------|----------|-------------|
	 * | blog-name | bloginfo("name") | Displays the "Site Title" set in Settings > General. This data is retrieved from the "blogname" record in the wp_options table. |
	 * | blog-description | bloginfo("description") |  Displays the "Tagline" set in Settings > General. This data is retrieved from the "blogdescription" record in the wp_options table.|
	 * | blog-wpurl | bloginfo("wpurl") |  Displays the "WordPress address (URL)" set in Settings > General. This data is retrieved from the "siteurl" record in the wp_options table. Consider using **blog-root_url** instead, especially for multi-site configurations using paths instead of subdomains (it will return the root site not the current sub-site). |
	 * | blog-root_url | site_url() |  Return the root site, not the current sub-site. |
	 * | blog-url | home_url() |  Displays the "Site address (URL)" set in Settings > General. This data is retrieved from the "home" record in the wp_options table. |
	 * | blog-admin_email | bloginfo("admin_email") |  Displays the "E-mail address" set in Settings > General. This data is retrieved from the "admin_email" record in the wp_options table.|
	 * | blog-charset | bloginfo("charset") |  Displays the "Encoding for pages and feeds" set in Settings > Reading. This data is retrieved from the "blog_charset" record in the wp_options table. Note: In Version 3.5.0 and later, character encoding is no longer configurable from the Administration Panel. Therefore, this parameter always echoes "UTF-8", which is the default encoding of WordPress.|
	 * | blog-version | bloginfo("version") |  Displays the WordPress Version you use. This data is retrieved from the $wp_version variable set in wp-includes/version.php.|
	 * | blog-html_type | bloginfo("html_type") |  Displays the Content-Type of WordPress HTML pages (default: "text/html"). This data is retrieved from the "html_type" record in the wp_options table. Themes and plugins can override the default value using the pre_option_html_type filter.|
	 * | blog-text_direction | bloginfo("text_direction") |  Displays the Text Direction of WordPress HTML pages. Consider using **blog-text_direction_boolean** instead if you want a true/false response. |
	 * | blog-text_direction_boolean | is_rtl() |  Displays true/false check if the Text Direction of WordPress HTML pages is left instead of right |
	 * | blog-language | bloginfo("language") |  Displays the language of WordPress.|
	 * | blog-stylesheet_url | get_stylesheet_uri() |  Displays the primary CSS (usually style.css) file URL of the active theme. |
	 * | blog-stylesheet_directory | bloginfo("stylesheet_directory") |  Displays the stylesheet directory URL of the active theme. (Was a local path in earlier WordPress versions.) Consider echoing get_stylesheet_directory_uri() instead.|
	 * | blog-template_url | get_template_directory_uri() |  Parent template uri. Consider using **blog-child_template_url** for the child template URI. |
	 * | blog-child_template_url | get_stylesheet_directory_uri() | Child template URI. |
	 * | blog-pingback_url | bloginfo("pingback_url") |  Displays the Pingback XML-RPC file URL (xmlrpc.php).|
	 * | blog-atom_url | bloginfo("atom_url") |  Displays the Atom feed URL (/feed/atom).|
	 * | blog-rdf_url | bloginfo("rdf_url") |  Displays the RDF/RSS 1.0 feed URL (/feed/rfd).|
	 * | blog-rss_url | bloginfo("rss_url") |  Displays the RSS 0.92 feed URL (/feed/rss).|
	 * | blog-rss2_url | bloginfo("rss2_url") |  Displays the RSS 2.0 feed URL (/feed).|
	 * | blog-comments_atom_url | bloginfo("comments_atom_url") |  Displays the comments Atom feed URL (/comments/feed).|
	 * | blog-comments_rss2_url | bloginfo("comments_rss2_url") |  Displays the comments RSS 2.0 feed URL (/comments/feed).|
	 * | login-url | wp_login_url() | Returns the Wordpress login URL. |
	 * | login-url | wp_logout_url() | Returns the Wordpress logout URL. |
	 * | current_year | date("Y") | Returns the current year. |
	 * | theme-name | $theme_info->get("Name") | Theme name as given in theme's style.css |
	 * | theme-uri | $theme_info->get("ThemeURI") | The path to the theme's directory |
	 * | theme-description | $theme_info->get("Description") | The description of the theme |
	 * | theme-author | $theme_info->get("Author") | The theme's author |
	 * | theme-author_uri | $theme_info->get("AuthorURI") | The website of the theme author |
	 * | theme-version | $theme_info->get("Version") | The version of the theme |
	 * | theme-template | $theme_info->get("Template") | The folder name of the current theme |
	 * | theme-status | $theme_info->get("Status") | If the theme is published |
	 * | theme-tags | $theme_info->get("Tags") | Tags used to describe the theme |
	 * | theme-text_domain | $theme_info->get("TextDomain") | The text domain used in the theme for translation purposes |
	 * | theme-domain_path | $theme_info->get("DomainPath") | Path to the theme translation files |
	 *
	 * @version 1.0.0
	 *
	 */
	class Redux_Shortcodes {

		// Protected vars
		protected $parent;

		/**
		 * Class Constructor. Defines the args for the extions class
		 *
		 * @since       1.0.0
		 * @access      public
		 *
		 * @param       array $parent Redux_Framework class instance
		 *
		 * @return      void
		 */
		public function __construct( $parent ) {

			if ( ! shortcode_exists( 'bloginfo' ) ) {
				add_shortcode( 'bloginfo', array( $this, 'blog_info' ) );
			}
			if ( ! shortcode_exists( 'themeinfo' ) ) {
				add_shortcode( 'themeinfo', array( $this, 'theme_info' ) );
			}
			if ( ! shortcode_exists( 'date' ) ) {
				add_shortcode( 'date', array( $this, 'date' ) );
			}

			// Allow users to extend if they want
			do_action( 'redux/shorcodes/' . $parent->args['opt_name'] . '/construct' );

		}


		/**
		 * Shortcode - blog-info
		 * @return bloginfo($data)
		 **/
		function blog_info( $atts, $content = null ) {
			if ( ! empty( $content ) && ! isset( $atts['data'] ) ) {
				$atts['data'] = $content;
			}
			switch ( $atts['data'] ) {
				case 'stylesheet_url':
					return get_stylesheet_uri();
					break;
				case 'parent_template_url':
					return get_template_directory_uri();
					break;
				case 'child_template_url':
				case 'template_url':
				case 'template_directory':
					return get_stylesheet_directory_uri();
					break;
				case 'text_direction_bool':
				case 'text_direction_boolean':
					return is_rtl();
					break;
				case 'is_multisite':
					return is_multisite();
					break;
				case 'url':
					return home_url();
					break;
				case 'root_url':
					return site_url();
					break;
				case 'stylesheet_url':
					return get_stylesheet_uri();
					break;
				case 'logout_url':
					return wp_logout_url();
					break;
				case 'login_url':
					return wp_login_url();
					break;
				default:
					return bloginfo( $atts['data'] );
					break;
			}
		}

		/**
		 * Shortcode - theme
		 * @return wp_get_theme()->{$data}
		 **/
		function theme_info( $atts, $content = null ) {
			if ( ! empty( $content ) && ! isset( $atts['data'] ) ) {
				$atts['data'] = $content;
			}
			if ( ! isset( $parent->theme_info ) || empty( $parent->theme_info ) ) {
				$this->parent->theme_info = wp_get_theme();
			}

			$keys         = array(
				'name'        => 'Name',
				'themeuri'    => 'ThemeURI',
				'theme_uri'   => 'ThemeURI',
				'theme_url'   => 'ThemeURI',
				'theme_url'   => 'ThemeURI',
				'description' => 'Description',
				'author'      => 'Author',
				'authoruri'   => 'AuthorURI',
				'authorurl'   => 'AuthorURI',
				'author_uri'  => 'AuthorURI',
				'author_url'  => 'AuthorURI',
				'version'     => 'Version',
				'template'    => 'Template',
				'status'      => 'Status',
				'tags'        => 'Tags',
				'textdomain'  => 'TextDomain',
				'text_domain' => 'TextDomain',
				'domainpath'  => 'DomainPath',
				'domain_path' => 'DomainPath'
			);
			$atts['data'] = $keys[ strtolower( $atts['data'] ) ];

			switch ( $atts['data'] ) {
				case 'stylesheet_url':
					return get_stylesheet_uri();
					break;
				case 'parent_template_url':
					return get_template_directory_uri();
					break;
				case 'child_template_url':
				case 'template_url':
				case 'template_directory':
					return get_stylesheet_directory_uri();
					break;
				case 'text_direction_bool':
				case 'text_direction_boolean':
					return is_rtl();
					break;
				case 'url':
					return home_url();
					break;
				case 'root_url':
					return site_url();
					break;
				case 'register_url':
					return wp_register_url();
					break;
				case 'lostpassword_url':
				case 'lost_password_url':
					return wp_lostpassword_url();
					break;
				case 'logout_url':
					return wp_logout_url();
					break;
				case 'login_url':
					return wp_login_url();
					break;
				case 'stylesheet_url':
					return get_stylesheet_uri();
					break;
				default:
					if ( isset( $this->parent->theme_info[ $atts['data'] ] ) ) {
						return $this->parent->theme_info[ $atts['data'] ];
					}

					break;
			}
		}

		/**
		 * Shortcode - date
		 * @return date($data)
		 **/
		function date( $atts, $content = null ) {
			if ( ! empty( $content ) && ! isset( $atts['data'] ) ) {
				$atts['data'] = $content;
			}
			if ( ! isset( $atts['data'] ) || empty( $atts['data'] ) ) {
				$atts['data'] = "Y";
			}

			return date( $atts['data'] );
		}

	} // class

endif;