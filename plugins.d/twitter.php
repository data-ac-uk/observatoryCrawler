<?php
class observatoryCrawlerPluginTwitterAccounts extends observatoryCrawlerPluginRegexpList
{
	protected $id = "twitterAccounts";	
	protected $caseSensitive = false;
	protected $regexp = "[='\"]https?:\/\/(www\.)?twitter.com\/([^ >'\"]+)";
	public function addMatch( $matches ) { return $matches[2]; }
}
observatoryCrawlerPluginRegister::instance()->register( "observatoryCrawlerPluginTwitterAccounts" );
