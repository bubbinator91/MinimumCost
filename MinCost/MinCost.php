<?php

public abstract class MinCost {
	private $startingTemperature;
	private $coolingRate;

	public function __construct($startingTemperature = 50000.0, $coolingRate = 0.00002) {
		$this->startingTemperature = $startingTemperature;
		$this->coolingRate = $coolingRate;
	}

	// Use simulated annealing to hopefully get a list with a really good cost, if not the best cost overall
	public function findBestCost() {
		// Create initial list with a random order of items
		$currentList = getRandomList(getUnsortedList());
		$bestList = $currentList;

		// Calculate cost of the list. Putting this here will enable a certain amount of caching to reduce the
		// amount of overall computational steps
		$currentCost = getCost($currentList);
		$workingCost = $currentCost;
		$bestCost = $currentCost;

		$temperature = $this->startingTemperature;

		// If the temperature of the system is 1, then it's cool enough
		while ($temperature > 1) {
			// Mutate the last list, store it in a new variable, and calculate its cost
			$workingList = mutateList($currentList);
			$workingCost = getCost($workingList);

			// Calculate the acceptance probability, and accept the new solution if the probability checks
			// out. This is the main improvement of simulated annealing over a hill climbing algorithm
			// since it enables jumping out of local optimums by sometimes choosing a worse solution over
			// the current one.
			if (getAcceptanceProbability($currentCost, $workingCost, $temperature) > (mt_rand() / mt_getrandmax())) {
				$currentList = $workingList;
				$currentCost = $workingCost;
			}

			// If the cost of the current list is better than the best known list, then we store it
			if ($currentCost < $bestCost) {
				$bestList = $currentList;
				$bestCost = $currentCost;
			}

			// Cool the system
			$temperature *= (1 - $this->coolingRate);
		}

		return $bestList;
	}

	// This function returns a value in the range of [0.0, 1.0]. This value is used to determine if we're
	// going to accept the newer solution, even though it might be worse.
	private function getAcceptanceProbability($oldCost, $newCost, $temperature) {
		// If the new solution is better, accept it
		if ($newCost < $oldCost) {
			return 1.0;
		} else {
			// Otherwise, calculate the probablity of acceptance. If the new solution is much
			// worse, or if the temperature is still high, the probabilty will be higher.
			return exp(($oldCost - $newCost) / $temperature);
		}
	}

	// Gets a new list, which is the given list in a random order
	private function getRandomList($list) {
		shuffle($list);
		return $list;
	}

	// Uses the 2-opt swap method to mutate the list
	private function mutateList($list) {
		$count = count($list);

		$i = mt_rand(0, $count - 1);
		$k = mt_rand(0, $count - 1);

		if ($i > $k) {
			$t = $i;
			$i = $k;
			$k = $i;
		}

		while ($i < $k) {
			$tmp = $list[$k];
			$list[$k] = $list[$i];
			$list[$i] = $tmp;

			$i++; $k--;
			if ($i == $k) {
				break;
			}
		}

		return $list;
	}

	// This function will return the base list to sort by cost. As long as the list is an array,
	// we don't care what's inside of it.
	abstract public function getUnsortedList();

	// This function will get the cost of a list passed to it.
	abstract public function getCost($list);
}

?>
