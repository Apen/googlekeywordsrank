Google Keywords Rank API
==================

Basic PHP class to get a the rank of your website for a keyword

Usage
------------

	require_once('GoogleKeywordsRank.php');
	$gRank = new GoogleKeywordsRank('http://www.ycerdan.fr');
	$gRank->setMaxPages(5);
	
	$keywords = array();
	$keywords[] = "typo3";
	
	$keywordsPositions = $gRank->getKeywordsArrayRank($keywords);
	
	foreach ($keywordsPositions as $keywords) {
		echo 'For teh keyword "' . $keywords[0] . '": ';
		if ($keywords[1] == 0) {
			echo 'you are not in the ' . ($maxPages * 10) . ' first results';
		} else {
			echo 'you are ranked ' . $keywords[1];
		}
	}