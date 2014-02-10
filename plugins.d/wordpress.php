<?php

class observatoryCrawlerPluginWordPress extends observatoryCrawlerPluginRegexp
{
	protected $id = "wordpress";	
	protected $regexp = "<meta[^>]+generator[^>]wordpress";
	protected $caseSensitive = false;
}
observatoryCrawlerPluginRegister::instance()->register( "observatoryCrawlerPluginWordPress" );

