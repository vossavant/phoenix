<?php
include_once('simple_html_dom.php');

function scrape_goodreads($url) {
	$quote_array = array();
	$html = new simple_html_dom();
	$html->load_file($url);

	$quote_text = $html->find('.leftContainer .quote .quoteText');

	foreach ($quote_text as $key => $quote) {
		$quote_text = explode('<a class="authorOrTitle"', $quote->innertext);
		$quote_text = trim($quote_text[0]);
		$quote_text = trim($quote_text, '&ldquo;&#8213');
		$quote_text = clean_random_chars($quote_text);
		$quote_text = trim(str_replace('<br>', "\n", $quote_text));
		$quote_text = trim($quote_text, '&rdquo;');

//		echo '<pre>';
//		echo $quote_text;
//		echo '</pre>';

		$quote_array[$key]['text'] = $quote_text;

		foreach ($quote->find('.authorOrTitle') as $key_inner => $quote_author_or_title) {
			if ($key_inner == 0) {
				$quote_array[$key]['citation']['author'] = $quote_author_or_title->innertext;
			} else {
				$quote_array[$key]['citation']['source'] = $quote_author_or_title->innertext;
			}
		}
	}

	return $quote_array;
}

function get_next_page_url($url) {
	$html = new simple_html_dom();
	$html->load_file($url);
	$next_page = $html->find('.next_page');
	$next_page_link = null;

	foreach ($next_page as $next_page_link) {
		$next_page_link = $next_page_link->href;
	}

	return 'https://www.goodreads.com' . $next_page_link;
}

function clean_random_chars($string) {
	$search = array(
		chr(145),
		chr(146),
		chr(147),
		chr(148),
		chr(151),
		chr(150),
		chr(133),
		chr(149),
		'…',
		'. . .'
	);

	$replace = array(
		"'",
		"'",
		'"',
		'"',
		'--',
		'-',
		'...',
		"&bull;",
		'...',
		'...'
	);

	return str_replace($search, $replace, $string);
}

$url_to_scrape = explode('?url=', $_SERVER['REQUEST_URI'])[1];
$next_page_to_scrape = explode('?page=', $url_to_scrape)[1] + 1;
$next_page_url = get_next_page_url($url_to_scrape);

$scraped_data = scrape_goodreads($url_to_scrape);
//$scraped_data = scrape_goodreads('scrape-test.html');

foreach($scraped_data as $key => $bits) {
	$quote_text = $bits['text'];
	$quote_author = null;
	$quote_source = null;

	if (isset($bits['citation']['author'])) {
		$quote_author = $bits['citation']['author'];
	}

	if (isset($bits['citation']['source'])) {
		$quote_source = $bits['citation']['source'];
	}

	$quote_fields = array(
		'is_scrape'     => true,
		'quote_text'    => $quote_text,
		'quote_author'  => $quote_author,
		'quote_source'  => $quote_source
	);

	$options = array(
		'http' => array(
			'method'    => 'POST',
			'content'   => json_encode($quote_fields),
			'header'    =>  "Content-Type: application/json\r\nAccept: application/json\r\n"
		)
	);

	$context    = stream_context_create($options);
	$result     = file_get_contents('http://localhost/~ryanburney/quoteboard/wp-content/themes/quoteboard/includes/add-quote.php', false, $context);
	$response   = json_decode($result);

	echo '<pre>';
	print_r($response);
	echo '</pre>';
}

if ($response) {
	echo 'Quotes scraped ok. <a href="http://localhost/~ryanburney/quoteboard/wp-content/themes/quoteboard/includes/scrape-quotes-from-goodreads.php?url=' . get_next_page_url($url_to_scrape) . '">Continue with page ' . $next_page_to_scrape . '?</a>';
} else {
	echo 'There was a problem scraping quotes - check source HTML.';
}