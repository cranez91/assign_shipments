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

        private function getStreetName(string $address): string {
            return preg_replace('/^\d+\s+/', '', $address);
        }

        private function findCommonFactors(int $num1, int $num2): array {
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
            $stName = $this->getStreetName($address);
            $stName = strlen(str_replace(' ', '', $stName));
            
            //Removing spaces in driver name
            $driverName = strlen(str_replace(' ', '', $driver));
            
            $ss = $this->isEven($stName) ? $this->countVowels($driver) * self::INCREASE_50 : $this->countConsonants($driver);
            if ($this->findCommonFactors($stName, $driverName)) $ss *= self::INCREASE_50;
            return $ss;
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
            // Generate all possible assignments
            $perms = $this->permutations(array_keys($this->destinations));

            // Here is the process
            foreach ($perms as $perm) {
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

            $this->printResults();
        }
    }
?>