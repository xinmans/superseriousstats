<?php

/**
 * Copyright (c) 2007-2010, Jos de Ruijter <jos@dutnie.nl>
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

/**
 * Class for handling URL data.
 */
final class URL extends Base
{
	/**
	 * Variables used in database table "user_URLs".
	 */
	protected $csURL = '';
	protected $total = 0;
	protected $firstUsed = '';
	protected $lastUsed = '';

	/**
	 * Constructor.
	 */
	public function __construct($csURL)
	{
		$this->csURL = $csURL;
	}

	/**
	 * Store the date and time of when the URL was first and last typed in the channel.
	 */
	public function lastUsed($dateTime)
	{
		if ($this->firstUsed == '' || $dateTime < $this->firstUsed) {
			$this->firstUsed = $dateTime;
		}

		if ($this->lastUsed == '' || $dateTime > $this->lastUsed) {
			$this->lastUsed = $dateTime;
		}
	}

	/**
	 * Write URL data to the database.
	 */
	public function writeData($mysqli, $UID)
	{
		/**
		 * Write data to database table "user_URLs".
		 */
		if (($query = @mysqli_query($mysqli, 'SELECT * FROM `user_URLs` WHERE `UID` = '.$UID.' AND `csURL` = \''.mysqli_real_escape_string($mysqli, $this->csURL).'\'')) === FALSE) {
			return FALSE;
		}

		$rows = mysqli_num_rows($query);

		if (empty($rows)) {
			/**
			 * Check if the URL exists in the database paired with an UID other than mine and if it does, use its LID in my own insert query.
			 */
			if (($query = @mysqli_query($mysqli, 'SELECT `LID` FROM `user_URLs` WHERE `csURL` = \''.mysqli_real_escape_string($mysqli, $this->csURL).'\' GROUP BY `csURL`')) === FALSE) {
				return FALSE;
			}

			$rows = mysqli_num_rows($query);

			if (empty($rows)) {
				if (!@mysqli_query($mysqli, 'INSERT INTO `user_URLs` (`LID`, `UID`, `csURL`, `total`, `firstUsed`, `lastUsed`) VALUES (0, '.$UID.', \''.mysqli_real_escape_string($mysqli, $this->csURL).'\', '.$this->total.', \''.mysqli_real_escape_string($mysqli, $this->firstUsed).'\', \''.mysqli_real_escape_string($mysqli, $this->lastUsed).'\')')) {
					return FALSE;
				}
			} else {
				$result = mysqli_fetch_object($query);

				if (!@mysqli_query($mysqli, 'INSERT INTO `user_URLs` (`LID`, `UID`, `csURL`, `total`, `firstUsed`, `lastUsed`) VALUES ('.$result->LID.', '.$UID.', \''.mysqli_real_escape_string($mysqli, $this->csURL).'\', '.$this->total.', \''.mysqli_real_escape_string($mysqli, $this->firstUsed).'\', \''.mysqli_real_escape_string($mysqli, $this->lastUsed).'\')')) {
					return FALSE;
				}
			}
		} else {
			$result = mysqli_fetch_object($query);

			if (!@mysqli_query($mysqli, 'UPDATE `user_URLs` SET `csURL` = \''.mysqli_real_escape_string($mysqli, $this->csURL).'\', `total` = '.((int) $result->total + $this->total).', `firstUsed` = \''.mysqli_real_escape_string($mysqli, $this->firstUsed.':00' < $result->firstUsed ? $this->firstUsed : $result->firstUsed).'\', `lastUsed` = \''.mysqli_real_escape_string($mysqli, $this->lastUsed.':00' > $result->lastUsed ? $this->lastUsed : $result->lastUsed).'\' WHERE `LID` = '.$result->LID.' AND `UID` = '.$UID)) {
				return FALSE;
			}
		}

		return TRUE;
	}
}

?>
