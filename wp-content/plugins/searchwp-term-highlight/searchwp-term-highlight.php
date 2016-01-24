<?php
/*
Plugin Name: SearchWP Term Highlight
Plugin URI: https://searchwp.com/
Description: Highlight search terms in results
Version: 1.8.7
Author: Jonathan Christopher
Author URI: https://searchwp.com/

Copyright 2014 Jonathan Christopher

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

// exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

global $searchwp_term_highlight;

class SearchWP_Term_Highlight {
	// how many words an excerpt should be
	public $number_of_words;

	public $common;
	public $min_word_length;

	function __construct() {
		// make sure post objects are getting returned
		add_filter( 'searchwp_load_posts', array( $this, 'maybe_load_posts' ), 10, 2 );
		add_filter( 'searchwp_found_post_objects', array( $this, 'highlight_posts' ), 10, 2 );

		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), array( $this, 'plugin_row' ), 11 );

		$this->number_of_words = absint( apply_filters( 'searchwp_th_num_words', 55 ) );
		$this->set_common_words();
		$this->set_min_word_length();

		$automatically_filter_excerpt = apply_filters( 'searchwp_th_auto_filter_excerpt', true );
		if ( $automatically_filter_excerpt ) {
			add_filter( 'get_the_excerpt', array( $this, 'apply_highlight' ) );
		}
	}

	function set_min_word_length() {
		$this->min_word_length = absint( apply_filters( 'searchwp_minimum_word_length', 3 ) );
	}

	function set_common_words() {
		$common_words = array();
		if ( class_exists( 'SearchWP' ) ) {
			$searchwp = SearchWP::instance();
			$common_words = $searchwp->common;
		}
		$this->common = $common_words;
	}


	function maybe_load_posts( $load_posts, $search_args ) {
		$excluded_engines = apply_filters( 'searchwp_th_excluded_engines', array() );
		return !in_array( $search_args['engine'], $excluded_engines );
	}

	function highlight_posts( $posts, $search_args ) {
		if ( is_array( $posts ) && ! empty( $posts ) ) {
			$terms = $search_args['terms'];
			foreach ( $posts as $key => $val ) {
				$posts[$key]->post_title = $this->apply_highlight( $posts[$key]->post_title, $terms );
				$posts[$key]->post_content = $this->apply_highlight( $posts[$key]->post_content, $terms );
				$posts[$key]->post_excerpt = $this->apply_highlight( $posts[$key]->post_excerpt, $terms );
			}
		}
		return $posts;
	}

	function prep_terms( $terms ) {
		global $searchwp;
		$whitelisted_terms = array();

		// allow developers to manually define which variable should be used for the search term
		$terms = apply_filters( 'searchwp_th_query', $terms );

		if ( empty( $terms ) ) {
			$terms = get_search_query();
		}

		// make sure it's a string
		if ( is_array( $terms ) ) {
			$terms = implode( ' ', $terms );
		} else {
			$terms = (string) $terms;
		}

		// check against the regex pattern whitelist
		$terms = ' ' . $terms . ' ';
		if ( method_exists( $searchwp, 'extract_terms_using_pattern_whitelist' ) ) { // added in SearchWP 1.9.5
			// extract terms based on whitelist pattern, allowing for approved indexing of terms with punctuation
			$whitelisted_terms = $searchwp->extract_terms_using_pattern_whitelist( $terms );

			// add the buffer so we can whole-word replace
			$terms = str_replace( ' ', '  ', $terms );

			// remove the matches
			if( ! empty( $whitelisted_terms ) ) {
				$terms = str_ireplace( $whitelisted_terms, '', $terms );
			}

			// clean up the double space flag we used
			$terms = str_replace( '  ', ' ', $terms );
		}

		// rebuild our terms array
		$terms = explode( ' ', $terms );

		// maybe append our whitelist
		if ( is_array( $whitelisted_terms ) && ! empty( $whitelisted_terms ) ) {
			$whitelisted_terms = array_map( 'trim', $whitelisted_terms );
			$terms = array_merge( $terms, $whitelisted_terms );
		}

		// make sure it's an array
		if ( ! is_array( $terms ) ) {
			$terms = array( $terms );
		}

		// sanitize
		$terms = array_map( 'sanitize_text_field', $terms );
		$terms = array_filter( $terms, 'strlen' );

		return $terms;
	}

	/**
	 * This extension does the best it can to automatically highlight content retrieved in search results, but since
	 * SearchWP can search anything, there are many things that cannot be automatically highlighted such as custom field
	 * content, taxonomy terms, and comment content. This utility function aims to make highlighting that content easier
	 */
	function apply_highlight( $content, $terms = null ) {
		$terms = $this->prep_terms( $terms );

		foreach ( $terms as $term ) {
			if ( ( ! is_array( $this->common ) || ( is_array( $this->common ) && ! in_array( $term, $this->common ) ) ) && $this->min_word_length <= strlen( $term ) ) {
				$term = preg_quote( $term );
				$content = preg_replace( "/$term(?!([^<]+)?>)/i", "<span class='searchwp-highlight'>$0</span>", $content );
			}
		}
		return $content;
	}

	/**
	 * Extract an excerpt with any number of words that should include one or more of the search terms
	 */
	function the_excerpt( $terms = null ) {
		echo $this->get_the_excerpt( $terms );
		return true;
	}

	function get_the_excerpt( $terms = null, $excerpt = '', $apply_native_wp_filter = true ) {

		global $searchwp_term_highlight;

		$post = get_post();
		$terms = $this->prep_terms( $terms );

		if ( post_password_required() ) {
			return apply_filters( 'searchwp_th_password_required_message', __( 'There is no excerpt because this is a protected post.' ) );
		}

		// by default we're going to use the post excerpt (in case there are no terms in the excerpt)
		$excerpt = empty( $excerpt ) ? $post->post_excerpt : sanitize_text_field( $excerpt );

		// find the first applicable search term (based on character length)
		$flag = false;
		foreach ( $terms as $termkey => $term ) {
			if ( !in_array( $term, $this->common ) && $this->min_word_length <= strlen( $term ) ) {
				$flag = $term;
				break;
			}
		}

		// grab all of the content and break it out into a clean array
		$haystack = empty( $excerpt ) ? $post->post_content : $excerpt;
		$haystack = $searchwp_term_highlight->handle_shortcodes( $haystack );
		$haystack = strip_tags( $haystack );
		$haystack = explode( ' ', $haystack );

		// find the first occurrence of the first applicable term
		if ( ! empty( $flag ) ) {
			foreach ( $haystack as $haystack_key => $haystack_term ) {
				if ( false !== strpos( strtolower( $haystack_term ), strtolower( $flag ) ) ) {

					// our buffer is going to be 1/3 the total number of words in hopes of snagging one or two more
					// highlighted terms in the second and third thirds
					$buffer = floor( ( $this->number_of_words - 1 ) / 3 ); // -1 to accommodate the search term itself

					// find the start point
					$start = 0;
					$underflow = 0;
					if( $haystack_key < $buffer ) {
						// the match occurred too early to get a proper first buffer
						$underflow = $buffer - $haystack_key;
					} else {
						// there is enough room to grab a proper first buffer
						$start = $haystack_key - $buffer;
					}

					// find the end point
					$end = count( $haystack );
					if ( $end > ( $haystack_key + ( $buffer * 2 ) ) ) {
						$end = $haystack_key + ( $buffer * 2 );
					}

					// if we had an underflow (e.g. the first buffer wasn't fully included) grab more at the end
					$end += $underflow;

					$excerpt = array_slice( $haystack, $start, $end - $start );
					$excerpt = implode( ' ', $excerpt );
					$excerpt = $this->apply_highlight( $excerpt, $terms );

					break;
				}
			}
		}

		if ( $apply_native_wp_filter ) {
			$excerpt = apply_filters( 'get_the_excerpt', $excerpt );
		}

		return $excerpt;
	}


	function plugin_row() {
		if ( !class_exists( 'SearchWP' ) ) { ?>
			<tr class="plugin-update-tr searchwp">
				<td colspan="3" class="plugin-update">
					<div class="update-message">
						<?php _e( 'SearchWP must be active for Term Highlight to work', 'searchwp' ); ?>
					</div>
				</td>
			</tr>
		<?php
		} else {
			$searchwp = SearchWP::instance();
			if( version_compare( $searchwp->version, '1.9.5', '<' ) ) { ?>
				<tr class="plugin-update-tr searchwp">
					<td colspan="3" class="plugin-update">
						<div class="update-message">
							<?php _e( 'SearchWP Term Highlight requires SearchWP 1.9.5 or greater', 'searchwp' ); ?>
						</div>
					</td>
				</tr>
			<?php }
		}
	}

	function handle_shortcodes( $content ) {
		if ( apply_filters( 'searchwp_th_strip_shortcodes', true ) ) {
			$content = strip_shortcodes( $content );
		} else if ( apply_filters( 'searchwp_th_do_shortcode', true ) ) {
			$content = do_shortcode( $content );
		}

		return $content;
	}

}

/**
 * Automatically generate an excerpt that has at least one search term in it, whether the content is inside
 * the main editor or within any Custom Field (if the data is a string).
 */
function searchwp_term_highlight_get_the_excerpt_global( $post_id = 0, $custom_field = '', $query = null ) {

	global $post, $searchwp_term_highlight;

	$original_post = $post;

	if ( empty( $post_id ) ) {
		$post_id = $post->ID;
	}

	$excerpt = $default_excerpt = '';
	if ( empty( $custom_field ) ) {
		// retrieve the default excerpt
		$post_id = absint( $post_id );
		$post = get_post( $post_id );
		setup_postdata( $post );

		// grab all content (default excerpt and all Custom Fields) and concatenate it
		$excerpt = $default_excerpt = $searchwp_term_highlight->get_the_excerpt( $query );
	} else {
		// a custom field was specified so we're going to use that to generate the excerpt
		$custom_field = sanitize_text_field( $custom_field );
	}

	if ( empty( $custom_field ) && false === strpos( $excerpt, 'searchwp-highlight' ) ) {
		// wasn't found in the main excerpt, so we're going to loop through the Custom Fields until we find one
		// custom fields next
		$custom_fields = get_post_custom( $post_id );
		if ( ! empty( $custom_fields ) ) {
			$better_excerpt = false;
			foreach( $custom_fields as $custom_field_name => $custom_field_value ) {
				// exclude all the keys that are excluded in SearchWP itself
				$excluded_custom_field_keys = apply_filters( 'searchwp_excluded_custom_fields', array(
						'_edit_lock',
						'_wp_page_template',
						'_edit_last',
						'_wp_old_slug',
						'_searchwp_indexed',
						'_searchwp_last_index',
					) );

				if ( ! in_array( $custom_field_name, $excluded_custom_field_keys ) && ! empty( $custom_field_value ) ) {
					// get_post_custom does not return single records, so everything is an array
					if ( isset( $custom_field_value[0] ) && is_string( $custom_field_value[0] ) && ! empty( $custom_field_value[0] ) ) {
						// check for a highlight
						$this_custom_field_value = $custom_field_value[0];
						$this_custom_field_value = $searchwp_term_highlight->handle_shortcodes( $this_custom_field_value );
						$excerpt = $searchwp_term_highlight->get_the_excerpt( $query, $this_custom_field_value, false );
						if ( false !== strpos( $excerpt, 'searchwp-highlight' ) ) {
							$better_excerpt = true;
							break;
						}
					}
				}
			}
			if( ! $better_excerpt ) {
				$excerpt = $default_excerpt;
			}
		}
	} elseif ( ! empty( $custom_field ) ) {
		$custom_field_value = get_post_custom( $post_id, $custom_field, true );
		$excerpt = $searchwp_term_highlight->get_the_excerpt( $query, $custom_field_value[0], false );
	}

	// reset the post object
	$post = $original_post;

	// return the best excerpt we could find...
	return $excerpt;

}


function searchwp_term_highlight_the_excerpt_global( $post_id = 0 , $custom_field = '', $query = null ) {
	echo searchwp_term_highlight_get_the_excerpt_global( $post_id, $custom_field, $query );
}

// instantiate
$searchwp_term_highlight = new SearchWP_Term_Highlight();
