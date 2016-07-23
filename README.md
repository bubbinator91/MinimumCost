# MinimumCost
This is an implementation of the heuristic simulated annealing algorithm in PHP. It is written in such a way that it technically can sort any array, as long as you can define some sort "cost" between any two items in the array.

## Purpose
This small project originally began as a direct implementation of simulated annealing to solve the Traveling Salesman problem in PHP for [Meal Prep SLO](https://mealprepslo.com) (launches soon). As the project progressed, I wanted to put a more general version of the simulated annealing implementation online, open source, so other people could use it. I abstracted the core of the TSP solver out into what is now the MinCost abstract class, and implemented the TSP solver as an example. The reason the TSP script generates data as it does is to mimic how data is represented my Meal Prep SLO's internal code.

## Usage
If you want to run the [examples](/Examples), clone the whole repo and check them out for yourself.

If you just want to extend the MinCost class for yourself, the general idea is as follows:

1. Place the MinCost.php file wherever it should belong in your project.
2. In the file where you are going to implement your extension class, make sure to include the MinCost.php file so your code can see it.
3. Make sure to implement the two abstract functions. Their purpose is as follows:
  * getListToSort(): This should just return the base list that needs to be sorted so that the data in your list is abstracted away.
  * getCost(): This should get the total cost of a list. How you determine the cost of a list is completely up to you, since the input list is your data.

#### Usage Example
```PHP
<?php

include_once "MinCost.php";

class Example extends MinCost {
	private $list = NULL;

	...

	public function __construct($list, ..., $startingTemp, $coolingRate) {
		parent::__construct($startingTemp, $coolingRate);

		// Any other initialization code
		...
	}

	public function getListToSort() {
		return $this->list;
	}

	public function getCost($list) {
		// Whatever code you need to determine to overall "cost" of $list should go here
	}

	...
}

?>
```

## Examples
To see some example implementations, head over to [examples](/Examples).

# License
There is no license attached to this. The only thing I will say is that I will take no responsibility if you use this code and it effects you negatively in some way. Use it at your own risk.
