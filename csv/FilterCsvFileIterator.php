<?php
class FilterCsvFileIterator extends FilterIterator {

	public function accept() {
		$current = $this->getInnerIterator()->current();

		// Skip rows where the name is greater than or equal to 10 chars
		if (strlen($current['name']) >= 10) {
			return false;
		}

		return true;
	}
}