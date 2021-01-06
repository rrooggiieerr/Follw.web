<?php
require_once(dirname(__DIR__) . '/libs/Base.php');
require_once(dirname(__DIR__) . '/libs/DataStore.php');
require_once(dirname(__DIR__) . '/models/ShareID.php');
require_once(dirname(__DIR__) . '/models/FollowID.php');

class ID extends ArrayObject {
	var $type = NULL;
	var $id = -1;
	var $bytes = NULL;
	var $config = [];

	protected $encodeCache = [];

	function __construct(int $id, $bytes) {
		$this->id = $id;
		$this->bytes = $bytes;
	}

	/**
	 * 
	 * @param string $s Base encoded string of the ID
	 * @param ShareID $shareID
	 * @return ID
	 */
	public static function decode(string $s, ShareID $shareID = NULL) {
		global $configuration;
		$binary = Base::decode($s, $configuration['id']['baseEncoding']);

		/*$query = 'SELECT `hash` FROM `deletedids` WHERE `hash` = ?';
		$statement = DataStore::getInstance()->execute($query, [(new ID(-1, $binary))->hash()]);
		 
		if($statement->rowCount() > 1) {
			//TODO Log error
			//TODO http_response_code should be in the controller, not in the model
			http_response_code(500);
			exit();
		}
		if($statement->rowCount() > 0) {
			return new DeletedID($binary);
		}*/

		if ($shareID) {
			$query = 'SELECT f.`id`, f.`type`, f.`type`, f.`config`, sf.`enabled`, sf.`expires`, sf.`delay`
				FROM `issuedids` f, `followers` sf
				WHERE f.`id` = sf.`followid` AND f.`type` = \'follow\' AND f.`hash` = ? AND sf.`shareid` = ?';
			//$query = 'SELECT `followid`, `enabled`, `expires`, `delay` FROM `followers` WHERE `shareid` = ? AND `followidraw` = ?';
			$statement = DataStore::getInstance()->execute($query, [(new ID(-1, $binary))->hash(), $shareID->id]);

			if($statement->rowCount() > 1) {
				//TODO Log error
				//$e->getCode()
				//TODO http_response_code should be in the controller, not in the model
				http_response_code(500);
				exit();
			}
			if($statement->rowCount() < 1) {
				// ID does not exist or is not a Follow ID or is not lnked to this Share ID
				//TODO Rate limit requests per IP to prevent guessing
				//http_response_code(429);
				http_response_code(404);
				return NULL;
			}

			$result = $statement->fetch();
			$id = $result['id'];
			$instance = new FollowID($shareID, $id, $binary);
			$instance->enabled = $result['enabled'] === 1; // TRUE if 1 else FALSE
			$instance->expires = $result['expires'];
			$instance->delay = $result['delay'];
		} else {
			$query = 'SELECT f.`id`, f.`type`, f.`config`, sf.`enabled`, UNIX_TIMESTAMP(sf.`expires`) AS \'expires\', TIME_TO_SEC(sf.`delay`) AS \'delay\', s.`config` AS \'sharerConfig\'
				FROM `issuedids` f
				LEFT JOIN `followers` sf ON sf.`followid` = f.`id`
				LEFT JOIN `issuedids` s ON s.`id` = sf.`shareid`
				WHERE f.`hash` = ?';
			$statement = DataStore::getInstance()->execute($query, [(new ID(-1, $binary))->hash()]);

			if($statement->rowCount() > 1) {
				//TODO Log error
				//TODO http_response_code should be in the controller, not in the model
				http_response_code(500);
				exit();
			}

			if($statement->rowCount() < 1) {
				// ID does not exist
				//TODO Rate limit requests per IP to prevent guessing
				return NULL;
			}

			$result = $statement->fetch();
			$id = $result['id'];
			$type = $result['type'];

			$instance = NULL;
			switch ($type) {
				case 'share':
					$instance = new ShareID($id, $binary);
					break;
				case 'follow':
					$instance = new FollowID(NULL, $id, $binary);
					$instance->enabled = $result['enabled'] === 1; // TRUE if 1 else FALSE
					$instance->expires = $result['expires'];
					$instance->delay = $result['delay'];
					break;
				case 'deleted':
					$instance = new ID(-1, $binary);
					$instance->type = 'deleted';
					break;
				case 'reserved':
					$instance = new ID(-1, $binary);
					$instance->type = 'reserved';
					break;
			}
		}

		$config = $result['config'];
		if ($config) {
			$config = json_decode($config, TRUE);
			foreach ($config as $key => $value) {
				$instance[$key] = $value;
			}
		}

		if ($instance instanceof FollowID && !isset($instance['alias']) && array_key_exists('sharerConfig', $result)) {
			$sharerConfig = json_decode($result['sharerConfig'], TRUE);
			if ($sharerConfig && array_key_exists('alias', $sharerConfig)) {
				$instance['alias'] = $sharerConfig['alias'];
			} else {
				$instance['alias'] = 'Something';
			}
		}

		$instance->encodeCache[$configuration['id']['baseEncoding']] = $s;

		return $instance;
	}

	/**
	 * The base encoded string is used in the URL
	 * @return string
	 */
	function encode() {
		global $configuration;

		if(array_key_exists($configuration['id']['baseEncoding'], $this->encodeCache)) {
			return $this->encodeCache[$configuration['id']['baseEncoding']];
		}

		$s = Base::encode($this->bytes, $configuration['id']['baseEncoding']);
		$this->encodeCache[$configuration['id']['baseEncoding']] = $s;
		return $s;
	}

	function generate() {
		global $configuration;

		return random_bytes($configuration['id']['nBytes']);
	}

	/**
	 * The binary hash is used in the database
	 * @return object the hash of the ID as a 16 byte binary
	 */
	function hash($bytes = NULL) {
		global $configuration;

		if($bytes) {
			return hash($configuration['id']['hashAlgorithm'], $bytes, TRUE);
		}

		return hash($configuration['id']['hashAlgorithm'], $this->bytes, TRUE);
	}

	function jsonSerialize() {
		return array_merge(['id' => $this->encode()], $this->getArrayCopy());
	}

	function json() {
		return json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}
}