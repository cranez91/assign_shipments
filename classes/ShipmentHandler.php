<?php 
    class ShipmentHandler {
        private $destinations;
        private $drivers;
        private $assignment = [];
        private $totalSS = 0;
        const INCREASE_50 = 1.5;

        function __construct(array $destinations, array $drivers) {
            $this->destinations = $destinations;
            $this->drivers = $drivers;
        }

        private function isEven(int $number): bool {
            if($number % 2 == 0) return true;
            return false;
        }

        private function countVowels(string $driver)
        {
            return preg_match_all('/[aeiou]/i', $driver, $matches);
        }

        private function countConsonants(string $driver)
        {
            return preg_match_all('/[bcdfghjklmnpqrstvwxyz]/i', $driver, $matches);
        }

        private function findCommonFactors(int $num1, int $num2) {
            $factors = [];
            $min = min([$num1, $num2]);
            $max = max([$num1, $num2]);
            
            // loop through all possible factors of the smaller number
            for ($ctrl = 2; $ctrl <= $min ; $ctrl++) {
                if ($min % $ctrl == 0 && $max % $ctrl == 0) $factors [] = $ctrl; 
            }
            return $factors;
        }

        private function permutations(array $arr): array {
            $n = count($arr);
            if ($n <= 1) return [$arr];
        
            $permutations = [];
            for ($i = 0; $i < $n; $i++) {
                $removed = array_splice($arr, $i, 1);
                $subPermutations = $this->permutations($arr);
                foreach ($subPermutations as $sub_permutation) {
                    array_unshift($sub_permutation, $removed[0]);
                    $permutations[] = $sub_permutation;
                }
                array_splice($arr, $i, 0, $removed);
            }
            return $permutations;
        }

        private function calculateSuitabilityScore(string $address, string $driver): float
        {
            //Removing number from the address, for instance '123 Main St' => 'Main St'
            $stName = $this->getStreetNameLenght($address);
            //Removing spaces in driver name
            $driverName = strlen(str_replace(' ', '', $driver));
            
            $ss = $this->isEven($stName) ? $this->countVowels($driver) * self::INCREASE_50 : $this->countConsonants($driver);
            if ($this->findCommonFactors($stName, $driverName)) $ss *= self::INCREASE_50;
            return $ss;
        }

        private function getStreetNameLenght(string $destination)
        {
            $stName = explode(" ", $destination);
            array_shift($stName);
            return strlen(implode('', $stName));
        }

        private function setPermutations($topDestination, $topDriver)
        {
            // Generate all possible assignments
            $perms = $this->permutations(array_keys($this->destinations));

            return array_filter($perms, function ($perm) use ($topDestination, $topDriver) {
                return $perm[$topDestination] == $topDriver;
            });
        }

        private function doPermutations(array $permutationsSet)
        {
            foreach ($permutationsSet as $perm) {
                $ss = 0;
                $assignments = [];
                for ($i = 0; $i < count($perm); $i++) {
                    $destination = $this->destinations[$i];
                    $driver = $this->drivers[$perm[$i]];
                    $ss += $this->calculateSuitabilityScore($destination, $driver);
                    $assignments[$destination] = $driver;
                }
                if ($ss > $this->totalSS) {
                    $this->totalSS = $ss;
                    $this->assignment = $assignments;
                }
            }
        }

        private function printResults()
        {
            echo "Total SS: {$this->totalSS}" . PHP_EOL;
            foreach ($this->assignment as $shipment => $driver) {
                echo "$shipment -> $driver" . PHP_EOL;
            }
        }

        public function processShipments()
        {
            $evenDestinations = [];
            $topVowels = 0;
            $topVowelsIndex = 0;
            $topConsonants = 0;
            $topConsonantsIndex = 0;

            //Get all destinations with street name length as even
            //Get the drivers with most vowels and consonants in name
            for($i = 0 ; $i < sizeof($this->destinations); $i++) {
                if ($this->isEven( $this->getStreetNameLenght($this->destinations[$i]) )){
                   $evenDestinations[$i] = $this->destinations[$i];
                }

                $vowels = $this->countVowels($this->drivers[$i]);
                if ($vowels > $topVowels) {
                    $topVowels = $vowels;
                    $topVowelsIndex = $i;
                }

                $cons = $this->countConsonants($this->drivers[$i]);
                if ($cons > $topConsonants) {
                    $topConsonants = $cons;
                    $topConsonantsIndex = $i;
                }
            }

            if ($evenDestinations) {
                $this->withEvenDestinations($topVowelsIndex, $evenDestinations);
                return;
            }
            $this->noEvenDestinations($topConsonantsIndex);
        }

        private function withEvenDestinations(int $topVowelsIndex, array $evenDestinations)
        {
            $topDestination = array_key_first($evenDestinations);
            $topDriver = $topVowelsIndex;

            $permutationsSet = $this->setPermutations($topDestination, $topDriver);
            $this->doPermutations($permutationsSet);
            $this->printResults();
        }

        private function noEvenDestinations(int $topConsonantsIndex)
        {
            $topDriver = $topConsonantsIndex;
            $topDriverName = $this->drivers[$topDriver];
            $topDestination = 0;

            foreach ($this->destinations as $index => $destination) {
                //Removing number from the address, for instance '123 Main St' => 'Main St'
                $stName = $this->getStreetNameLenght($destination);
                //Removing spaces in driver name
                $driverName = strlen(str_replace(' ', '', $topDriverName));
                if ($this->findCommonFactors($stName, $driverName)) {
                    $topDestination = $index;
                    break;
                }
            }
            
            $permutationsSet = $this->setPermutations($topDestination, $topDriver);
            $this->doPermutations($permutationsSet);
            $this->printResults();
        }
    }
?>