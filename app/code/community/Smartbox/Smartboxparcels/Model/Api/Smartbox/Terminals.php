<?php


class Smartbox_Smartboxparcels_Model_Api_Smartbox_Terminals extends Smartbox_Smartboxparcels_Model_Api_Smartbox_Abstract
{
    //Save an array of requested terminals
    private $terminals = false;

    //The location in the cache of our terminals JSON
    const SMARTBOX_TERMINALS_CACHE_KEY = 'smartbox_smartboxparcels_terminals';

    //Retrieve all the terminals from the API
    public function getTerminals()
    {
		
        // Only even attempt to load the terminals once
        if(!$this->terminals) {

            // Attempt to load the terminals from the cache
            if($terminals = $this->getCache()->load(self::SMARTBOX_TERMINALS_CACHE_KEY)) {

                // If they load from the cache then use those values
                $this->terminals = Mage::helper('core')->jsonDecode($terminals);

            } else {

                //make a request to the API
                $http = parent::buildRequest('terminalSearch');
                $responseTerminals = parent::makeRequest($http);
                $terminals = array();
                foreach($responseTerminals as $terminal){
					
						$terminals[] = $terminal;
						
                }
                $this->terminals = $terminals;
                //update cache if the request returns terminals
                if(!empty($this->terminals)) {
                    // Save the terminals within our cache
                    $this->getCache()->save(Mage::helper('core')->jsonEncode($this->terminals), self::SMARTBOX_TERMINALS_CACHE_KEY, array(self::SMARTBOX_TERMINALS_CACHE_KEY), 60 * 60 * 24);
                }

            }
        }

        return $this->terminals;
    }
    //create parcel
    public function createParcel($parcel_data){
        if(!$parcel_data){
            return ;
        }
         $http = parent::buildRequest('parcels',Varien_Http_Client::POST,$parcel_data);
         return parent::makeRequest($http);
    }

    //fetch parcel details
    public function getParcelDetails($tracking_id){
        if(!$tracking_id){
            return ;
        }
        $http = parent::buildRequest('parcelSearch?invoiceNumber='.$tracking_id);
        
        return parent::makeRequest($http);

    }

    

    //Get the closest terminals depending on long & lat
    public function getClosestTerminals($lat, $long, $size = 5)
    {
        // Push our location into an array
        $userLocation = array($lat, $long);

        // Grab the terminals
        $terminals = $this->getTerminals();
    
        // If we get no terminals returned
        if(empty($terminals)) {
            return false;
        }

        // Run through an array_map function
        $distances = array_map(function($terminal) use($userLocation) {
            $a = array($terminal['geocode']['lat'], $terminal['geocode']['lng']);
            return $this->distance($a, $userLocation);
        }, $terminals);

        // Sort correctly
        asort($distances);

        // Get closest terminals
        $closestTerminals = array();
        foreach($distances as $key => $distance) {
			
			if($distance <= 60){
				// Add distance into our array
            $terminals[$key]['distance'] = number_format($distance, 1);

            // Add terminal into our closestTerminals array
            $closestTerminals[] = $terminals[$key];

            // Watch size of the response
            if(count($closestTerminals) == $size) {
                break;
            }
				}
        }
        return $this->createTerminalCollection($closestTerminals);
    }

    //Return a singular terminal
    public function getTerminal($terminalId, $returnData = false)
    {
        // Retrieve terminal from the API
        $http = parent::buildRequest('terminalSearch?number=' . $terminalId);
        $terminaldata = parent::makeRequest($http);

        // If the terminal loads and isn't false
        if($terminaldata[0]){

            // Do we just want the data?
            if($returnData) {
                return $terminaldata[0];
            }
            // Add the data into a model and return
            return Mage::getModel('smartbox_smartboxparcels/terminal')->addData($terminaldata[0]);
        }

        return false;
    }

    //Build the terminal objects into Varien_Objects
    public function createTerminalCollection($terminals)
    {
        // Build a new basic collection
        $collection = new Varien_Data_Collection();
        // Loop through each terminal
        foreach($terminals as $terminal) {
                 // Create a new instance of the terminal model and append the data
                $terminalItem = Mage::getModel('smartbox_smartboxparcels/terminal')->addData($terminal);
                // Add the item into our collection
                $collection->addItem($terminalItem);
        }
        
        return $collection;
    }

    //Calculate the distance in kilometers between point A and B
    protected function distance($a, $b)
    {
        list($lat1, $lon1) = $a;
        list($lat2, $lon2) = $b;

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $km = $dist * 60 * 1.1515 * 1.6 ;
        return $km;
    }

    //Return an instance of the caching system
    protected function getCache()
    {
        return Mage::app()->getCache();
    }
}