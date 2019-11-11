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
        $perPage = 3;

        // get "raw" data (i.e. not OA classes) from "database"
        $pageItemsData = $this->dataForPage($changeNumber, $perPage);

        // convert data on raw items into OA models
        $pageItemsData = array_map(function($rawItem) {
            if(array_key_exists("data", $rawItem)) {
                // in this case item's data is already is format ready to deserialized into a SessionSeries object
                $rawItem["data"] = SessionSeries::deserialize(json_encode($rawItem["data"]));
            }
            return $rawItem;
        }, $pageItemsData);

        // convert raw items into RPDE items
        $pageItems = array_map(function($rawItem) {
            $args = [
                "Id" => $rawItem["id"],
                "State" => $rawItem["state"],
                "Kind" => $rawItem["kind"],
                "Modified" => $rawItem["modified"],
            ];
            if ($args["State"] === "updated") {
                $args["Data"] = $rawItem["data"];
            }
            // and similarly
            return new RpdeItem($args);
        }, $pageItemsData);

        // create an RPDE page
        $page = RpdeBody::createFromNextChangeNumber($baseUrl, $changeNumber, $pageItems);

        // return RPDE page serialized as JSON
        return response(RpdeBody::serialize($page))
            ->header('Content-Type', 'application/json');
    }

    private function dataForPage($changeNumber, $limit) {
        // get all items from the "database" - aka a JSON file
        $pageItems = $this->allData();
        // filter out items which are too old
        $pageItems = array_filter($pageItems, function($item) use ($changeNumber) { return $item["modified"] > $changeNumber; });
        // sort items by modified ASC, id ASC
        usort($pageItems, function($item1, $item2) {
            if($item1["modified"] == $item2["modified"] && $item1["id"] == $item2["id"]) {
                return 0;
            } elseif($item1["modified"] == $item2["modified"]) {
                return $item1-["id"] < $item2["id"] ? -1 : 1;
            } else {
                return $item1["modified"] < $item2["modified"] ? -1 : 1;
            }
        });
        // limit number of items per page
        $pageItems = array_slice($pageItems, 0, $limit);

        return $pageItems;
    }

    private function allData() {
        $strJsonFileContents = file_get_contents(__DIR__ ."/../../../session-series-feed-items.json");
        return json_decode($strJsonFileContents, true);
    }
}
