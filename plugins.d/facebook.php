<?php
class observatoryCrawlerPluginFacebookAccounts extends observatoryCrawlerPluginRegexpList
{
	protected $id = "facebookAccounts";	
	protected $caseSensitive = false;
	protected $regexp = "[='\"]https?:\/\/(www\.)?facebook.com\/([^(plugins\/) >'\"]+)";
	public function addMatch( $matches ) { return $matches[2]; }
}
observatoryCrawlerPluginRegister::instance()->register( "observatoryCrawlerPluginFacebookAccounts" );
