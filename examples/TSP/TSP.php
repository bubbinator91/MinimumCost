<?php

include_once "../../MinCost.php";

/***********************CLASS IMPLEMENTATION***********************/

class TSP extends MinCost {
	// Our list. In this case, it's a list of locations.
	private $locations = NULL;
	// An array that will hold the distances for all of the possible paths between locations, minus duplicates
	private $paths = NULL;

	public function __construct($locations = NULL, $paths = NULL, $startingTemperature = 50000.0, $coolingRate = 0.00002) {
		parent::__construct($startingTemperature, $coolingRate);

		// Validate the locations array to make sure it has the data we need to process it
		if (is_null($locations) || !is_array($locations)) {
			throw new InvalidArgumentException("\$locations must not be null, and must be an array of locations");
		} else {
			foreach ($locations as $location) {
				if (!is_array($location) || !isset($location["id"])) {
					throw new InvalidArgumentException("The locations in \$locations must each be an array,"
										. " with each said array containing at least an"
										. " integer stored at the \"id\" key");
				}
			}
			$this->locations = $locations;
		}

		// Validate the paths array, and process the data in order to store each path in a table with the key
		// of each path being from_id->to_id, or to_id->from_id if to_id < $from_id
		if (is_null($paths) || !is_array($paths)) {
			throw new InvalidArgumentException("\$paths must not be null, and must be an array of paths");
		} else {
			foreach ($paths as $path) {
				if (!is_array($path) || !isset($path["from_id"]) || !isset($path["to_id"]) || !isset($path["dist"])) {
					throw new InvalidArgumentException("The paths in \$paths must each be an array, with each"
										. " said array containing integers stored at the"
										. " \"from_id\" and \"to_id\" keys, as well as"
										. " either an integer or float stored at the"
										. " \"dist\" key");
				}

				$this->paths[$this->getKeyForPathTable($path["from_id"], $path["to_id"])] = $path["dist"];
			}
		}
	}

	// Overridden abstract function. Just returns the base list of locations
	protected function getListToSort() {
		return $this->locations;
	}

	// Iterates through the given list and returns the cost of it. In this case, the cost is the total distance of the route.
	protected function getCost($list) {
		if (is_null($list) || !is_array($list)) {
			return -1;
		}

		$cost = 0;
		for ($i = 0, $c = count($list); $i < $c; $i++) {
			$from = $list[$i];
			$to = $list[0];

			// If we're not already at the end of the route, then set $i to the next location rather than the beginning
			if (($i + 1) < $c) {
				$to = $list[$i + 1];
			}

			$cost += $this->getDistanceBetweenLocations($from["id"], $to["id"]);
		}

		return $cost;
	}

	// Queries the $path array for the distance between any two locations
	private function getDistanceBetweenLocations($idFrom, $idTo) {
		$key = $this->getKeyForPathTable($idFrom, $idTo);

		if (isset($this->paths[$key])) {
			return $this->paths[$key];
		}

		return -1;
	}

	// Since our $path array is basically a hash table, this gets the key for a specific path
	private function getKeyForPathTable($idFrom, $idTo) {
		$minId = $idFrom;
		$maxId = $idTo;

		// To make sure that we don't have duplicates (A->B == B->A afterall), make sure the lesser of the two IDs is
		// assigned to $minId, and the greater is assigned to $maxId
		if ($minId > $maxId) {
			$minId = $idTo;
			$maxId = $idFrom;
		}

		return $minId . "->" . $maxId;
	}
}

/***********************END CLASS*************************/

/***********************TEST SCRIPT***********************/

// Checks to see if a path already exists in the given $paths array
function doesPathExist($idFrom, $idTo, $paths) {
	foreach ($paths as $path) {
		if (($path["from_id"] == $idFrom) || ($path["from_id"] == $idTo)) {
			if (($path["to_id"] == $idFrom) || ($path["to_id"] == $idTo)) {
				return true;
			}
		}
	}

	return false;
}

$locations = array();
$paths = array();
$numLocations = 20;

// Generate $numLocations random locations using a X, Y coordinate system
for ($i = 0; $i < $numLocations; $i++) {
	$x = mt_rand(0, 500);
	$y = mt_rand(0, 500);
	$location = array("id" => $i, "x" => $x, "y" => $y);
	$locations[] = $location;
}

// Pregenerate all of the distances between locations and store them
foreach ($locations as $location) {
	foreach ($locations as $location2) {
		if (($location["id"] != $location2["id"]) && !doesPathExist($location["id"], $location2["id"], $paths)) {
			$xDist = abs($location["x"] - $location2["x"]);
			$yDist = abs($location["y"] - $location2["y"]);
			$distance = sqrt(($xDist * $xDist) + ($yDist * $yDist));
			$path = array("from_id" => $location["id"], "to_id" => $location2["id"], "dist" => $distance);
			$paths[] = $path;
		}
	}
}

// Utilize TSP to find the best route
echo "Starting finding best route at " . date("Y:m:d:h:i:s", time()) . "\n";
$tsp = new TSP($locations, $paths);
$goodRoute = $tsp->findBestCost();
echo "Found best route at " . date("Y:m:d:h:i:s", time()) . "\n";

// Output the locations and paths as JSON to a file
echo "Outputting location data to a json file...\n";
$jsonArray = array("locations" => $locations, "paths" => $paths);
$jsonFile = fopen("location_data.json", "w+");
fwrite($jsonFile, json_encode($jsonArray));
fclose($jsonFile);

// Output the best route to a file
echo "Outputting the IDs in the order of the best route to a file...\n";
$routeFile = fopen("route.txt", "w+");
foreach ($goodRoute as $location) {
	fwrite($routeFile, $location["id"] . PHP_EOL);
}
fclose($routeFile);

/***********************END SCRIPT************************/

?>
