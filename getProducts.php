<?php 
class getProducts{
	protected $_requestURL;
	
	public function __construct($requestURL)
	{
		$this->_requestURL = $requestURL;
	}
	
	public function getProducts()
	{			
		$cURL = new Sainsburys_Library_Curl($this->_requestURL);
		$result = $cURL->getResult();
		
		$dom = new DOMDocument();
		@$dom->loadHTML($result);
		$xpath = new DOMXPath($dom);
		$products = $xpath->query('//div[@class="productInner"]');
		
		$productAry = array();
		$total = 0;
  		foreach($products as $key=>$product) {
   			$arr = $product->getElementsByTagName("a");
		  	foreach($arr as $item) {
		  		if($item->parentNode->tagName == "h3") {
			      $href =  $item->getAttribute("href");
			      $text = trim(preg_replace("/[\r\n]+/", " ", $item->nodeValue));
			      //$size = round($size/1024,1);
			      $productAry[$key] = array(
			        'title' => $text
			      );
		  		}			    
		  	}
		  	$par = $product->getElementsByTagName("p");
		  	foreach($par as $item) {
			    $class =  $item->getAttribute("class");
			    if($class == "pricePerUnit"){
			     	$text = trim(preg_replace("/[\r\n]+/", " ", $item->nodeValue));
			     	$text = trim(trim(utf8_decode($text),"/unit"),"£");
			     	$productAry[$key] = array_merge($productAry[$key],
			      	 							array('unit_price'=>$text)); 
			      	$total += (float)$text;
			    }
			    
		  	}
		  	
		  	//To get the size of the linked HTML and the description of the product
		  	$productInfo = $this->getProductInfo($href);
			$productAry[$key] = array_merge($productAry[$key],
			      	 							$productInfo);
  		}
  		//$resultsAry = array_replace(array_flip(array('title', 'size', 'unit_price', 'description')), $productAry);//php >= 5.4
  		$resultsAry = $productAry;
  		return array('results'=>$resultsAry,
  							'total'=>number_format($total,2));
	}
	
	
    public function getProductInfo($url)
    {
    	$cURL = new Sainsburys_Library_Curl($url);
		$result = $cURL->getResult();
		$size = $cURL->_getSize();
		$size = round($size/1024,1);
		
		$dom = new DOMDocument();
		@$dom->loadHTML($result);

		$description='';
	    foreach($dom->getElementsByTagName("h3") as $node) {
	    	if($node->nodeValue == "Description") {
			    while(($node = $node->nextSibling) && $node->nodeName != "h3") {
			        if($node->nodeName == 'div') {
			            $description .= $node->nodeValue.' ';   
			        }
			    }
	    	}
		}

		 return array('size' => $size.'kb',
		 				'description' => trim(preg_replace("/[\r\n]+/", " ", $description))
		 			);
    }
	
	function getFileSize($url){
		$cURL = new Sainsburys_Library_Curl($url);
		return $cURL->getSize();
	}
	
	function toJSON($ary){
		return json_encode($ary);
	}
}
?>