#!/usr/bin/php
<?php


require_once( "observatoryCrawler.php" );
require_once( "mycurl.php" );

$plugins = observatoryCrawlerPluginRegister::instance();
$plugins->loadDir(
 "plugins.d" );

$domains = array(
	"totl.net",
	"ecs.soton.ac.uk",
	"eprints.org",
	"microsoft.com",
	"communitymodel.sharepoint.com" );

$ttl = 10;
foreach( $domains as $domain )
{
	$url = "http://$domain/";
		
	$curl = new mycurl( true, 10, 10 );


	$curl->createCurl( $url );
	
	$result = $plugins->applyTo( $curl );

	print "\n$domain:\n";
	print json_encode($result );
	print "\n";

}



