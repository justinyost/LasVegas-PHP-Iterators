<?php
/**
 * SimpleIterator
 *
 * @link https://gist.github.com/beporter/188863f6158d15ddc4c9
 */
class SimpleIterator extends FilterIterator {

	public function __construct($pathToFile) {
		// Call the parent constructor with an SplFileObject (also Traversable) for the given path.
		parent::__construct(new SplFileObject($pathToFile, 'r'));

		// These set up the inner SplFileObject's properties to process CSV.
		$file = $this->getInnerIterator();
		$file->setFlags(SplFileObject::READ_CSV);
		$file->setCsvControl(',', '"', "\\");
	}

	public function accept() {
		$current = $this->getInnerIterator()->current();

		// Skip rows where the name is greater than or equal to 10 chars
		if (strlen($current[1]) >= 10) {
			return false;
		}

		return true;
	}
}