<?php
/*
Plugin Name: Bookstore Search
Plugin URI: 
Description: This plugin generates bookstore search box and results.
Author: Hivista
Version: 1.0
*/

define("SEARCH_BOX_DESCRIPTION", "Paste this code into your page or post if
you only want a search box. You will need
to also paste the code from the second box
on to a separate page where users will
see the results to their search.");

define("SEARCH_RESULTS_DESCRIPTION", "If you are creating a themed bookstore
you should only copy the code from this
box and paste it into your WP page or post.
This will automatically populate the page
with book titles from your search term.");

function bookstore_search_add_admin_options_page() {
	add_options_page(
		'Bookstore Search', // meta title
		'Bookstore Search', // admin menu title
		8,
		str_replace("\\", "/", __FILE__),
		'bookstore_search_settings_page'
	);
}
add_action('admin_menu', 'bookstore_search_add_admin_options_page');

function bookstore_search_settings_page() {
	$updated_flag = false;
	if ($_POST["bookstore_search_action"] == "submit") {
	    bookstore_search_submit_action();
		$updated_flag = true;
	}
	$bookstore_search_affilate_id = get_option('bookstore_search_affilate_id');
	$bookstore_search_default_search_term = get_option('bookstore_search_default_search_term');
	$bookstore_search_url_search_results = get_option('bookstore_search_url_search_results');
?>
	<div class="wrap">
	<h2><?php _e('Bookstore Search') ?></h2>
	<?php if ($updated_flag) { ?><div id="message" class="updated"><p>Settings updated.</p></div><?php } ?>
	<form method="post" action="">
	<input type="hidden" name="bookstore_search_action" value="submit">
	<table>
	  <tr>
	    <td><b>Affilate ID</b></td>
	    <td><input type="text" name="bookstore_search_affilate_id" value="<?php echo $bookstore_search_affilate_id; ?>" style="width:300px;"></td>
		<td></td>
	  </tr>
	  <tr>
	    <td><b>Default Search Term</b></td>
	    <td><input type="text" name="bookstore_search_default_search_term" value="<?php echo $bookstore_search_default_search_term; ?>" style="width:300px;"></td>
		<td>&nbsp;<i>this will populate the search page with books pertaining to this term</i></td>
	  </tr>
	  <tr>
	    <td><b>URL of Search Results&nbsp;</b></td>
	    <td><input type="text" name="bookstore_search_url_search_results" value="<?php echo $bookstore_search_url_search_results; ?>" style="width:300px;"></td>
		<td></td>
	  </tr>
	  <tr>
	    <td></td>
	    <td><p class="submit" style="padding: 0px 0px 5px 5px;"><input type="submit" value="<?php _e('Update') ?>" /></p></td>
		<td></td>
	  </tr>
	</table>
	</form>
	<table>
	  <tr>
	    <td width="250"><b>Code for Search Box</b></td>
	    <td width="250" align="center">[search]</td>
		<td><i><?php echo str_replace(chr(10), '<br>', SEARCH_BOX_DESCRIPTION); ?></i></td>
	  </tr>
	  <tr>
		<td colspan="3">&nbsp;</td>
	  </tr>
	  <tr>
	    <td><b>Code for Search Box and Results</b></td>
	    <td align="center">[search_results]</td>
		<td><i><?php echo str_replace(chr(10), '<br>', SEARCH_RESULTS_DESCRIPTION); ?></i></td>
	  </tr>
	</table>
<?php
}

function bookstore_search_submit_action() {
	$bookstore_search_affilate_id = $_POST['bookstore_search_affilate_id'];
	$bookstore_search_default_search_term = $_POST['bookstore_search_default_search_term'];
	$bookstore_search_url_search_results = $_POST['bookstore_search_url_search_results'];

	if (strlen($bookstore_search_url_search_results) && substr($bookstore_search_url_search_results, 0, 4) != 'http') {
		$bookstore_search_url_search_results = 'http://'.$bookstore_search_url_search_results;
	}

	update_option('bookstore_search_affilate_id', $bookstore_search_affilate_id);
	update_option('bookstore_search_default_search_term', $bookstore_search_default_search_term);
	update_option('bookstore_search_url_search_results', $bookstore_search_url_search_results);
}

function bookstore_search_shortcode_search() {
	$bookstore_search_url_search_results = urlencode(get_option('bookstore_search_url_search_results'));
	$textbookx_url = "http://textbookx.com/widgets/portmp/php/searchBox.php?searchPage=".$bookstore_search_url_search_results;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $textbookx_url);
	$textbookx_results = curl_exec($ch);
	curl_close($ch);
	echo $textbookx_results;
}

function bookstore_search_shortcode_search_results() {
	$bookstore_search_affilate_id = urlencode(get_option('bookstore_search_affilate_id'));
	$bookstore_search_default_search_term = urlencode(get_option('bookstore_search_default_search_term'));
	$bookstore_search_url_search_results = urlencode(get_option('bookstore_search_url_search_results'));

	$bookstore_search_use_default = "no";
	$filter = urlencode($_GET["tbx-category"]);
	$page = urlencode($_GET["tbx-page"]);

	if (isset($_POST["tbx-search-new"])) {
		$searchTerm = urlencode($_POST["tbx-search-new"]);
	} else if(isset($_GET["tbx-search-term"])) {
		$searchTerm = urlencode($_GET["tbx-search-term"]);
	} else {
		$searchTerm = $bookstore_search_default_search_term;
		$bookstore_search_use_default = "yes";
	}

	$textbookx_url = "http://textbookx.com/widgets/portmp/php/searchResults.php?searchTerm=".$searchTerm."&filter=".$filter."&page=".$page."&searchPage=".$bookstore_search_url_search_results."&default=".$bookstore_search_use_default."&affiliate=".$bookstore_search_affilate_id;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $textbookx_url);
	$textbookx_results = curl_exec($ch);
	curl_close($ch);
	echo $textbookx_results;
}

add_shortcode('search', 'bookstore_search_shortcode_search');
add_shortcode('search_results', 'bookstore_search_shortcode_search_results');
?>