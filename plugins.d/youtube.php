<?php
class observatoryCrawlerPluginYouTubeAccounts extends observatoryCrawlerPluginRegexpList
{
	protected $id = "youtubeAccounts";	
	protected $caseSensitive = false;
	protected $regexp = "[='\"]https?:\/\/(www\.)?youtube.com\/([^ >'\"\/\?]+)";
	public function addMatch( $matches ) { return $matches[2]; }
}
observatoryCrawlerPluginRegister::instance()->register( "observatoryCrawlerPluginYouTubeAccounts" );

class observatoryCrawlerPluginYouTubeVideo extends observatoryCrawlerPluginRegexp
{
	protected $id = "youtubeVideo";	
	protected $caseSensitive = false;
	protected $regexp = "<iframe\s[^>]*https?:\/\/(www\.)?youtube.com\/";
}
observatoryCrawlerPluginRegister::instance()->register( "observatoryCrawlerPluginYouTubeVideo" );
