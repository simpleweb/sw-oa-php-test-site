<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use OpenActive\DatasetSiteTemplate\FeedType;
use OpenActive\DatasetSiteTemplate\TemplateRenderer;

Route::get('/', function () {
    $supportedFeedTypes = array(
        FeedType::FACILITY_USE,
        FeedType::SCHEDULED_SESSION,
        FeedType::SESSION_SERIES,
        FeedType::SLOT,
    );

    $settings = array(
        "openDataFeedBaseUrl" => "https://sw-oa-test-site.herokuapp.com/feed/",
        "datasetSiteUrl" => "https://sw-oa-test-site.herokuapp.com",
        "datasetDiscussionUrl" => "https://github.com/simpleweb/sw-oa-php-test-site",
        "datasetDocumentationUrl" => "https://developer.openactive.io/",
        "datasetLanguages" => array("en-GB"),
        "organisationName" => "Simpleweb",
        "organisationUrl" => "https://www.simpleweb.co.uk/",
        "organisationLegalEntity" => "Simpleweb Ltd",
        "organisationPlainTextDescription" => "Simpleweb is a purpose driven software company that specialises in new technologies, product development, and human interaction.",
        "organisationLogoUrl" => "https://simpleweb.co.uk/wp-content/uploads/2015/07/facebook-default.png",
        "organisationEmail" => "spam@simpleweb.co.uk",
        "backgroundImageUrl" => "https://simpleweb.co.uk/wp-content/uploads/2017/06/IMG_8994-500x500-c-default.jpg",
        "dateFirstPublished" => "2019-11-05", // remember, remember the fifth of November...
    );

    echo((new TemplateRenderer())->renderSimpleDatasetSite($settings, $supportedFeedTypes));

    // return view('welcome');
});
