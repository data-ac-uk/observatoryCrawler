<?php

class phpCurl {
	
	function __construct(){
		$this->ch =  curl_init();
		curl_setopt($this->ch, CURLOPT_USERAGENT, "phpCurl");
		$this->cj = array();
		
	}
	
	function setUserPWD($user,$pass){
		curl_setopt($this->ch, CURLOPT_USERPWD, "$user:$pass");
	}
	
	function debug(){
		curl_setopt($this->ch, CURLOPT_VERBOSE, true);
	}
	
	function timeout($time){
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $time);
	}
	function followlocation($go = true){
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, $go);
	}
	
	function get($url, $params = array()){


		curl_setopt($this->ch, CURLOPT_HEADER, 1);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_URL, $url);

		$return = curl_exec($this->ch);
		
		$this->info =  curl_getinfo($this->ch);
		$this->info['header'] = $this->parse_headers(substr($return,0,$this->info['header_size']));
		$this->parse_cookie($this->info['header']);
		
		return $return;
	}
	
	
	
	
	
	function parse_headers($raw_headers)
	    {
	        $headers = array();
	        $key = ''; // [+]

	        foreach(explode("\n", $raw_headers) as $i => $h)
	        {
	            $h = explode(':', $h, 2);

	            if (isset($h[1]))
	            {
					
					if(in_array($h[0],array("Set-Cookie"))){
						if(!isset($headers[$h[0]])) 
							$headers[$h[0]] = array();
						$headers[$h[0]][] = trim($h[1]);
					}elseif (!isset($headers[$h[0]]))
	                    $headers[$h[0]] = trim($h[1]);
	                elseif (is_array($headers[$h[0]]))
	                {
	                    // $tmp = array_merge($headers[$h[0]], array(trim($h[1]))); // [-]
	                    // $headers[$h[0]] = $tmp; // [-]
	                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1]))); // [+]
	                }
	                else
	                {
	                    // $tmp = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [-]
	                    // $headers[$h[0]] = $tmp; // [-]
	                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1]))); // [+]
	                }

	                $key = $h[0]; // [+]
	            }
	            else // [+]
	            { // [+]
	                if (substr($h[0], 0, 1) == "\t") // [+]
	                    $headers[$key] .= "\r\n\t".trim($h[0]); // [+]
	                elseif (!$key) // [+]
	                    $headers[0] = trim($h[0]);trim($h[0]); // [+]
	            } // [+]
	        }

	        return $headers;
	    }
	
	
		function parse_cookie( $header ) {
			if(isset( $header['Set-Cookie'])){
				foreach( $header['Set-Cookie'] as $cookie ) {
                    $csplit = preg_split( "/\;\ /", $cookie );
					
                    $csplit2 = preg_split( "/=/", $csplit[0],2);
					$this->cj[$csplit2[0]] = $csplit2[1];
					
				}
				
			}
		}
}