<?php

/**
 * Media Organizer
 *
 * @package     MediaOrganizer
 * @author      drdelaney
 * @copyright   2024-2026 drdelaney
 * @license     GNU General Public License v3.0
 * @link        https://github.com/drdelaney/MediaOrganizer
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Setup routes (Publicly accessible for initialization)
$routes->get('setup', 'Setup::index', ['as' => 'setup_public']);
$routes->post('setup/run', 'Setup::run');
$routes->get('setup/complete', 'Setup::complete');
$routes->post('setup/markMigrationComplete', 'Setup::markMigrationComplete');
$routes->get('setup/(:any)', 'Setup::$1');
$routes->post('setup/(:any)', 'Setup::$1');

// Public routes
$routes->get('login', 'Auth::login');
$routes->post('authenticate', 'Auth::authenticate');
$routes->get('logout', 'Auth::logout');
$routes->match(['GET', 'POST'], 'public', 'PublicView::index');

// Re-authentication for maintenance (require basic authentication)
$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('reauth', 'Auth::reauth');
    $routes->post('reauth', 'Auth::processReauth');
});

// Protected routes (require authentication)
$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'Home::index');

    // Media routes
    $routes->match(['GET', 'POST'], 'media', 'Media::index');
    $routes->get('media/view/(:num)', 'Media::view/$1');
    $routes->get('media/edit/(:num)', 'Media::edit/$1');
    $routes->post('media/update/(:num)', 'Media::update/$1');
    $routes->get('media/delete/(:num)', 'Media::delete/$1');
    $routes->post('media/toggleSeen/(:num)', 'Media::toggleSeen/$1');
    $routes->post('media/fetchFromApi/(:num)', 'Media::fetchFromApi/$1');
    $routes->get('media/poster/(:num)', 'Media::poster/$1');
    $routes->post('media/fetchPosters/(:num)', 'Media::fetchPosters/$1');
    $routes->post('media/updatePoster/(:num)', 'Media::updatePoster/$1');
    $routes->get('media/loanHistory/(:num)', 'Media::loanHistory/$1');

    // Add/create routes
    $routes->get('media/add', 'Media::add');
    $routes->post('media/store', 'Media::store');
    $routes->post('media/lookup', 'Media::lookup');
    $routes->post('media/fetchDetails', 'Media::fetchDetails');
    $routes->post('media/fetchPostersForNew', 'Media::fetchPostersForNew');
    $routes->post('media/checkDuplicate', 'Media::checkDuplicate');
    $routes->post('media/exempt/(:num)', 'Media::exempt/$1');

    // Loan management routes
    $routes->post('media/loan/(:num)', 'Media::loan/$1');
    $routes->post('media/returnLoan/(:num)', 'Media::returnLoan/$1');
    $routes->post('media/sendReminder/(:num)', 'Media::sendReminder/$1');
    $routes->get('media/getPeople', 'Media::getPeople');
    $routes->post('media/addPerson', 'Media::addPerson');
    $routes->post('media/addCollection', 'Media::addCollection');
    $routes->post('media/addVolume', 'Media::addVolume');

    // Tag management for media (AJAX)
    $routes->post('media/updateMediaTags/(:num)', 'Media::updateMediaTags/$1');

    // Password management
    $routes->get('settings/password', 'Auth::changePassword');
    $routes->post('settings/password', 'Auth::updatePassword');

    // About page
    $routes->get('settings/about', 'Home::about');

    // People (loaned users) management (AJAX endpoints)
    // $routes->get('people', 'People::index'); // Standalone page no longer used
    $routes->post('people/add', 'People::add');
    $routes->post('people/update/(:num)', 'People::update/$1');
    $routes->post('people/delete/(:num)', 'People::delete/$1');

    // Loans (currently loaned media) management
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
    $routes->post('applyMigrations', 'DatabaseMaintenance::applyMigrations');
    $routes->post('checkEnvironmentAjax', 'DatabaseMaintenance::checkEnvironmentAjax');
    $routes->get('backupDatabase', 'DatabaseMaintenance::backupDatabase');
    $routes->get('downloadRawSqlite', 'DatabaseMaintenance::downloadRawSqlite');

    // Lookup tables management
    $routes->get('manage-lookups', 'DatabaseMaintenance::manageLookups');
    $routes->get('duplicate-detector', 'DatabaseMaintenance::duplicateDetector');

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
    $routes->get('tag/media/(:num)', 'DatabaseMaintenance::viewTagMedia/$1');
    $routes->post('tag/add', 'DatabaseMaintenance::addTag');
    $routes->post('tag/update/(:num)', 'DatabaseMaintenance::updateTag/$1');
    $routes->post('tag/delete/(:num)', 'DatabaseMaintenance::deleteTag/$1');

    // AChannel routes
    $routes->post('achannel/add', 'DatabaseMaintenance::addAChannel');
    $routes->post('achannel/update/(:num)', 'DatabaseMaintenance::updateAChannel/$1');
    $routes->post('achannel/delete/(:num)', 'DatabaseMaintenance::deleteAChannel/$1');

    // ACodec routes
    $routes->post('acodec/add', 'DatabaseMaintenance::addACodec');
    $routes->post('acodec/update/(:num)', 'DatabaseMaintenance::updateACodec/$1');
    $routes->post('acodec/delete/(:num)', 'DatabaseMaintenance::deleteACodec/$1');

    // Language routes
    $routes->post('language/add', 'DatabaseMaintenance::addLanguage');
    $routes->post('language/update/(:num)', 'DatabaseMaintenance::updateLanguage/$1');
    $routes->post('language/delete/(:num)', 'DatabaseMaintenance::deleteLanguage/$1');

    // Ratio routes
    $routes->post('ratio/add', 'DatabaseMaintenance::addRatio');
    $routes->post('ratio/update/(:num)', 'DatabaseMaintenance::updateRatio/$1');
    $routes->post('ratio/delete/(:num)', 'DatabaseMaintenance::deleteRatio/$1');

    // Subformat routes
    $routes->post('subformat/add', 'DatabaseMaintenance::addSubformat');
    $routes->post('subformat/update/(:num)', 'DatabaseMaintenance::updateSubformat/$1');
    $routes->post('subformat/delete/(:num)', 'DatabaseMaintenance::deleteSubformat/$1');

    // Poster routes
    $routes->post('poster/purge', 'DatabaseMaintenance::purgePosters');

    // Cron routes
    $routes->post('cron/toggle/(:num)', 'DatabaseMaintenance::toggleCronJob/$1');
    $routes->post('cron/run/(:num)', 'DatabaseMaintenance::runCronJob/$1');
    $routes->post('cron/update-schedule/(:num)', 'DatabaseMaintenance::updateCronSchedule/$1');

    // Config routes
    $routes->post('config/update', 'DatabaseMaintenance::updateConfig');
});