<?php

class observatoryCrawlerPluginDrupal extends observatoryCrawlerPluginRegexp
{
	protected $id = "drupal";	
	protected $regexp = "\bdrupal\.js\b";
	protected $caseSensitive = false;
}
observatoryCrawlerPluginRegister::instance()->register( "observatoryCrawlerPluginDrupal" );

