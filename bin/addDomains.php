<?php


	include '../uk.ac.data.Crawler.php';
	
	
	$crawler = new ukacdataCrawler();
	
	
	$domain = $crawler->addDomain("soton.ac.uk");