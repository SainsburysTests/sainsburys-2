<?php

class Sainsburys_Library_Curl
{

    /**
     * @var requestURL
     */
    protected $_requestURL;

    /**
     * @var requestURL
     */
    protected $_size;
    
    /**
     * @var userAgent
     */
    protected $_userAgent;
    
    public function __construct($requestURL)
    {
    	$this->_requestURL = $requestURL;
    	$this->_userAgent="Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
    	
    }

	public function _getURL()
    {
    	return $this->_requestURL;
    }
    
    public function _setSize($size){
    	$this->_size = $size;
    }
    
	public function _getSize()
    {
    	return $this->_size;
    }
    
    /**
     * @return array
     */
    public function getResult(){
    	$ch = curl_init();
		curl_setopt($ch, CURLOPT_USERAGENT, $this->_userAgent);
		curl_setopt($ch, CURLOPT_HTTPGET, 1 );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_URL, $this->_requestURL);	// The url to get links from
		curl_setopt($ch, CURLOPT_REFERER, '' );
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
 		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$result = curl_exec($ch);
		$size = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
		curl_close($ch);
		if(!$result){
			echo "<br />cURL error number:" .curl_errno($ch);
			echo "<br />cURL error:" . curl_error($ch);
			exit;
		}
		$this->_setSize($size);
		return $result;
    }   
	
}
?>