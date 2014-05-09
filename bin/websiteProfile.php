<?php


	include '../uk.ac.data.Crawler.php';
	
	
	
	$crawler = new ukacdataCrawler();
	
	
	require_once( "{$crawler->config->pwd}/lib/Observe/Observe.php" );

	$plugins = CensusPluginRegister::instance();
	$plugins->loadDir( "{$crawler->config->pwd}/lib/Observe/plugins.d" );
	
	$mysqldate_start = date( $crawler->config->date->mysql );

	$loop = true;
	while($loop){
		
		$res = $crawler->db->fetch_one('websites', array('site_status' => "OK"));
		
		if(!isset($res['site_url'])) break;
	

		$crawler->curl_launch();
		
		$page = $crawler->curl_get( $res['site_url'] );
		
		$curl = (object) NULL;
		$curl->webpage = $page['crawl_content'];
		$curl->text = $page['crawl_text'];
		$curl->header = $page['crawl_header'];
	
		$result = $plugins->applyTo( $curl );

		print "\n {$res['site_url']}:\n";
		print json_encode($result );
		print "\n";
		
	}
