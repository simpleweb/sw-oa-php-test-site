<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenActive\Rpde\RpdeBody;
use OpenActive\Rpde\RpdeItem;
use OpenActive\Rpde\RpdeKind;
use OpenActive\Rpde\RpdeState;
use OpenActive\Models\OA\SessionSeries;
use OpenActive\Models\OA\Place;
use OpenActive\Models\OA\GeoCoordinates;
use OpenActive\Models\OA\Concept;
use OpenActive\Models\OA\Organization;
use OpenActive\Models\OA\Offer;

class FeedSessionSeriesController extends Controller
{
    public function show()
    {
        $baseUrl = request()->url();
        $changeNumber = (request()->query('afterChangeNumber') ?: 0);

        $pageItems = $this->itemsForPage($changeNumber, 1);

        $page = RpdeBody::createFromNextChangeNumber($baseUrl, $changeNumber, $pageItems);

        return response(RpdeBody::serialize($page))
            ->header('Content-Type', 'application/json');
    }

    private function itemsForPage($changeNumber, $limit) {
        $pageItems = $this->allItems();
        // filter out items which are too old
        $pageItems = array_filter($pageItems, function($item) use ($changeNumber) { return $item->getModified() > $changeNumber; });
        // sort items by modified ASC, id ASC
        usort($pageItems, function($item1, $item2) {
            if($item1->getModified() == $item2->getModified() && $item1->getId() == $item2.getId()) {
                return 0;
            } elseif($item1->getModified() == $item2->getModified()) {
                return $item1->getId() < $item2.getId() ? -1 : 1;
            } else {
                return $item1->getModified() < $item2->getModified() ? -1 : 1;
            }
        });
        // limit number of items per page
        $pageItems = array_slice($pageItems, 0, $limit);

        return $pageItems;
    }

    private function allItems() {
        $sessionSeries = new SessionSeries([
            "name" => "Virtual BODYPUMP",
            "description" => "This is the virtual version of the original barbell class, which will help you get lean, toned and fit - fast",
            "startDate" => "2017-04-24T19:30:00-08:00",
            "endDate" => "2017-04-24T23:00:00-08:00",
            "location" => new Place([
                "name" => "Raynes Park High School, 46A West Barnes Lane",
                "geo" => new GeoCoordinates([
                    "latitude" => 51.4034423828125,
                    "longitude" => -0.2369088977575302,
                ])
            ]),
            "activity" => new Concept([
                "id" => "https://openactive.io/activity-list#5e78bcbe-36db-425a-9064-bf96d09cc351",
                "prefLabel" => "Bodypump™",
                "inScheme" => "https://openactive.io/activity-list"
            ]),
            "organizer" => new Organization([
                "name" => "Central Speedball Association",
                "url" => "http://www.speedball-world.com"
            ]),
            "offers" => [new Offer([
                "identifier" => "OX-AD",
                "name" => "Adult",
                "price" => 3.3,
                "priceCurrency" => "GBP",
                "url" => "https://profile.everyoneactive.com/booking?Site=0140&Activities=1402CBP20150217&Culture=en-GB"
            ])],
        ]);

        $items = [
            new RpdeItem([
                "Id" => "2",
                "Modified" => 4,
                "State" => RpdeState::UPDATED,
                "Kind" => RpdeKind::SESSION_SERIES,
                "Data" => $sessionSeries,
            ]),
            new RpdeItem([
                "Id" => "1",
                "Modified" => 5,
                "State" => RpdeState::DELETED,
                "Kind" => RpdeKind::SESSION_SERIES,
            ])
        ];

        return $items;
    }
}
