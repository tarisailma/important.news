<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override('App\Controllers\Pages::Show404');
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.

//reditect to ads Check Bot
if (option('redirect_to_ads') && !is_bot($_SERVER['HTTP_USER_AGENT'])) {
	$routes->get('(:any)', function () {
		return redirect_to(option('reditect_ads_url'));
	});
}

// Pages
$routes->get('/', 'Pages::Index');
$routes->get(search_permalink('(:segment)', true), 'Pages::Search/$1');
$routes->get(download_permalink('(:segment)', true), 'Pages::Download/$1');
$routes->get(page_permalink('(:segment)', true), 'Pages::Page/$1');
$routes->get(playlist_permalink('(:segment)', true), 'Pages::Playlist/$1');
$routes->get(genre_permalink('(:segment)', true), 'Pages::Genre/$1');
$routes->get(image_permalink('(:segment)', true), 'Pages::Image/$1');
$routes->get(option('ping_sitemap_permalink'), 'Pages::Ping');

// Sitemap
$routes->get(option('sitemap_index_permalink'), 'Sitemaps::Index');
$routes->get(sitemap_playlist_permalink('(:segment)', true), 'Sitemaps::Playlist/$1');
$routes->get(sitemap_genre_permalink('(:segment)', true), 'Sitemaps::Genre/$1');

$routes->get(key_permalink('(:segment)', true), 'Indexing::IndexNowKey/$1');
$routes->get(option('indexing_permalink'), 'Indexing::Index');
$routes->get(option('flush_cache_url'), 'Pages::FlushCache');

// Apis
$routes->get('/api/search', 'Apis::Search');
$routes->get('/api/video', 'Apis::Video');
$routes->get('/api/playlist', 'Apis::Playlist');
$routes->get('/api/related', 'Apis::Related');
$routes->get('/api/topsong', 'Apis::Topsong');
$routes->get('/api/comment', 'Apis::Comment');


$routes->get('(:any)', function () {
	if (option('redirect_404') == 'home') {
		return redirect_to('/');
	}

	if (option('redirect_404') == 'random') {
		$term = get_terms(1);
		$term = count($term['items']) > 0 ? $term['items'][0] : null;
		$url = search_permalink($term);
		return redirect_to($url);
	}
});


/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
