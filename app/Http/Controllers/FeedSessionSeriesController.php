<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenActive\Rpde\RpdeBody;
use OpenActive\Rpde\RpdeItem;
use OpenActive\Rpde\RpdeKind;
use OpenActive\Rpde\RpdeState;
use OpenActive\BaseModel;
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

        $pageItems = $this->itemsForPage($changeNumber, 3);

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
        $strJsonFileContents = file_get_contents(__DIR__ ."/../../../session-series-feed-items.json");
        $rawItems = json_decode($strJsonFileContents, true);

        $items = array_map(function($item) {
            $args = [
                "Id" => $item["id"],
                "State" => $item["status"],
                "Kind" => $item["kind"],
                "Modified" => $item["modified"],
            ];
            if ($args["State"] == "updated") {
                $args["Data"] = SessionSeries::deserialize(json_encode($item["data"]));
            }
            return new RpdeItem($args);
        }, $rawItems);

        return $items;
    }
}
