### 2.2.1
- **[Improvement]** Better handling of indexer stall check
- **[Improvement]** Switched Admin Bar entry from 'Currently Being Indexed' to 'In index queue' for accuracy's sake
- **[Improvement]** Better handling of delta updates prior to the initial index being built
- **[Improvement]** Better implementation of `searchwp_exclusive_regex_matches` usage, matches are now extracted earlier resulting in more concise results
- **[Fix]** Fixed an issue where manually edited PDF content would be overwritten by a subsequent delta index update after saving
- **[Fix]** Fixed an issue that may have prevented the indexer from fully waking up when waking up the indexer
- **[Fix]** Fixed a false positive when checking for WPML Integration
- **[Fix]** Fixed an issue with Xpdf Integration not saving the extracted text


### 2.2
- **[New]** New class: `SearchWP_Stats` which will eventually house a number of utility methods for better statistics as development continues
- **[New]** SearchWP will now detect if you're running a plugin that has an integration Extension available and tell you about it
- **[New]** New Filter: `searchwp_omit_meta_key` allows developers to omit specific meta keys from being indexed during indexing
- **[Improvement]** Hardened the indexer communication process, reducing server resource consumption during indexing
- **[Improvement]** Better handling of regex whitelist matches that result with multi-word tokens (e.g. spaces within) NOTE: having multi-word matches is not recommended
- **[Improvement]** Added `$engine` parameter to `searchwp_query_orderby` filter
- **[Improvement]** Simplified the check for a stalled indexer
- **[Improvement]** Multi-term regex whitelist matches will no longer be tokenized but indexed as a whole for better phrase-matching
- **[Fix]** Fixed an issue where Heartbeat index time updates were not prefixed with "Last indexed"
- **[Fix]** Fixed an issue where the debugger would not properly instantiate thereby preventing additions to the log file
- **[Fix]** Fixed an issue where Heartbeat API-powered timestamp of last index was missing "Last Indexed" phrasing
- **[Fix]** Fixed an issue where in some circumstances content blocks parsed from PDFs would not be properly separated, resulted in the last word of one section being lumped together with the first word of the next section
- **[Fix]** Prevent over-preparation of terms when performing AND logic refinement
- **[Fix]** Check for indexer being disabled when issuing delta updates


### 2.1.3
- **[Improvement]** Better encoding and font support for PDF content extraction
- **[Improvement]** Reduced memory footprint when not indexing PDFs
- **[Fix]** Fixed an issue where `searchwp_settings_cap` was not properly applied
- **[Fix]** Reduced aggressiveness when tokenizing PDF content
- **[Fix]** Fixed thrown exception when parsing specific PDF encodings
- **[Fix]** Fixed a PHP 5.2 issue
- **[Fix]** Corrected an include path for ElementXRef.php


### 2.1
- **[Improvement]** Significant query performance improvement in AND logic pass
- **[Improvement]** Much improved PDF content extraction when using only PHP as opposed to Xpdf Integration (requires PHP5.3+ else SearchWP will fall back to previous method)
- **[New]** New Filter: `searchwp_settings_cap` allows you to customize the capability required to manage SearchWP's settings in the WordPress admin
- **[New]** You can now bulk-reintroduce posts that failed indexing


### 2.0.4
- **[New]** New regex whitelist pattern to support ampersand-joined terms (e.g. M&M)
- **[Fix]** Fixed an issue where toggling whether the indexer was enabled/disabled would sometimes conflict if not done on the SearchWP settings screen
- **[Improvement]** Fixed an issue where umlaut's were incorrectly removed from PDF content when extracted using internal (PHP-based) method
- **[Improvement]** Theme conflict detection now takes into account single line comments (does not cover all commented use cases)
- **[Improvement]** Improved term processing when using built in sanitization prior to searches


### 2.0.3
- **[New]** New Filter: `searchwp_get_custom_fields` allowing developers to pre-fetch (and set) post metadata just before indexing takes place (props Stefan Hans Schonert)
- **[New]** New Filter: `searchwp_term_in` allowing you to modify each term (per term) used in the main search algorithm
- **[Improvement]** Better handling of filtered terms, allowing extensions more control over the actual search query
- **[Improvement]** `searchwp_indexer_loopback_args` is now applied to every HTTP call SearchWP makes


### 2.0.2
- **[New]** New Filter: `searchwp_statistics_cap` allows you to filter which capability is required to view and interact with stats
- **[Improvement]** Ignored queries in search statistics are now stored per user, not globally


### 2.0.1
- **[Fix]** Fixed an issue introduced in 2.0 that prevented the uninstallation routine from properly executing when not using multisite
- **[Improvement]** Resolved an issue in certain hosting environments that may have prevented the indexer from running


### 2.0
- **[New]** Shortcode processing: SearchWP can now process your Shortcodes in a number of ways
- **[New]** New Filter: `searchwp_do_shortcode` allows you to conditionally tell SearchWP to process all Shortcodes
- **[New]** New Filter: `searchwp_set_post` allows you to modify each post object prior to indexing, allowing for conditional processing of Shortcodes and more
- **[New]** New Filter: `searchwp_nuke_on_delete` to trigger Nuke on Delete (*overrides setting!*)
- **[New]** New Filter: `searchwp_exclusive_regex_matches` to force the indexer to prevent indexing exploded regex matches
- **[New]** New Filter: `searchwp_omit_meta_key_{custom_field_key}` allowing for per-key conditional exclusion of post meta when indexing
- **[Improvement]** Refined regex whitelist pattern for matching hyphen-separated-strings
- **[Improvement]** The indexer no longer strips regex matches but instead retains them to better facilitate partial matches
- **[Improvement]** Better language for action/conflict notices
- **[Improvement]** You can now restore dismissed action/conflict notices in case you want to view them again
- **[Improvement]** Slight update to the settings UI (better active tab contrast, lessened border radii)
- **[Improvement]** Better exposure of Statistics feature
- **[Improvement]** Uninstallation routine now better respects multisite environment
- **[Improvement]** You can now exclude by terms that have not been used yet
- **[Improvement]** Better default exclusion of internal metadata when indexing
- **[Fix]** Fixed an issue where regex whitelist matches were not extracted from supplemental search queries during sanitization
- **[Fix]** Fixed an issue that might result in duplication of terms that are integers in the index
- **[Fix]** Fixed a potential issue (during updates) where Supplemental search engine labels would inadvertently have their first letter converted to an 'A'
- **[Fix]** Redundant preparation of search terms when checking for exclusions by weight of -1
- **[Fix]** PHP Warning cleanup


### 1.9.11
- **[Improvement]** Added a regex whitelist pattern for hyphen-separated strings often used for serial numbers
- **[Improvement]** Reduced the overhead of term extraction when processing the regex whitelist which might cause long posts with many regex matches to stall the indexer


### 1.9.10
- **[Fix]** Fixed a regression in version 1.9.8 that prevented the installation of new plugins from .org


### 1.9.9
- **[Fix]** Fixed an issue where extended term-chunking of long posts may not have completed properly


### 1.9.8
- **[Fix]** Fixed an issue where the changelog would not be visible when clicking 'view version details' links
- **[Change]** Automatic load monitoring is again enabled by default
- **[Improvement]** The notice that outputs an indication of posts that failed to index now respects purposefully excluded post IDs via `searchwp_prevent_indexing` filter


### 1.9.7
- **[New]** New Filter: `searchwp_index_comments` allows you to prevent comments from being indexed
- **[Improvement]** Prevented potential edge case where the indexer may stall after completing a delta update
- **[Improvement]** More aggressive implementation of term regex whitelist (matches are now indexed fully in tact and not broken apart)
- **[Fix]** Fixed an issue where problematic posts that failed to index were not properly called out in the WordPress admin
- **[Fix]** Fixed an issue where 'Any' Custom Field may not have applied correctly


### 1.9.5
- **[Fix]** Fixed an issue where `searchwp_in_admin` may not properly hijack search results in the WordPress admin as desired
- **[Improvement]** Only return results from the post type being viewed when `searchwp_in_admin` is enabled and performing a search
- **[Improvement]** Additional optimization and segmentation of settings where appropriate to prevent potential collision
- **[Improvement]** Use the Heartbeat API to dynamically update the Last Indexed time when on post edit screens


### 1.9.4
- **[Fix]** Fixed a CSS rendering issue in Firefox on the Search Stats page
- **[Improvement]** Hardened settings getting and setting mechanism
- **[New]** New Filter: `searchwp_show_conflict_notices` allows you to force-hide any conflict warnings generated by SearchWP


### 1.9.2
- **[New]** Added a number of actions to allow developers to react to various phases of indexing
- **[Fix]** Fixed an issue where setting -1 posts per page was incorrectly utilized when performing searches
- **[Fix]** Fixed an issue that prevented SearchWPSearch instantiation when AJAX calls were made
- **[Improvement]** Modified default term whitelist rules to be more targeted
- **[Improvement]** Reduced the number of notifications displayed upon activation
- **[Improvement]** Reduced the number of queries necessary to store/retrieve various settings
- **[Improvement]** Better data storage so as to work alongside various object caching plugins without stalling the indexer


### 1.9
- **[New]** You can now ignore queries from Search Stats (to help avoid spam getting in the way)
- **[New]** Term Whitelisting: you can now define regex patterns and in doing so better retain specially formatted terms (e.g. dates, phone numbers, function names) in the index that otherwise would have been stripped of punctuation
- **[New]** New Filter: `searchwp_query_orderby` allows open-ended customization of the `ORDER BY` clause of the main search query
- **[New]** New Filter: `searchwp_force_run` allows developers the ability to force SearchWP to run no matter what
- **[New]** New Filter: `searchwp_leinant_accents` allowing for 'lazy' quotes (e.g. searches without quotes will find terms with quotes)
- **[Improvement]** PHP Error cleanup
- **[Improvement]** Revisited index table indices, they're now better optimized which should result in noticeable performance improvements
- **[Improvement]** Load monitoring has been removed as it proved to be holding back the indexer resulting in delayed index times
- **[Improvement]** Attachment indexing has been disabled by default to save the (significant) overhead, but it will enable itself if any of your search engine settings incorporate Media
- **[Improvement]** Refined the number of posts indexed per indexer passed, as always this can be filtered
- **[Improvement]** Reduced the information overload present in the debug log, allowing for easier scanning for issues
- **[Improvement]** Offloaded AJAX handlers to minimize footprint and impact on the indexer
- **[Improvement]** Fixed overflow issues on the Search Stats page
- **[Improvement]** Informational notice linking directly to more information on Filters having everything to do with indexer configuration
- **[Improvement]** Better detection of parallel indexer processes running that could have resulted in duplicate indexing
- **[Improvement]** Indexer pause/unpause has been re-named enable/disable to reduce confusion
- **[Fix]** Fixed an issue where update notifications wouldn't show up in the Network Administration on Multisite


### 1.8.4
- **[Improvement]** Better handling of serialized objects which resulted in __PHP_Incomplete_Class errors
- **[Improvement]** Better enforcement of maximum term length when indexing as defined by the database schema
- **[Improvement]** Better handling of Deadlock cases when indexing
- **[Improvement]** Improved default common/stop words


### 1.8.3
- **[Fix]** Cleanup of PHP Warnings
- **[New]** New Filter: `searchwp_outside_main_query` allowing for specific overrides when performing native searches
- **[Improvement]** Updated translation files (accurate translations will be rewarded, please contact info@searchwp.com)


### 1.8.2
- **[Fix]** Fixed an issue where update notifications would not persist properly


### 1.8.1
- **[Fix]** Fixed an issue where, in certain cases, weight attribution (or a lack thereof) would cause searches to fail


### 1.8
- **[New]** You can now include a LIKE modifier (%) in Custom Field keys (essentially supporting ACF Repeater field (and similar plugins) data storage)
- **[New]** SearchWP will now attempt to detect potential conflicts and add notices in the WordPress admin when it finds potential problems
- **[Fix]** Fixed an issue where custom keyword stemmers would only be used during indexing, not searching
- **[Improvement]** The Custom Fields dropdown on the settings page is no longer limited to the 25 most-used meta keys
- **[Improvement]** The Custom Fields dropdown now uses select2 to make it easier to quickly select your desired meta key
- **[Improvement]** Various improvements to the main settings screen styles
- **[Improvement]** Fixed an issue where Custom Field meta keys would be over-sanitized when saved in the SearchWP options
- **[Improvement]** Update checks are performed less aggressively, reducing some cases of increased latency in the WordPress admin
- **[Improvement]** Better singleton instantiation, fixing an issue with localization and certain hook utilization
- **[Improvement]** Scheduled events are now properly removed upon plugin deactivation
- **[Improvement]** Reduction in query overhead when multiple Custom Field meta keys have the same weight
- **[Improvement]** Reduction in query overhead when multiple taxonomies have the same weight
- **[Improvement]** Refactored the main query algorithm so as to improve maintainability and stability over time
- **[Improvement]** Formatting improvements, code quality improvements
- **[Improvement]** Updated translation files (accurate translations will be rewarded, please contact info@searchwp.com)


### 1.7.2
- **[New]** New Extension: Term Highlight - highlight search terms when outputting results!
- **[New]** New Filter: `searchwp_found_post_objects` allowing for the customization of Post objects returned by searches
- **[Fix]** Fixed an issue where overriding SearchWP File Content would be overwritten when the post got reindexed
- **[Fix]** Hotfix for a potential update issue, if you do not see an update notification in your dashboard, please download from your Account


### 1.7
- **[New]** There is a new Advanced option called Nuke on Delete that adjusts the uninstallation routine to only remove data if you opt in
- **[Improvement]** AND logic queries now force their own index
- **[Improvement]** Removed unused constant
- **[Improvement]** Offloaded update checks to only occur in the admin and therefore reduce overhead on the front end
- **[Improvement]** Update checks are now performed once a day so as to reduce page load latency when working in the WordPress admin
- **[Improvement]** Changed the way license checks were performed to avoid potential unwanted caching issues
- **[Fix]** Fixed invalid textdomain usage for l18n


### 1.6.10
- **[Fix]** Fixed an issue where keyword weights between 0 and 1 were converted to integers
- **[New]** New Filter: `searchwp_post_statuses` allowing for the customization of which post statuses are considered when indexing and searching


### 1.6.9
- **[Fix]** Fixed an issue that may have generated a SQL error after late term sanitization when performing searches
- **[Fix]** Fixed an issue that caused taxonomies to be omitted in searches by default upon activation


### 1.6.8
- **[Fix]** Fixed a regression introduced in 1.6.7 that prevented the 'last indexed' statistic from being properly maintained
- **[New]** New Filter: `searchwp_extra_metadata` allowing you to force additional content into the index per post


### 1.6.7
- **[Change]** Background indexing process has been updated to better accommodate maintenance mode plugins
- **[Fix]** Fixed an issue that may have prevented result exclusion given weight of -1 in some cases
- **[Improvement]** Reduced the overhead of the logs table (note: search stats will be reset to accommodate this)
- **[Improvement]** Miscellaneous code reorganization and optimization


### 1.6.6
- **[New]** New Advanced option to reset Search Stats


### 1.6.5
- **[Improvement]** Better appropriate suppression of WP_Query filters in internal calls
- **[Improvement]** Admin Bar entry better labels whether the indexer is paused


### 1.6.4
- **[Fix]** Admin bar entries now only show up when browsing the WordPress admin and the current user can `update_options`
- **[Fix]** Fixed an issue where overwriting the stored PDF content may not have properly taken place
- **[Improvement]** Initial AND logic pass now assumes keyword stem to create a better initial results pool
- **[Improvement]** When debugging is enabled, an HTML comment block is output at the bottom of pages to indicate what took place during that pageload
- **[Improvement]** Asset URLs in the admin now better respect alternative placement (props Jason C.)


### 1.6.3
- **[Fix]** Fixed an issue where AND logic was wrongly applied to single term searches if you set `searchwp_and_logic_only` to be true
- **[Fix]** Fixed an issue where `searchwp_posts_per_page` was not properly applied to WordPress native searches
- **[New]** New Filter: `searchwp_big_selects` for cases where SearchWP breaches your MySQL-defined `max_join_size`


### 1.6.2
- **[Fix]** Fixed a PHP 5.2 compatibility error (T_PAAMAYIM_NEKUDOTAYIM)


### 1.6.1
- **[Fix]** Fixed an error on plugin deletion via WP admin
- **[Fix]** Fixed an issue where (if you opt to disable automatic delta updates) the queue could be overwritten in some cases
- **[Fix]** Fixed an issue where (in certain circumstances) searches for values only in Custom Field data may yield no results


### 1.6
- **[New]** Added indexer pause toggle to Admin Bar
- **[New]** New Filter: `searchwp_custom_fields` allowing you parse custom fields, telling SearchWP what content you want to be indexed
- **[New]** New Filter: `searchwp_custom_field_{$customFieldName}` performs the same filtration, but for a single Custom Field
- **[New]** New Filter: `searchwp_excluded_custom_fields` allowing you to customize which meta_keys are automatically excluded during indexing
- **[New]** New Filter: `searchwp_background_deltas` allowing you to disable automatic delta index updates (you would then need to set up your own via WP-Cron or otherwise, useful for high traffic sites)
- **[New]** New Filter: `searchwp_weight_threshold` allowing you to specify a minimum weight for search results to be considered (default is zero)
- **[New]** New Filter: `searchwp_indexed_post_types` allowing you to specify which post types are indexed (note: this controls only what post types are indexed, it has no effect on enabling/disabling post types on the SearchWP Settings screen)
- **[New]** New Filter: `searchwp_return_orderby_random` allowing search results to be returned at random
- **[Improvement]** Indexer optimizations in a number of places, index builds should be even faster (and more considerate of server resources)
- **[Improvement]** Auto-throttling now takes into account your max_execution_time so as to not exceed it
- **[Improvement]** Indexer now scales how many terms are processed per pass based on your memory_limit (can still be overridden)
- **[Improvement]** Better handling of potential table deadlock when indexing
- **[Improvement]** Overall reduction in memory usage when indexing
- **[Fix]** Fixed an off-by-one issue when filtering terms by minimum character length when parsing search terms
- **[Fix]** Fixed an issue where the progress meter would automatically dismiss itself after purging the index


### 1.5.5
- **[Improvement]** Better performance on a number of queries throughout
- **[Improvement]** SearchWP will now monitor load averages (on Linux machines) and auto-throttle when loads get too high
- **[Change]** The default indexer pass timeout (throttle) is now 1 second
- **[Fix]** Fixed an issue where Media may not be indexed on upload
- **[Fix]** Fixed an issue where terms in file names may be counted twice when indexing
- **[Improvement]** Many more logging messages included, logs now include internal process identification
- **[Fix]** Fixed an issue with non-blocking requests and their associated timeouts potentially stalling the indexing process


### 1.5
- **[New]** Admin Bar entry (currently displays the last time the current post was indexed)
- **[New]** New Filter: `searchwp_admin_bar` to allow you to disable the Admin Bar entry if you so choose
- **[New]** New Filter: `searchwp_indexer_throttle` allows you to tell the indexer to pause a number of seconds in between passes
- **[Fix]** PHP Warning cleanup
- **[Fix]** Fixed an issue where keyword stems were not fully utilized in AND logic passes
- **[Fix]** Fixed an issue where attachments may not be properly reindexed after an edit
- **[Fix]** Better index cleanup of deleted Media
- **[Improvement]** SearchWP's indexer will now automatically pause/unpause when running WordPress Importer


### 1.4.9
- **[Fix]** Fixed a regression that removed the `searchwp_and_logic_only` filter


### 1.4.8
- **[Fix]** Fixed an issue where the default comment weight was not properly set on install
- **[Improvement]** Better handling of additional reduction of AND pool results
- **[New]** New Filter: `searchwp_return_orderby_date` to allow developers to return results ordered by date instead of weight


### 1.4.7
- **[Fix]** Fixed an issue where the minimum word length was not taken into consideration when sanitizing terms


### 1.4.6
- **[Improvement]** More precise refactor of AND logic to prevent potential false positives
- **[New]** New Filter: `searchwp_and_fields` allows you to limit which field types apply to AND logic (i.e. limit to Title)


### 1.4.5
- **[Fix]** Fixed potential PHP Warnings
- **[New]** You can now use weights of -1 to forcefully *exclude* matches
- **[New]** By default SearchWP will now ignore WordPress core postmeta (e.g. `_edit_lock`)
- **[New]** New Filter: `searchwp_omit_wp_metadata` to include WordPress core postmeta in the index


### 1.4.4
- **[Fix]** Better coverage of deferred index delta updates


### 1.4.3
- **[Improvement]** Better handling of refinement of AND logic in more cases
- **[Improvement]** Better handling of forced AND logic when it returns zero results


### 1.4.2
- **[Fix]** Fixed a potential issue where the search algorithm refinement may be too aggressive
- **[New]** New Filter: `searchwp_custom_stemmer` to allow for custom (usually localized) stemming. *Requires a re-index if utilized*


### 1.4.1
- **[New]** New filter: `searchwp_and_logic_only` to allow you to explicity force SearchWP to use AND logic only
- **[New]** New filter: `searchwp_refine_and_results` tells SearchWP to further restrict AND results to titles
- **[New]** New filter: `searchwp_max_and_results` to allow you to tell SearchWP when you want it to refine AND results


### 1.4
- **[New]** Added a new Advanced setting to allow you to pause the indexer without deactivating SearchWP
- **[New]** New filter: `searchwp_include_comment_author` allows you to enable indexing of comment author
- **[New]** New filter: `searchwp_include_comment_email` allows you to enable indexing of comment author email
- **[New]** New filter: `searchwp_auto_reindex` to allow you to disable automatic reindexing of edited posts
- **[New]** New filter: `searchwp_indexer_paused` to allow you to override the Advanced setting programmatically
- **[Fix]** Fixed an issue where comments were not accurately indexed
- **[Improvement]** Improved stability of results when multiple posts have the same final weight by supplementally sorting by post_date DESC


### 1.3.6
- **[New]** New filter: `searchwp_short_circuit` to allow you to have SearchWP *not* run at runtime. Useful when implementing other plugins that utilize search.


### 1.3.5
- **[Improvement]** Implemented workaround for issues experienced with Post Types Order that prevented search results from appearing


### 1.3.4.1
- **[Fix]** Fixed a bug with weight attribution that resulted from the update in 1.3.4


### 1.3.4
- **[Fix]** Fixed an issue where taxonomy/custom field weight may not have been appropriate attributed when applicable
- **[Fix]** Fixed an issue where 'Any' custom field weight may not have been appropriately applied
- **[Improvement]** Remote debugging info now updates more consistently
- **[Improvement]** Additional method for remote debugging


### 1.3.3
- **[New]** Initial implementation of Remote Debugging (found in Advanced settings)
- **[New]** Extension: Xpdf Integration
- **[New]** New filter: `searchwp_external_pdf_processing` to allow you to use your own PDF processing mechanism
- **[Improvement]** Less strict AND logic used in the main query
- **[Improvement]** Better database environment check (needed as a result of [MySQL bug 41156](http://bugs.mysql.com/bug.php?id=41156))
- **[Improvement]** Additional cleanup of SearchWP options during uninstallation
- **[Improvement]** Force license deactivation during uninstallation


### 1.3.2
- **[New]** SearchWP now defaults to AND logic with search terms, huge performance boost as a result (note: if no posts are found via AND, OR will be used)
- **[New]** New filter: `searchwp_and_logic` to revert back to OR logic
- **[Fix]** Fixed an issue with the new deferred index updates


### 1.3.1
- **[New]** Added ability to 'wake up' the indexer if you feel it has stalled
- **[Fix]** Fixed an issue where Custom Field weights would not be properly retrieved if there were capital letters in the key
- **[Fix]** Fixed an issue with cron scheduling and management
- **[Improvement]** Delta index updates are now performed in the background
- **[Improvement]** Reduced latency between indexer passes
- **[Improvement]** Better tracking of repeated passes on lengthy index entries
- **[Improvement]** Better accommodation of potential indexer pass overlapping and in doing so reduce the liklihood of database table deadlocking
- **[Improvement]** Better notifications regarding license activation
- **[Improvement]** More useful debugger logging
- **[Improvement]** Better index progress display after purging the index


### 1.3
- **[New]** New filter: `searchwp_search_query_order` to allow changing the search query order results at runtime
- **[New]** New filter: `searchwp_max_index_attempts` to allow control over how many times the indexer should try to index a post
- **[New]** New filter: `searchwp_prevent_indexing` to allow exclusion of post IDs from the index process entirely
- **[Improvement]** Better low-level interception of WordPress query process so as to accommodate other plugin workflows
- **[Improvement]** Better realtime monitoring of indexer progress
- **[Improvement]** Better detection and handling of troublesome posts when indexing
- **[Improvement]** Better handling of 'initial index complete' notification after purging and reindexing
- **[Improvement]** Cleaned up purging and uninstallation routines


### 1.2.5
- **[Improvement]** Search statistics are no longer reset along with purging the index
- **[Improvement]** Better options cleanup both during index purges and uninstallations
- **[Improvement]** Improvement in overall indexer performance, not by a large margin, but some


### 1.2.4
- **[Fix]** Fixed an issue where the database environment check was too aggressive and prevented activation before the environment was set up


### 1.2.3
- **[Fix]** Fixed an issue where numeric Custom Field data was not indexed accurately
- **[Improvement]** Better detection for custom database table creation


### 1.2.2
- **[Improvement]** Better accommodation for regression in 1.2.0 that prevented proper taxonomy exclusion


### 1.2.1
- **[Fix]** Fixed an issue where index progress indicator could exceed 100% after disabling attachment indexing
- **[Fix]** Fixed an issue where category exclusion would not always apply to search engine settings


### 1.2.0
- **[Improvement]** Overall reduction in query time when performing searches (sometimes down to 50%!)
- **[Improvement]** Indexing process now handles huge posts in a more efficient way, avoiding PHP timeouts
- **[Improvement]** Better handling of term indexing resulting in a more accurate index
- **[Fix]** Fixed an issue where term IDs were not pulled properly during indexing
- **[Change]** Changed the default weight for Titles so as to better meet user expectations


### 1.1.2
- **[Fix]** Fixed an issue where the WordPress database prefix was hardcoded in certain situations
- **[Improvement]** Removed redundant SQL call resulting in faster search queries


### 1.1.1
- **[Improvement]** More parameters passed to just about every SearchWP hook, please view the documentation for details


### 1.1
- **[New]** A more formal integration of Extensions such that settings screens can be added
- **[New]** You can now limit Media search results by their type (e.g. search only Documents)
- **[New]** Extension: Term Synonyms - manually define term synonyms
- **[New]** Extension: WPML Integration
- **[New]** Extension: Polylang Integration
- **[New]** Extension: bbPress Integration
- **[New]** New filter `searchwp_include` that accepts an array of limiting post IDs during searches
- **[New]** New filter `searchwp_query_main_join` to allow custom joining during the main search query
- **[New]** New filter `searchwp_query_join` to allow custom joining in per-post-type subqueries when searching
- **[New]** New filter `searchwp_query_conditions` to allow custom conditions in per-post-type subqueries when searching
- **[New]** New filter `searchwp_index_attachments` to allow you to disable indexing of Attachments entirely to save index time
- **[Improvement]** Major reduction in query time if you choose to NOT include Media in search results (or limit Media to Documents)
- **[Improvement]** Better edge case coverage to the indexing process; it's now less likely to stall arbitrarily
- **[Improvement]** Better delta index updates by skipping autosaves and revisions more aggressively
- **[Improvement]** Fixed a UI issue where the CPT column on the settings screen may expand beyond the right hand column
- **[Improvement]** Better default weights


### 1.0.10
- **[New]** Added filter `searchwp_common_words`
- **[New]** Added filter `searchwp_enable_parent_attribution_{posttype}`
- **[New]** Added filter `searchwp_enable_attribution_{posttype}`


### 1.0.9
- **[Improvement]** Better cleaning and processing of taxonomy terms
- **[Improvement]** Additional parameter when invoking SearchWPSearch for 3rd party integrations (props Matt Gibbs)
- **[Fix]** Fixed an issue with Firefox not liking SVG files


### 1.0.8
- **[Fix]** Fixed an issue where duplicate terms could get returned when sanitizing
- **[New]** Extension: Fuzzy Searching
- **[New]** Extension: Term Archive Priority
- **[New]** Added filter `searchwp_results` to faciliate filtration of results before they're returned
- **[New]** Added filter `searchwp_query_limit_start` to allow offsetting the main query results
- **[New]** Added filter `searchwp_query_limit_total` to allow offsetting the main query results
- **[New]** Added filter `searchwp_pre_search_terms` to allow filtering search terms before searches run
- **[New]** Added filter `searchwp_load_posts` so as to prevent weighty loading of all post data when all you want is IDs (props Matt Gibbs)
- **[Improvement]** More arguments passed to searchwp_before_query_index and searchwp_after_query_index actions


### 1.0.7
- **[NOTICE]** Due to an indexer update, it is recommended that you purge your index after updating
- **[Improvement]** Better, more performant indexer behavior during updates
- **[Improvement]** Added logging for supplemental searches
- **[Improvement]** Better punctuation handling during indexing and searching
- **[Improvement]** Better cleanup of stored options when applicable
- **[Fix]** Better logging of original search queries compared to what actually gets sent through the algorithm
- **[Fix]** Fixed potential PHP warning


### 1.0.6
- **[Improvement]** Better handling of source code-related indexing and searching
- **[New]** Added filter `searchwp_engine_settings_{$engine}` to allow adjustment of weights at runtime
- **[New]** Added filter `searchwp_max_search_terms` to cap the number of search terms that can be searched for (default 6)
- **[New]** Added filter `searchwp_max_search_terms_supplemental` to cap the number of terms for supplemental searches
- **[New]** Added filter `searchwp_max_search_terms_supplemental_{$engine}` to cap the number of terms for supplemental searches by engine
- **[Fix]** Fixed an issue with empty search queries showing up in search stats
- **[Fix]** Fixed an issue with CSS alignment of search stats
- **[Fix]** Fixed an issue where the indexer would index and then re-index posts when not needed
- **[Fix]** Fixed a MySQL error when logging indexer actions


### 1.0.5
- **[Change]** Updated user-agent of indexer background process for easier debugging
- **[New]** Added initial support for common debugging assistance via searchwp_log action
- **[Improvement]** Better support for WordPress installations in subdirectories
- **[Improvement]** If the initial index is already built by the time you go from activation to settings screen, a notice is displayed
- **[Improvement]** Better support for generating your own pagination with supplemental searches http://d.pr/MXgp
- **[Fix]** Stopped 'empty' search queries from being logged
- **[New]** Added filter `searchwp_index_chunk_size` to adjust how many posts are indexed at a clip


### 1.0.4
- **[Fix]** Much better handling of all UTF-8 characters both when indexing and when searching


### 1.0.3
- **[Fix]** Fixed an issue with the auto-update script not resolving properly
- **[Improvement]** Better handling of special characters both when indexing and querying


### 1.0.2
- **[Fix]** Fixed an issue where Custom Field weights weren't saving properly on the Settings screen


### 1.0.1
- **[Fix]** Fixed an issue that would cause searches to fail if an enabled custom post type had a hyphen in it's name
- **[Fix]** Fixed an off-by-one issue in generating statistical figures


### 1.0.0
- Initial release
