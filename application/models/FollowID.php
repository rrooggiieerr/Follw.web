<?php
require_once(dirname(__DIR__) . '/models/ID.php');

class FollowID extends ID implements JsonSerializable {
	var $type = 'follow';

	var $shareID = NULL;
	var $enabled = FALSE;
	var $expires = NULL;
	var $delay = NULL;

	/**
	 *
	 * @param ShareID $shareID
	 * @param int $id
	 * @param $bytes
	 */
	function __construct(ShareID $shareID = NULL, int $id = -1, $bytes = NULL) {
		$this->shareID = $shareID;
		$this->id = $id;
		$this->bytes = $bytes;
	}

	function store() {
		if (!$this->shareID) {
			//TODO Log error
			return FALSE;
		}

		$ds = DataStore::getInstance();
		
		if ($this->id > -1) {
			// Update configuration in database
			$query = 'UPDATE `issuedids` SET `config` = ? WHERE `id` = ?';
			return $ds->execute($query, [json_encode($this->getArrayCopy(), JSON_UNESCAPED_UNICODE), $this->id]) !== FALSE;
		}

		$ds->beginTransaction();
		$failureCounter = 0;
		$success = FALSE;
		do {
			// Generate unique ID
			$bytes = $this->generate();

			try {
				// Insert ID in database
				$query = 'INSERT INTO `issuedids` (`hash`, `type`, `config`) VALUES (?, \'follow\', ?)';
				$ds->execute($query, [$this->hash($bytes), json_encode($this->getArrayCopy(), JSON_UNESCAPED_UNICODE)]);
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
		
		$query = 'INSERT INTO `followers` (`shareid`, `followid`, `followidencrypted`, `enabled`, `expires`, `delay`) VALUES (?, ?, ?, ?, FROM_UNIXTIME(?), ?)';
		$ds->execute($query, [$this->shareID->id, $this->id, $this->encrypt(), $this->enabled, $this->expires, $this->delay]);

		return $ds->commit();
	}

	function enable() {
		if (!$this->shareID) {
			//TODO Log error
			return FALSE;
		}

		$query = 'UPDATE `followers` SET `enabled` = 1 WHERE `shareid` = ? AND `followid` = ?';
		return DataStore::getInstance()->execute($query, [$this->shareID->id, $this->id]) !== FALSE;
	}

	function disable() {
		if (!$this->shareID) {
			//TODO Log error
			return FALSE;
		}

		$query = 'UPDATE `followers` SET `enabled` = 0 WHERE `shareid` = ? AND `followid` = ?';
		return DataStore::getInstance()->execute($query, [$this->shareID->id, $this->id]) !== FALSE;
	}

	function delete() {
		if (!$this->shareID) {
			//TODO Log error
			return FALSE;
		}

		$ds = DataStore::getInstance();
		$ds->beginTransaction();

		$query = 'DELETE FROM `followers` WHERE `shareid` = ? AND `followid` = ?;';
		$statement = $ds->execute($query, [$this->shareID->id, $this->id]);
		if($statement->rowCount() == 0) {
			//TODO Log error
			$ds->rollback();
			return FALSE;
		} else if($statement->rowCount() > 1) {
			//TODO Log error
			$ds->rollback();
			return FALSE;
		}

		//$query = 'INSERT INTO `deletedids` (`hash`) VALUES (?)';
		$query = 'UPDATE `issuedids` SET `type` = \'deleted\', `config` = \'\' WHERE `id` = ?';
		$statement = $ds->execute($query, [$this->id]);
		if($statement->rowCount() == 0) {
			//TODO Log error
			$ds->rollback();
			return FALSE;
		} else if($statement->rowCount() > 1) {
			//TODO Log error
			$ds->rollback();
			return FALSE;
		}

		//$query = 'DELETE FROM `issuedids` WHERE `hash` = ?';
		//$statement = $ds->execute($query, [$this->hash()]);
		//if($statement->rowCount() == 0) {
		//	//TODO Log error
		//	$ds->rollback();
		//	return FALSE;
		//} else if($statement->rowCount() > 1) {
		//	//TODO Log error
		//	$ds->rollback();
		//	return FALSE;
		//}

		return $ds->commit();
	}

	function encrypt() {
		global $configuration;

		$ivlen = openssl_cipher_iv_length($configuration['id']['cipher']);
		$iv = random_bytes($ivlen);
		$encrypted = openssl_encrypt($this->bytes, $configuration['id']['cipher'], $this->shareID->bytes, OPENSSL_RAW_DATA, $iv);

		return $iv . $encrypted;
	}

	static function decrypt($bytes, ShareID $shareID) {
		global $configuration;

		$ivlen = openssl_cipher_iv_length($configuration['id']['cipher']);
		$iv = substr($bytes, 0, $ivlen);
		$encrypted = substr($bytes, $ivlen);

		return openssl_decrypt($encrypted, $configuration['id']['cipher'], $shareID->bytes, OPENSSL_RAW_DATA, $iv);
	}

	function jsonSerialize() {
		$a = array_merge(parent::jsonSerialize(),
			['expires' => $this->expires,
			'delay' => $this->delay]);

		if ($this->shareID) {
			$a = array_merge($a,
				['enabled' => $this->enabled,
				'expired' => $this->expired,
				'url' => $this->url()]);
		}

		return $a;
	}
}