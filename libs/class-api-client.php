<?php

class WPAdflyDashboardIntegrationAPIClient {

	const BASIC = 1;
	const HMAC = 2;

	const BASE_HOST = 'https://api.adf.ly';
	const HMAC_ALGO = 'sha256';
	
	private $userId = 0;
	private $publicKey = '';
	private $secretKey = '';

	public function __construct($userId, $publicKey, $secretKey) {
		$this->secretKey = $secretKey;
		$this->publicKey = $publicKey;
		$this->userId = $userId;
	}

	private function doGet($endpoint, array $payload) {
		 
		$args = array(
		    'body' => $payload,
		    'timeout' => '5',
		    'redirection' => '5',
		    'httpversion' => '1.0',
		    'blocking' => true,
		    'headers' => array(),
		    'cookies' => array()
		);
		 
		$ret = wp_remote_get( self::BASE_HOST.$endpoint, $args );

		return json_decode(wp_remote_retrieve_body( $ret ), true);
	}

	private function getParams(array $params = [], $authType = self::BASIC) {

		$params['_user_id'] = $this->userId;
		$params['_api_key'] = $this->publicKey;

		if (self::BASIC == $authType) {

		} else if (self::HMAC == $authType) {
			// Get current unix timestamp (UTC time).
			$params['_timestamp'] = time();
			// And calculate hash.
			$params['_hash'] = $this->doHmac($params);
		}

		return $params;
	}

	private function doHmac(array $params) {

		// Built-in 'http_build_query' function which is used
		// to construct query string does not include parameters with null
		// values which is incorrect in our case.
		$params = array_map(function($x) { return is_null($x) ? '' : $x; }, $params);

		// Sort query parameters by names using byte ordering.
		// So 'param[10]' comes before 'param[2]'.
		if (ksort($params)) {
			// Url encode parameters. The encoding should be performed
			// per RFC 1738 (http://www.faqs.org/rfcs/rfc1738)
			// which implies that spaces are encoded as plus (+) signs.
			$queryStr = http_build_query($params);
			// Generate hash value based on encoded query string and
			// secret key.
			return hash_hmac(self::HMAC_ALGO, $queryStr, $this->secretKey);
		} else {
			throw new Exception('Could not ksort data array');
		}
	}

	public function getPublisherStats($date = null, $urlId = 0){
		$params = array();
		if(!empty($date)) $params['date'] = $date;
		if(!empty($urlId)) $params['urlId'] = $urlId;

		return $this->doGet('/v1/publisherStats',$this->getParams($params, self::HMAC));
	}

	public function getPushadStats($start = null) {

        $params = array();

        if (!empty($start))
            $params['start'] = $start;

        return $this->doGet('/v1/pushadStats', $this->getParams($params, self::HMAC));
    }

    public function getPopAdStats($start = null) {

        $params = array();

        if (!empty($start))
            $params['start'] = $start;

        return $this->doGet('/v1/popadStats', $this->getParams($params, self::HMAC));
    }

}