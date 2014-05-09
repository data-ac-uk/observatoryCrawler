<?php
	
	$config = (object) NULL;
	$config->pwd = dirname(__DIR__);

	$config->db = (object) NULL;
	$config->db->connection = "mysql:host=localhost;port=3306;dbname=crawler";
	$config->db->user = 'crawler';
	$config->db->password = 'crawler';

	$config->date = (object) NULL;
	$config->date->mysql = 'Y-m-d H:i:s';
	$config->date->mysqldate = 'Y-m-d';

	$config->crawl = (object) NULL;
	$config->crawl->cache = 24; //in Hours
	$config->crawl->timeout = 10;
	$config->crawl->agent = 'AcUkCrawler';
	$config->crawl->locationlimit = 10;
	$config->crawl->emailTo = array("andy@data.ac.uk");

	$config->websitedatalocation = "dataacuk@example.com:path/.";
	
	if(file_exists("{$config->pwd}/etc/config.local.php"))
	{
		require("{$config->pwd}/etc/config.local.php");
	}


	