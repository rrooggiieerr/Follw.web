<?php
require_once(dirname(__DIR__) . '/models/ID.php');
require_once(dirname(__DIR__) . '/models/FollowID.php');

class ShareID extends ID {
	var $type = 'share';

	function __construct(int $id = -1, $bytes = NULL) {
		$this->id = $id;
		$this->bytes = $bytes;
	}

	/**
	 * 8 bytes equals 64 bits equals 1.84467440737e+19 possible IDs
	 *
	 * @param number $nBytes Length in bytes of the unique ID
	 * @return ShareID
	 */
	function store() {
		$ds = DataStore::getInstance();

		if ($this->id > -1) {
			// Update configuration in database
			$query = 'UPDATE `issuedids` SET `config` = ? WHERE `id` = ?';
			return $ds->execute($query, [$this->json(), $this->id]) !== FALSE;
		}

		$ds->beginTransaction();
		$failureCounter = 0;
		$success = FALSE;
		do {
			// Generate unique ID
			$bytes = $this->generate();

			try {
				// Insert ID in database
				$query = 'INSERT INTO `issuedids` (`hash`, `type`, `config`) VALUES (?, \'share\', ?)';
				$ds->execute($query, [$this->hash($bytes), $this->json()]);
				$this->id = $ds->lastInsertId();
				$this->bytes = $bytes;
				$success = TRUE;
			} catch(PDOException $e) {
				//TODO Log error
				//$e->getCode()
				$failureCounter++;
			}
		} while (!$success && $failureCounter < 10);

		// Check if insert was successful
		if (!$success) {
			//TODO Log error
			$ds->rollback();
			return FALSE;
		}

		return $ds->commit();;
	}

	function delete() {
		$ds = DataStore::getInstance();
		$ds->beginTransaction();

		// Delete location
		$query = 'DELETE FROM `locations` WHERE `id` = ?';
		$ds->execute($query, [$this->id]);

		// Delete followers
		//$query = 'INSERT INTO `deletedids` (`hash`) SELECT `hash` FROM `issuedids` WHERE `id` IN (SELECT `followid` FROM `followers` WHERE `shareid` = ?)';
		//$statement = $ds->execute($query, [$shareID->id]);

		$query = 'UPDATE `issuedids` SET `type` = \'deleted\', `config` = \'\' WHERE `id` IN (SELECT `followid` FROM `followers` WHERE `id` = ?)';
		$ds->execute($query, [$this->id]);

		$query = 'DELETE FROM `followers` WHERE `shareid` = ?';
		$ds->execute($query, [$this->id]);

		//$query = 'DELETE FROM `issuedids` WHERE `type` = \'follow\' AND `id` NOT IN (SELECT `followid` FROM `followers`)';
		//$statement = $ds->execute($query);

		// Delete sharer
		//$query = 'INSERT INTO `deletedids` (`hash`) SELECT `hash` FROM `issuedids` WHERE `id` = ?';
		//$ds->execute($query, [$shareID->id]);

		//$query = 'DELETE FROM `issuedids` WHERE `id` = ?';
		$query = 'UPDATE `issuedids` SET `type` = \'deleted\', `config` = \'\' WHERE `id` = ?';
		$ds->execute($query, [$this->id]);

		return $ds->commit();
	}

	function updateConfig($key, $value) {
		$config = $this->config;
		$config[$key] = $value;
		return $this->setConfig($config);
	}

	function getFollowers() {
		$followers = [];

		$query = 'SELECT f.`followid`, f.`followidencrypted`, i.`config`, f.`enabled`, UNIX_TIMESTAMP(f.`starts`) AS `starts`, UNIX_TIMESTAMP(f.`expires`) AS `expires`, f.`delay`
					FROM `followers` f, `issuedids` i
					WHERE i.`id` = f.`followid` AND f.`shareid` = ? ORDER BY i.`created`';
		if($statement = DataStore::getInstance()->execute($query, [$this->id])) {
			while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
				$bytes = FollowID::decrypt($row['followidencrypted'], $this);
				$instance = new FollowID($this, $row['followid'], $bytes);

				$config = $row['config'];
				if($config) {
					$config = json_decode($config, TRUE);
					foreach ($config as $key => $value) {
						$instance[$key] = $value;
					}
				}

				$instance->enabled = $row['enabled'] ? TRUE : FALSE;

				if($row['starts']) {
					$instance->starts = $row['starts'];
					$instance->started = $instance->starts < time();
				} else {
					$instance->started = TRUE;
				}

				if($row['expires']) {
					$instance->expires = $row['expires'];
					$instance->expired = $instance->expires < time();
				} else {
					$instance->expired = FALSE;
				}

				if($row['delay']) {
					$instance->delay = $row['delay'];
				}
				$followers[] = $instance;
			}
		}

		return $followers;
	}
}