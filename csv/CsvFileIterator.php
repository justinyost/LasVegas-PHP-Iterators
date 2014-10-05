<?php
/**
 * CsvFileIterator
 *
 * @link https://github.com/codeinthehole/php-csv-file/blob/master/CSV/Iterator.php
 * @link https://gist.github.com/beporter/188863f6158d15ddc4c9
 */

/**
 * Convenience iterator that wraps around both SplFileObject to make
 * looping over records in a CSV file as painless as possible by returning
 * associative arrays, skipping invalid rows and type-casting values.
 *
 */
class CsvFileIterator extends FilterIterator {

	/**
	 * Store the columns (and their order) obtained from the first row of
	 * the CSV file.
	 *
	 * @var	array
	 */
	protected $_names = null;

	/**
	 * Minimum fields required to be present in first row. If any of these
	 * values is missing during initialization, an exception will be thrown
	 * from _setColumnNames().
	 *
	 * @var	array
	 */
	protected $_requiredFields = array(
	);

	/**
	 * __construct
	 *
	 * Build a new Iterator using a combination of SplFileObject and fgetcsv()
	 * options.
	 *
	 * @access	public
	 * @param	string	$pathToFile		A full or relative path to a CSV-formatted file.
	 * @param	array	$requiredFields	An ordered array of fields expected to be present in the first row of any CSV file opened with this Iterator.
	 * @param	string	$delimiter		The field delimiter to use for the given CSV file. Default: `,`.
	 * @param	string	$fieldEnclosure	The field enclosure character. Default: `"`.
	 * @param	string	$escapeChar		The escape character. Default: `\\`
	 */
	public function __construct(
		$pathToFile,
		$requiredFields = array(),
		$delimiter = ",",
		$fieldEnclosure = '"',
		$escapeChar = "\\"
	) {
		parent::__construct(new SplFileObject($pathToFile, 'r'));

		$file = $this->getInnerIterator();
		$file->setFlags(SplFileObject::READ_CSV);
		$file->setCsvControl($delimiter, $fieldEnclosure, $escapeChar);

		$this->_setRequiredFields($requiredFields);
		$this->_setColumnNames($file->current());
	}

	/**
	 * accept
	 *
	 * Called internally before each call to `::current()`. When
	 * it returns false, the iterator skips to the next record.
	 *
	 * Ensures that only "valid" records are returned by
	 * the iterator. Also handily skips accidentally-empty rows.
	 *
	 * @access	public
	 * @return	boolean					True if record is valid, false otherwise.
	 */
	public function accept() {
		$key = $this->getInnerIterator()->key();
		$current = $this->getInnerIterator()->current();

		// Process every line when no column names available.
		if (!is_array($this->_names)) {
			return true;
		}
		// Skip the first line when column names are set.
		if ($key === 0) {
			return false;
		}
		// Process lines with the correct field count (when we know how many fields there should be).
		return (count($current) == count($this->_names));
	}

	/**
	 * current
	 *
	 * Return the "current" record in the iterator.
	 *
	 * Attempt to build an associative array out of the values for the current
	 * row. ::accept() ensures that we have the proper number of columns ahead
	 * of time.
	 *
	 * @access	public
	 * @return	array					Returns the array of fields recognized from the CSV file with keys defined by `::$_names`.
	 */
	public function current() {
		$row = $this->getInnerIterator()->current();
		if (!empty($this->_names)) {
			$row = array_combine($this->_names, $row);
		}
		$row = $this->_correctFields($row);
		return $row;
	}

	/**
	 * getColumnNames
	 *
	 * Returns the ordered array of column _names in use for the given file.
	 * Useful for verifying the columns present in the file against an
	 * outside (expected) list.
	 *
	 * @return	array			The numerically indexed, ordered array of columns in the current CSV file.
	 */
	public function getColumnNames() {
		return $this->_names;
	}

	/**
	 * _setColumnNames
	 *
	 * Ensures that the provided array of $names contains at least the
	 * values defined in ::$_requiredFields, and sets the keys to be used
	 * for each record returned from the iterator.
	 *
	 * @access	protected
	 * @throws	RuntimeException	If any ::$_requiredFields are missing from $names.
	 * @param	array		$names	An ordered, indexed array of column names to expect from each row in the CSV file.
	 * @return	CsvFileIterator 	$this
	 */
	protected function _setColumnNames(array $names) {
		$missingFields = array_diff($this->_requiredFields, $names);
		if (count($missingFields)) {
			throw new RuntimeException('Minimum required fields [' . implode(', ', $missingFields) . '] missing from header row.');
		}
		$this->_names = $names;
		return $this;
	}

	/**
	 * _setRequiredFields
	 *
	 * Assigns the provided array of indexed fields names to the internal
	 * $_requiredFields property.
	 *
	 * @access	protected
	 * @param	array		$requiredFields
	 */
	protected function _setRequiredFields(array $requiredFields) {
		$this->_requiredFields = array_values($requiredFields);
	}

	/**
	 * _correctFields
	 *
	 * Loops over all fields in the provided $row, calling ::_correctField()
	 * for each.
	 *
	 * @codeCoverageIgnore			Don't test a foreach loop.
	 * @access	protected
	 * @param	array	$row		A single CSV row represented as an array of [column_name => value] pairs.
	 * @return	array				An array where the values have been type coerced and in special cases, modified.
	 */
	protected function _correctFields(array $row) {
		foreach ($row as $k => $v) {
			$row[$k] = $this->_correctField($k, $v);
		}
		return $row;
	}

	/**
	 * _correctField
	 *
	 * Provides a mechanism for making field-specific corrections. If a
	 * field is not specified, it will be type-coerced only. Subclasses
	 * can replace this method to perform field-specific adjustments.
	 *
	 * @access	protected
	 * @param	string	$key		A CSV field name.
	 * @param	string	$value		A raw CSV field value, always represented as a string.
	 * @return	mixed				Either the same value passed in, or a "corrected" version.
	 */
	protected function _correctField($key, $value) {
		switch ($key) {
			// case 'sample_csv_column_name':
			//	$value .= ' some special suffix';
			//	break;
			default:
				$value = $this->_correctValueType($value);
				break;
		}
		return $value;
	}

	/**
	 * _correctValueType
	 *
	 * Applies some text-to-value adjustments for known values since
	 * everything from the CSV file is represented as a string. Used by
	 * `::groupKeys()` since we're already hitting every array element
	 * there.
	 *
	 * @access	public
	 * @param	string	$v			A single CSV field value, always represented as a string.
	 * @return	mixed				Either the same value passed in, or a "corrected" version.
	 */
	protected function _correctValueType($v) {
		// The ORDER of these checks MATTERS.
		// (All ints validate as bools. If bool check is first, all ints get cast as bools.)
		if (strtolower($v) === 'null') {
			$v = null;
		} elseif (filter_var($v, FILTER_VALIDATE_INT)) {
			$v = intval($v);
		} elseif (filter_var($v, FILTER_VALIDATE_FLOAT)) {
			$v = floatval($v);
		} elseif (filter_var($v, FILTER_VALIDATE_BOOLEAN)) {
			$v = ($v ? true : false);
		} else {
			$v = trim($v);
		}
		return $v;
	}
}