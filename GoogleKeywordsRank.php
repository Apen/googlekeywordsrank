<?php

/**
 * Basic PHP class to get a the rank of your website for a keyword
 *
 * @author CERDAN Yohann <cerdanyohann@yahoo.fr>
 */
class GoogleKeywordsRank
{
	/** URL of the website to check in the google results **/
	private $url = '';

	/** Max number of pages of google to parse (there is 10 results per page) **/
	private $maxPages = 1;

	/** Extension of the google domain (fr,com,...) **/
	private $extension = 'fr';

	/** The HTML response send by the service **/
	private $response;

	/**
	 * Constuctor
	 *
	 * @param string $url        url of the site
	 * @param int    $maxPages   max pages
	 * @param string $extension  extension of the domain
	 */
	public function __construct($url, $maxPages = 1, $extension = 'fr') {
		$this->url = str_replace('http://www.', '', $url);
		$this->extension = $extension;
		if ($maxPages > 0) {
			$this->maxPages = $maxPages;
		} else {
			$this->maxPages = 1;
		}
	}

	/**
	 * Set the max number of pages of google to parse
	 *
	 * @param int $maxPages the max number of pages
	 */
	public function setMaxPages($maxPages) {
		if ($maxPages > 0) {
			$this->maxPages = $maxPages;
		} else {
			$this->maxPages = 1;
		}
	}

	/**
	 * Get the max number of pages of google to parse
	 *
	 * @return int maxPages
	 */
	public function getMaxPages() {
		return $this->maxPages;
	}

	/**
	 * Get URL content using cURL
	 *
	 * @param string $url url to get
	 * @return string error code
	 * @throws Exception
	 */
	public function getContent($url) {
		if (!extension_loaded('curl')) {
			throw new Exception('curl extension is not available on your installation');
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_TIMEOUT, 10);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($curl, CURLOPT_URL, $url);
		$this->response = curl_exec($curl);
		$infos = curl_getinfo($curl);
		curl_close($curl);
		return $infos['http_code'];
	}

	/**
	 * Get the position of the keywords
	 *
	 * @param string $keywords
	 * @return array
	 * @throws Exception
	 */
	public function getKeywordsRank($keywords) {
		if (isset($this->url) && isset($keywords)) {
			$baseUrl = 'http://www.google.' . $this->extension . '/search?hl=fr&q=' . urlencode($keywords) . '&start=';
			$index = 0;
			$page = 0;
			for ($page = 0; $page < $this->maxPages; $page++) {
				$makeUrl = $baseUrl . ($page * 10);
				$getContentCode = $this->getContent($makeUrl);
				if ($getContentCode == 200) {
					if (preg_match_all('/<h3 class="r"><a href="([^"]+)".*?>.+?<\/a>/', $this->response, $results) > 0) {
						foreach ($results[1] as $link) {
							$link = preg_replace('(^http://|/$)', '', $link);
							$index++;
							if (strlen(stristr($link, $this->url)) > 0) {
								return array($keywords, $index);
							}
						}
					} else {
						throw new Exception('Google results parse problem : could not find the html result code ');
					}
				} else {
					throw new Exception('Google results parse problem : http error ' . $getContentCode);
				}
			}
		}
		return NULL;
	}

	/**
	 * Get the position of the keywords array
	 * There is a sleep() function of 3 secondes because google can ban you for 24 hours if the number of search is too large(1000 req/24 hour)
	 *
	 * @param array $keywords array of keywords
	 * @param int   $seconds  number of seconds between request
	 *
	 * @return array arrays with keywords=>rank
	 */
	public function getKeywordsArrayRank($keywords, $seconds = 3) {
		$keywordsRank = array();
		foreach ($keywords as $keyword) {
			$rank = $this->getKeywordsRank($keyword);
			if ($rank) {
				$keywordsRank[] = $rank;
			} else {
				$keywordsRank[] = array($keyword, 0);
			}
			sleep($seconds);
		}
		return $keywordsRank;
	}

}

?>