<?php

include_once('lib/fatfree/lib/base.php');
include_once('lib/phpCurl.php');

class ukacdataCrawler {
	
	public $config = array();
	
	function __construct(){
		$config = (object) NULL;
		$config->pwd = __DIR__;
		
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
	
	
		$this->config = $config;
		
		$this->launch_db();
	}
	
	function launch_db(){
		if(!isset($this->db))
			$this->db = new mDB($this->config->db->connection,$this->config->db->user,$this->config->db->password);
		return $this->db;
	}
	
	function addDomain($domain, $src = ""){
		include_once $this->config->pwd.'/lib/tldextract/tldextract.php';
		include_once $this->config->pwd.'/lib/phpwhois/whois.main.php';
		
		$tld = new TLDExtract();
		
		$components = $tld->extract($domain);
		if(!strlen( (string) $components->domain ) || !strlen( (string) $components->tld )){
			return false;
		}

		$ins['domain_domain'] = "{$components->domain}.{$components->tld}";

		
		$ins['domain_tld'] = "{$components->tld}";
		$ins['domain_name'] = "{$components->domain}";

		$whois = new Whois();
		$result = @$whois->Lookup($ins['domain_domain']);
		
		if(isset($result['regrinfo']['owner']['onbehalfof'])){
			$ins['domain_onbehalfof'] = "{$result['regrinfo']['owner']['onbehalfof']}";
			if(isset($result['regrinfo']['owner']['organization'])){
				$ins['domain_org'] = "{$result['regrinfo']['owner']['organization']}";
			}else{
				$ins['domain_org'] = $ins['domain_onbehalfof'];
			}
		}
		$ins['domain_whois'] = json_encode($result);
		$ins['domain_enabled'] = 1;
		
		$ins['domain_src'] = $src;
		$insraw = array();

		$res = $this->db->fetch_one('domains', array('domain_domain' => $ins['domain_domain']), array(), "`domain_domain`");
		if(isset($res['domain_domain'])){
			$this->db->update('domains', $ins, $insraw, array('domain_domain' => $ins['domain_domain']));
		}else{
			$insraw['domain_firstseen'] = 'NOW()';
			$this->db->insert('domains',  $ins, $insraw);
		}
		return $this->db->fetch_one('domains', array('domain_domain' => $ins['domain_domain']));
		
	}
	
	
	function curl_launch(){
		if(!isset($this->curl))
			$this->curl = new phpCurl();
		
		$this->curl->timeout($this->config->crawl->timeout);
		curl_setopt($this->curl->ch, CURLOPT_USERAGENT, $this->config->crawl->agent);
		
		return $this->curl;
	}
	
	function curl_get($url, $follow = true, $cache = true, $locloop = 0){
		
		if($locloop >= $this->config->crawl->locationlimit){
			error_log("CURL To many redirects");
			return false;
		}
		
		if($cache){
			$res = $this->db->fetch_one('crawls', array('crawl_url' => $url), array('crawl_timestamp' => ">:DATE_SUB(NOW(), INTERVAL {$this->config->crawl->cache} hour)"));

			if(isset($res['crawl_url'])){
				if($follow){
					$info = json_decode($res['crawl_info'],true);
					if(strlen($info['redirect_url']) && $info['redirect_url'] != $url){
						return $this->curl_get($info['redirect_url'], $follow, $cache, $locloop+1);
					}
				}
				$this->curl_get_decode($res);
				return $res;
			}
		}
		
		$this->curl->followlocation(false);
		
		$ret = $this->curl->get($url);
		
		$ins = array();
		$ins['crawl_url'] = $this->curl->info['url'];
		$ins['crawl_httpcode'] = $this->curl->info['http_code'];
		$ins['crawl_header'] = substr($ret,0,$this->curl->info['header_size']);
		$ins['crawl_content'] = (string)substr($ret,$this->curl->info['header_size']);
		$ins['crawl_text'] = gzcompress(preg_replace( '/[\n\r]+/', "\n",(strip_tags( $ins['crawl_content'] ))));
		$ins['crawl_content'] = gzcompress($ins['crawl_content'] );
		$ins['crawl_info'] = json_encode($this->curl->info);
		if($cache){
			$ins['crawl_id'] = $this->db->insert('crawls',  $ins, array('crawl_timestamp'=>'NOW()'));
		}
		$ins['crawl_timestamp'] = date( $this->config->date->mysql );

		
		if($follow && strlen($this->curl->info['redirect_url']) &&  $this->curl->info['redirect_url'] != $url){
			return $this->curl_get($this->curl->info['redirect_url'], $follow, $cache, $locloop+1);
		}else{
			$this->curl_get_decode($ins);
			return $ins;
		}
		
	
	}
	
	function curl_get_decode(&$res){
		$res['crawl_text'] = gzuncompress($res['crawl_text']);
		$res['crawl_content'] = gzuncompress($res['crawl_content']);
	}
	
	
	// Original PHP code by Chirp Internet: www.chirp.com.au
	// Please acknowledge use of this code by including this header.

	function robots_parse($path, $robotstxt)
	{
		
		$useragent = $this->config->crawl->agent ;
	    // parse url to retrieve host and path
	   
	    $agents = array(preg_quote('*'));
	    if($useragent) $agents[] = preg_quote($useragent);
	    $agents = implode('|', $agents);

	    // if there isn't a robots, then we're allowed in
	    if(empty($robotstxt)) return true;
		
		$robotstxt = explode("\n",$robotstxt);

	    $rules = array();
	    $ruleApplies = false;
	    foreach($robotstxt as $line) {
	      // skip blank lines
	      if(!strlen(trim($line))) continue;
		  $line = trim($line);
	      // following rules only apply if User-agent matches $useragent or '*'
	      if(preg_match('/User-agent:(.*)/i', $line, $match)) {
			$ruleApplies = preg_match("/($agents)/i", $match[1]);
	      }
		
	      if($ruleApplies && preg_match('/^\s*Disallow:(.*)/i', $line, $regs)) {
			// an empty rule implies full access - no further tests required
	        if(!$regs[1]) return true;
	        // add rules that apply to array for testing
	        $rules[] = preg_quote(trim($regs[1]), '/');
	      }
	    }
	    foreach($rules as $rule) {
	      // check if page is disallowed to us
	      if(preg_match("/^$rule/", $path)) return false;
	    }

	    // page is not disallowed
	    return true;
	  }
	
}


class mDB extends DB\SQL {
	
	public $dryrun = false;
	
	function insert($table,$fields,$fieldsraw = array(), $type = 'INSERT'){
		
		
		if($this->dryrun){
			echo "SQL: Insert into: $table\n";
			foreach(array(&$fields,&$fieldsraw) as $a){
				foreach($a as $k=>$v){
					echo "\t{$k}=>".substr($v,0,255)."\n";
				}
			}
			return true;
		}
		
		$i = 1;
		foreach($fields as $k=>$v){
			$fieldsraw[$k] = "?";
			$infields[$i] = $v;
			$i++;
		}
		
		
		
		$sql = "$type into `$table` (`".join("`,`",array_keys($fieldsraw))."`) VALUES (".join(",",array_values($fieldsraw)).");"; 
		$this->exec($sql, $infields);		
		return $this->lastinsertid();
	}
	
	function where($params, $paramsraw, &$query, &$infields, &$i, &$orderby = array()){		
		
		$orderby = array();
		
		foreach($params as $k=>$v){
			if(substr($k,0,5)=='sort:'){
				if(substr($v,0,2)=="a:"){
					$orderby[] = substr($v,2)." ASC";
				}elseif(substr($v,0,2)=="d:"){
					$orderby[] = substr($v,2)." DESC";
				}else{
					$orderby[] = "$v ASC";
				}
			}elseif(substr($v,0,2)=="<:"){
				$query[] = "`$k` < ?";
				$infields[$i] = substr($v,2);
			}elseif(substr($v,0,2)==">:"){
				$query[] = "`$k` > ?";
				$infields[$i] = substr($v,2);
			}elseif(substr($v,0,2)=="!:"){
				$query[] = "`$k` != ?";
				$infields[$i] = substr($v,2);
			}else{
				$query[] = "`$k` = ?";
				$infields[$i] = $v;
			}
			
			$i++;
		}
		
		foreach($paramsraw as $k=>$v){
			
			if(substr($v,0,2)=="<:"){
				$query[] = "`$k` < ".substr($v,2);
			}elseif(substr($v,0,2)==">:"){
				$query[] = "`$k` > ".substr($v,2);
			}elseif(substr($v,0,2)=="!:"){
				$query[] = "`$k` != ".substr($v,2);
			}else{
				$query[] = "`$k` = $v";
			}
						
		}
		
		if(count($query)==0){
			$query[] = "1";
		}
		
	}
	
	function update($table, $fields,$fieldsraw, $params, $paramsraw = array(), $limit = false){
		
		$i = 1;
		$query = array();
		$infields = array();
		
		if($this->dryrun){
			echo "SQL: Insert into: $table\n";
			foreach(array(&$fields,&$fieldsraw) as $a){
				foreach($a as $k=>$v){
					echo "\t{$k}=>".substr($v,0,255)."\n";
				}
			}
			foreach(array(&$params,&$paramsraw) as $a){
				foreach($a as $k=>$v){
					$query[] = "`$k` = $v";
				}
			}
			echo "\t\tWHERE ".join(" AND ", $query)."\n";
			return true;
		}
		
		
		foreach($fields as $k=>$v){
			$fieldsraw[$k] = "?";
			$infields[$i] = $v;
			$i++;
		}
		
		$fieldsup = array();
		foreach($fieldsraw as $k=>$v){
			$fieldsup[] = "`$k` = $v";
		}
				
		$this->where($params, $paramsraw, $query, $infields, $i, $orderby);
	
		$sql = "UPDATE {$table} SET ".join(", ",$fieldsup)." WHERE ".join(" AND ", $query);
		
		if(count($orderby))
			$sql .= "ORDER BY ".join(", ",$orderby);
		
		if($limit)
			$sql .= " Limit ".$limit;

		return $this->exec($sql, $infields);
	}
	
	function delete($table, $params = array(), $paramsraw = array(), $limit = false){
		$i = 1;
		$query = array();
		$infields = array();
	
		
		if($this->dryrun){
			echo "SQL: Delete from: $table\n";
			foreach(array(&$params,&$paramsraw) as $a){
				foreach($a as $k=>$v){
					$query[] = "`$k` = $v";
				}
			}
			echo "\t\tWHERE ".join(" AND ", $query)."\n";
			return true;
		}
		
		
		$this->where($params, $paramsraw, $query, $infields, $i);
		
		$sql = "DELETE FROM {$table} WHERE ".join(" AND ", $query);
		
		if($limit)
			$sql .= " Limit ".$limit;

		return $this->exec($sql, $infields);
	}
	
	
	function fetch_many($table, $params = array(), $paramsraw = array(), $what = "*", $limit = false){
		$i = 1;
		$query = array();
		$infields = array();
		
		$this->where($params, $paramsraw, $query, $infields, $i,$orderby);
		
		$sql = "SELECT {$what} FROM {$table} WHERE ".join(" AND ", $query);
		if(count($orderby))
			$sql .= " ORDER BY ".join(", ",$orderby);
		
		if($limit)
			$sql .= " Limit ".$limit;
		return $this->exec($sql, $infields);
	}
	
	function fetch_one($table, $params = array(), $paramsraw = array(), $what = "*"){
		$res = $this->fetch_many($table, $params, $paramsraw, $what, 1);
		if(!isset($res[0]))
			return false;
		else
			return $res[0];
	}
}


