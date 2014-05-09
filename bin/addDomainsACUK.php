<?php


	include '../uk.ac.data.Crawler.php';

	$crawler = new ukacdataCrawler();
	$weird = strlen('VU6AUEMLTFOJUOQFUME6NVJC8G6T8IME.ac.uk');

	include_once $crawler->config->pwd.'/lib/tldextract/tldextract.php';
	$seen = array();
	
	$tld = new TLDExtract();
	
	
	
	$handle = @fopen("../_misc/acuk", "r");
	if ($handle) {
	    while (($buffer = fgets($handle, 4096)) !== false) {
	        $domain = trim($buffer);
			if($domain=='ac.uk') continue;
			if(strlen($domain) == $weird) continue;
			if(in_array($domain,$seen)) continue;
			$seen[] = $domain; 
			$components = $tld->extract($domain);
			$domain = "{$components->domain}.{$components->tld}";
			if(!in_array($domain,$seen)) $seen[] = $domain; 
			$crawler->addDomain($domain, 'acuk');
			echo "{$domain}\n";
			
	    }
	    if (!feof($handle)) {
	        echo "Error: unexpected fgets() fail\n";
	    }
	    fclose($handle);
	}
	
	exit();
	
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
	
	
	
	
	