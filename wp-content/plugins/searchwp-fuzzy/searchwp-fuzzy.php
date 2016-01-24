<?php
/*
Plugin Name: SearchWP Fuzzy Matches
Plugin URI: https://searchwp.com/
Description: Fuzzy matching for search terms and primitive spelling error affordance
Version: 1.1
Author: Jonathan Christopher
Author URI: https://searchwp.com/

Copyright 2013-2014 Jonathan Christopher

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

class SearchWPFuzzy {

	function __construct() {
		add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), array( $this, 'plugin_row' ), 11 );

		add_filter( 'searchwp_term_in', array( $this, 'find_fuzzy_matches' ), 10, 2 );
	}

	function find_fuzzy_matches( $terms, $engine ) {
		global $wpdb, $searchwp;

		if( ! class_exists( 'SearchWP' ) || version_compare( $searchwp->version, '2.0.3', '<' ) ) {
			return $terms;
		}

		$prefix = $wpdb->prefix;

		// there has to be at least a term
		if( ! is_array( $terms ) || empty( $terms ) ) {
			return $terms;
		}

		// dynamic minimum character length
		$minCharLength = absint( apply_filters( 'searchwp_fuzzy_min_length', 5 ) ) - 1;

		$sql = "SELECT term FROM {$prefix}swp_terms WHERE CHAR_LENGTH(term) > {$minCharLength} AND (";

		// need to query for fuzzy matches in terms table and append them
		$count = 0;
		foreach( $terms as $term ) {
			$term = str_replace( "'", '', $wpdb->prepare( "%s", $term ) );

			if( $count > 0 ) $sql .= " OR ";

			$sql .= "
				( term LIKE '%{$term}%'
				OR reverse LIKE CONCAT(REVERSE( '{$term}' ), '%') ";

			// check for the number of digits (e.g. SKUs being sent through would result in disaster)
			preg_match_all( "/[0-9]/", $term, $digits );
			$percentDigits = ! empty( $digits ) && isset( $digits[0] ) ? ( count( $digits[0] ) / strlen( $term ) ) * 100 : 0;

			$percentDigitsThreshold = absint( apply_filters( 'searchwp_fuzzy_digit_threshold', 10 ) );
			if( $percentDigits < $percentDigitsThreshold ) {
				$sql .= " OR SOUNDEX(term) LIKE SOUNDEX( '{$term}' ) ";
			}

			// close it up
			$sql .=" ) ";
			$count++;
		}
		$sql .= ")";

		$wickedFuzzyTerms = $wpdb->get_col( $sql );

		// depending on whether we actually used SOUNDEX, we need to trim out potential results
		// determine whether each match should be included based on how many characters match
		$threshold = absint( apply_filters( 'searchwp_fuzzy_threshold', 85 ) );
		if( $threshold > 100 ) $threshold = 100;

		// loop through all of the wicked fuzzy terms and pluck out what's really relevant
		$actualTerms = array();
		foreach( $wickedFuzzyTerms as $wickedFuzzyTerm ) {
			foreach( $terms as $term ) {
				similar_text( $wickedFuzzyTerm, $term, $percent );
				if( $percent > $threshold )
					$actualTerms[] = $wickedFuzzyTerm;
			}
		}

		// clean up our dupes
		if( ! empty( $actualTerms ) ) {
			$terms = array_values( array_unique( $actualTerms ) );
			$terms = array_map( 'sanitize_text_field', $terms );
		}

		return $terms;
	}

	function plugin_row() {
		if( !class_exists( 'SearchWP' ) ) {
			return;
		}

		$searchwp = SearchWP::instance();
		if( version_compare( $searchwp->version, '2.0.3', '<' ) ) { ?>
			<tr class="plugin-update-tr searchwp">
				<td colspan="3" class="plugin-update">
					<div class="update-message">
						<?php _e( 'SearchWP Fuzzy Matches requires SearchWP 2.0.3 or greater', $searchwp->textDomain ); ?>
					</div>
				</td>
			</tr>
		<?php }
	}

}

new SearchWPFuzzy();
