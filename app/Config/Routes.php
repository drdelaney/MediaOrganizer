<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Public routes
$routes->get('login', 'Auth::login');
$routes->post('authenticate', 'Auth::authenticate');
$routes->get('logout', 'Auth::logout');

// Re-authentication for maintenance
$routes->get('reauth', 'Auth::reauth');
$routes->post('reauth', 'Auth::processReauth');

// Protected routes (require authentication)
$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Home::index');

    // Movie routes
    $routes->get('movies', 'Movies::index');
    $routes->get('movies/view/(:num)', 'Movies::view/$1');
    $routes->get('movies/edit/(:num)', 'Movies::edit/$1');
    $routes->post('movies/update/(:num)', 'Movies::update/$1');
    $routes->get('movies/delete/(:num)', 'Movies::delete/$1');
    $routes->post('movies/toggleSeen/(:num)', 'Movies::toggleSeen/$1');
    $routes->post('movies/fetchFromApi/(:num)', 'Movies::fetchFromApi/$1');
    $routes->get('movies/poster/(:num)', 'Movies::poster/$1');
    $routes->post('movies/fetchPosters/(:num)', 'Movies::fetchPosters/$1');
    $routes->post('movies/updatePoster/(:num)', 'Movies::updatePoster/$1');

    // Add/create routes
    $routes->get('movies/add', 'Movies::add');
    $routes->post('movies/store', 'Movies::store');
    $routes->post('movies/lookup', 'Movies::lookup');
    $routes->post('movies/fetchDetails', 'Movies::fetchDetails');
    $routes->post('movies/fetchPostersForNew', 'Movies::fetchPostersForNew');

    // Loan management routes
    $routes->post('movies/loan/(:num)', 'Movies::loan/$1');
    $routes->post('movies/returnLoan/(:num)', 'Movies::returnLoan/$1');
    $routes->get('movies/getPeople', 'Movies::getPeople');

    // Tag management for movies (AJAX)
    $routes->post('movies/updateTags/(:num)', 'Movies::updateMovieTags/$1');

    // Password management
    $routes->get('settings/password', 'Auth::changePassword');
    $routes->post('settings/password', 'Auth::updatePassword');

    // People (loaned users) management
    $routes->get('people', 'People::index');
    $routes->post('people/add', 'People::add');
    $routes->post('people/update/(:num)', 'People::update/$1');
    $routes->post('people/delete/(:num)', 'People::delete/$1');

    // Loans (currently loaned movies) management
    $routes->get('loans', 'Loans::index');
    $routes->post('loans/returnLoan/(:num)', 'Loans::returnLoan/$1');
    $routes->post('loans/sendReminder', 'Loans::sendReminder');
});

// Database maintenance routes (require recent authentication)
$routes->group('database-maintenance', ['filter' => 'maintenanceauth'], function($routes) {
    $routes->get('/', 'DatabaseMaintenance::index');
    $routes->post('reindexTables', 'DatabaseMaintenance::reindexTables');
    $routes->post('optimizeTables', 'DatabaseMaintenance::optimizeTables');
    $routes->post('convertToInnoDB', 'DatabaseMaintenance::convertToInnoDB');
    $routes->post('checkEnvironmentAjax', 'DatabaseMaintenance::checkEnvironmentAjax');
    $routes->get('backupDatabase', 'DatabaseMaintenance::backupDatabase');

    // Lookup tables management
    $routes->get('manage-lookups', 'DatabaseMaintenance::manageLookups');

    // Medium routes
    $routes->post('medium/add', 'DatabaseMaintenance::addMedium');
    $routes->post('medium/update/(:num)', 'DatabaseMaintenance::updateMedium/$1');
    $routes->post('medium/delete/(:num)', 'DatabaseMaintenance::deleteMedium/$1');

    // Collection routes
    $routes->post('collection/add', 'DatabaseMaintenance::addCollection');
    $routes->post('collection/update/(:num)', 'DatabaseMaintenance::updateCollection/$1');
    $routes->post('collection/delete/(:num)', 'DatabaseMaintenance::deleteCollection/$1');

    // Volume routes
    $routes->post('volume/add', 'DatabaseMaintenance::addVolume');
    $routes->post('volume/update/(:num)', 'DatabaseMaintenance::updateVolume/$1');
    $routes->post('volume/delete/(:num)', 'DatabaseMaintenance::deleteVolume/$1');

    // Codec routes
    $routes->post('codec/add', 'DatabaseMaintenance::addCodec');
    $routes->post('codec/update/(:num)', 'DatabaseMaintenance::updateCodec/$1');
    $routes->post('codec/delete/(:num)', 'DatabaseMaintenance::deleteCodec/$1');

    // Tag routes
    $routes->get('tag/movies/(:num)', 'DatabaseMaintenance::viewTagMovies/$1');
    $routes->post('tag/add', 'DatabaseMaintenance::addTag');
    $routes->post('tag/update/(:num)', 'DatabaseMaintenance::updateTag/$1');
    $routes->post('tag/delete/(:num)', 'DatabaseMaintenance::deleteTag/$1');
});