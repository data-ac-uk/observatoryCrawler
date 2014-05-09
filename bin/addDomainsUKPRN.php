<?php


	include '../uk.ac.data.Crawler.php';
	
	
	
	$crawler = new ukacdataCrawler();
	
	
	if (($handle = fopen("http://learning-provider.data.ac.uk/data/learning-providers.csv", "r")) !== FALSE) {
	    # Set the parent multidimensional array key to 0.
	    $nn = 0;
	    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
	      
		  	if($nn>0){
				echo "{$data[9]}\n";
				$domain = $crawler->addDomain($data[9], 'ukprn');
		  	}
		  
	        $nn++;
			sleep(1); //Slow down a bit to help who is server;
	    }
	    # Close the File.
	    fclose($handle);
	}
	
	
	
	
	