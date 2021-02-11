<?php
class Location extends ArrayObject implements JsonSerializable {
	var $id = NULL;
	var $timestamp = NULL;
	var $refresh = 1;

	function __construct(ID $id = NULL) {
		$this->id = $id;
	}

	/**
	 * 
	 * @param ID $id
	 * @return NULL|Location
	 */
	static function get(ID $id) {
		// Get location from database
		if ($id instanceof ShareID) {
			$query = 'SELECT UNIX_TIMESTAMP(l.`timestamp`) AS `timestamp`, l.`location`
				FROM `locations` l
				WHERE `id` = ?';
		} elseif ($id instanceof FollowID) {
			$query = 'SELECT UNIX_TIMESTAMP(l.`timestamp`) AS `timestamp`, l.`location`
				FROM `locations` l, `followers` f
				WHERE l.`id` = f.`shareid` AND f.`enabled` = 1 AND (f.`expires` IS NULL OR f.`expires` >= NOW()) AND f.`followid` = ?';
		}
		$statement = DataStore::getInstance()->execute($query, [$id->id]);

		if($statement->rowCount() < 1) {
			return NULL;
		}

		$result = $statement->fetch();

		$instance = new Location();
		$parsed = json_decode($result['location'], TRUE);
		foreach ($parsed as $key => $value) {
			$instance[$key] = $value;
		}

		if (isset($instance['latitude']) && isset($instance['longitude'])) {
			$instance->timestamp = $result['timestamp'] + 0;
			// Calculate the recomended refresh interval based on timestamp
			if(date_create()->getTimestamp() - $result['timestamp'] < 60) {
				$instance->refresh = 1;
			} else {
				$instance->refresh = 5;
			}
		} else {
			return NULL;
		}

		$instance->id = $id;

		return $instance;
	}

	/**
	 * Insert location in database
	 * @return boolean TRUE if location has been succesfully stored else FALSE
	 */
	function store() {
		if (!$this->id) {
			//TODO Log error
			return FALSE;
		}

		$query = 'INSERT INTO `locations` (`id`, `location`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `location` = ?';
		$json = $this->json();
		return DataStore::getInstance()->execute($query, [$this->id->id, $json, $json]) !== FALSE;
	}

	/**
	 * Delete location from database
	 * @return boolean TRUE if location has been succesfully deleted else FALSE
	 */
	function delete() {
		if (!$this->id) {
			//TODO Log error
			return FALSE;
		}

		$query = 'DELETE FROM `locations` WHERE `id` = ?';
		return DataStore::getInstance()->execute($query, [$this->id->id]) !== FALSE;
	}

	function jsonSerialize() {
		return array_merge($this->getArrayCopy(),
				['timestamp' => $this->timestamp, "refresh" => $this->refresh]);
	}

	function json() {
		if (count($this) == 0) {
			return '{}';
		}

		global $configuration;

		return json_encode($this, $configuration['jsonoptions']);
	}
}