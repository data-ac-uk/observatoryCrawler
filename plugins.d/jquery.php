<?php

class observatoryCrawlerPluginJQuery extends observatoryCrawlerPluginRegexp
{
	protected $id = "jquery";	
	protected $regexp = "jquery[\.a-z0-9-_]*\.js";
	protected $caseSensitive = false;
}
observatoryCrawlerPluginRegister::instance()->register( "observatoryCrawlerPluginJQuery" );

