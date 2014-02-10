<?php

class observatoryCrawlerPluginSoftwareWordCount extends observatoryCrawlerPluginRegexpCount
{
	protected $id = "softwareWordCount";	
	protected $regexp = "\bsoftware\b";
	protected $caseSensitive = false;
}
observatoryCrawlerPluginRegister::instance()->register( "observatoryCrawlerPluginSoftwareWordCount" );

