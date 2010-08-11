<?php

class LmeApiRequester {
	static function gatherContent($modules) {
		$mh = curl_multi_init();
		$handles = array();
		$running = null;
		
		foreach ($modules as $module => $urls) {
			foreach ($urls as $urlDescription => $url) {
				$cachedContent = self::tryLoadFromCache($url);
				if (!empty($cachedContent)) {
					$modules[$module][$urlDescription] = $cachedContent;
					continue;
				}
				
				$ch = curl_init();
				
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_TIMEOUT, 5);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
				
				curl_multi_add_handle($mh, $ch);
				$handles[$url] = $ch;
			}
		}
		
		do {
			curl_multi_exec($mh, $running);
			usleep(100000);
		} while ($running > 0); // polling is about as good as we're gonna get out of PHP
		
		foreach ($handles as $handleKey => $handle) {
			$content = curl_multi_getcontent($handle);
			curl_multi_remove_handle($mh, $handle);
			
			foreach ($modules as $module => $urls) {
				foreach ($urls as $urlDescription => $url) {
					if ($handleKey == $url) {
						$modules[$module][$urlDescription] = $content;
					}
				}
			}
		}
		
		curl_multi_close($mh);
	}
	static function getCompressCache() {
		return function_exists('gzdeflate') && function_exists('gzinflate');
	}
	static function getCacheKey($url) {
		return "lme-" . md5($url); // key can only be a certain length, so this will ensure we're under that limit
	}
	static function tryLoadFromCache($url) {
		$content = get_transient(self::getCacheKey($url));
		if (empty($content))
			return false;
		return false;
		if (self::getCompressCache())
			return unserialize(gzinflate(base64_decode($content)));
		else
			return $content;
	}
	static function setCache($url, $data) {
		$key = self::getCacheKey($url);
		$cacheSeconds = 60 * 60 * 24;
		
		if (self::getCompressCache())
			return set_transient($key, base64_encode(gzdeflate(serialize($data))), $cacheSeconds);
		else
			return set_transient($url, $data, $cacheSeconds);
	}
}

?>