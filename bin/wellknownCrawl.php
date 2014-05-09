<?php


	include '../uk.ac.data.Crawler.php';
	
	
	$test404 = "/I_am_trying_to_test_your_404_response_".date("Y-m-d");
	
	$wellknowndef = "http://www.iana.org/assignments/well-known-uris/well-known-uris-1.csv";
	$wellknowns = array("void", "openorg");
	
	$row = 1;
	if (($handle = fopen($wellknowndef, "r")) !== FALSE) {
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			if(!in_array($data[0], $wellknowns)){
	           	$wellknowns[] = $data[0];		
	        }
	    }
	    fclose($handle);
	}
	
	
	$crawler = new ukacdataCrawler();

	$crawler->curl_launch();
	
	$mysqldate = date( $crawler->config->date->mysql );
	$pageno = 0;
	$pagesize = 10;

	$loop = true;
	while($loop){
		
		$websites = $crawler->db->fetch_many('websites', array('site_status' => "OK"), array(), "*", "$pageno,$pagesize");
		
		if(!count($websites)){
			break;
		}
		

		foreach($websites as $site){

			echo $site['site_domain']."\n";

		
			$_404test = $crawler->curl_get( "http://{$site['site_domain']}{$test404}" );
			$ins4 = array();
			$ins4['test_key'] = "{$site['site_domain']}-404test";
			$ins4['test_domain'] = "{$site['site_domain']}";
			$ins4['test_type'] = "404test";
			if($_404test['crawl_httpcode']!='404'){
				$ins4['test_result'] = 0;
				$skipwell = true;
			}else{
				$ins4['test_result'] = 1;
				$skipwell = false;
			}
			$crawler->db->insert('misctests',  $ins4, array('test_date'=>'NOW()'), 'REPLACE');
		
			if(!$skipwell){
				$urlbase = "http://{$site['site_domain']}/.well-known/";
				foreach($wellknowns as $wk){
					$url = "{$urlbase}{$wk}";
					$get =  $crawler->curl_get( $url );
					if($get['crawl_httpcode']=='200'){
						echo "Found {$url} => {$get['crawl_url']}";
						$ins = array();
						$ins['wk_url'] = $url;
						$ins['wk_domain'] = $site['site_domain'];
						$ins['wk_target'] = $get['crawl_url'];
						$ins['wk_crawl'] = $get['crawl_id'];
						$crawler->db->insert('wellknowns',  $ins, array('wk_crawled'=>'NOW()'), 'REPLACE');
					}
				}
			}
					
		}
		
		$pageno += $pagesize;
		
	}

?>
