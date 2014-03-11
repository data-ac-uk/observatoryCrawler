<?php

class observatoryCrawlerSharePoint extends observatoryCrawlerPluginRegexp
{
	protected $id = "sharePoint";	
	protected $regexp = "MicrosoftSharePointTeamServices";
	protected $caseSensitive = false;
	protected $onlyHeaders = true;
}
observatoryCrawlerPluginRegister::instance()->register( "observatoryCrawlerSharePoint" );

