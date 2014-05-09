<?php

	$_SERVER['SERVER_PROTOCOL'] = 'cli';
	$_SERVER['SERVER_PORT'] = '0';

	ini_set('memory_limit','256M');

	include '../uk.ac.data.Crawler.php';
	
	$crawler = new ukacdataCrawler();
	
	

	$crawler->curl_launch();
	//$page = $crawler->curl_get( "http://www.ecs.soton.ac.uk" , true, false);
	$page = $crawler->curl_get( "http://www.ravemedia.ac.uk/robots.txt");
	
	print_r($page);