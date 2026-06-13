<?php
/*+***********************************************************************************
 * Reader cho file Excel (.xls / .xlsx) — dùng PHPExcel để đọc, tái dùng luồng import của vtiger.
 *************************************************************************************/

class Import_XLSReader_Reader extends Import_FileReader_Reader {

	/** Nạp sheet đầu tiên của file Excel đã upload. Trả về PHPExcel_Worksheet hoặc null. */
	private function loadSheet() {
		require_once 'libraries/PHPExcel/PHPExcel/IOFactory.php';
		$filePath = $this->getFilePath();
		if (!file_exists($filePath)) {
			$this->status = 'failed';
			$this->errorMessage = 'ERR_FILE_DOESNT_EXIST';
			return null;
		}
		try {
			// nhận dạng theo nội dung (file upload không có đuôi) → đọc được cả .xls lẫn .xlsx
			$objReader = PHPExcel_IOFactory::createReaderForFile($filePath);
			$objReader->setReadDataOnly(true);
			$workbook = $objReader->load($filePath);
		} catch (Exception $e) {
			$this->status = 'failed';
			$this->errorMessage = $e->getMessage();
			return null;
		}
		// dùng getSheet(0) thay getActiveSheet(): với setReadDataOnly(true) + Excel2007,
		// getActiveSheet() trả null (lỗi đã biết của PHPExcel 1.7.7)
		return $workbook->getSheet(0);
	}

	private function getRows($sheet) {
		// nullValue='', calculateFormulas=true, formatData=true (lấy giá trị hiển thị), returnCellRef=false (key số)
		return $sheet->toArray('', true, true, false);
	}

	public function arrayCombine($key, $value) {
		$combine = array();
		$dup = array();
		for ($i = 0; $i < count($key); $i++) {
			if (array_key_exists($key[$i], $combine)) {
				if (empty($dup[$key[$i]])) $dup[$key[$i]] = 1;
				$key[$i] = $key[$i]."(".++$dup[$key[$i]].")";
			}
			$combine[$key[$i]] = $value[$i];
		}
		return $combine;
	}

	public function getFirstRowData($hasHeader = true) {
		$sheet = $this->loadSheet();
		if (!$sheet) return null;
		$rows = $this->getRows($sheet);

		$headers = array();
		$firstRowData = array();
		if ($hasHeader) {
			$rawHeaders = isset($rows[0]) ? $rows[0] : array();
			$rawFirst   = isset($rows[1]) ? $rows[1] : array();
		} else {
			$rawHeaders = array();
			$rawFirst   = isset($rows[0]) ? $rows[0] : array();
		}
		foreach ($rawHeaders as $k => $v) $headers[$k] = trim(strip_tags(decode_html((string)$v)));
		foreach ($rawFirst as $k => $v)   $firstRowData[$k] = trim(strip_tags(decode_html((string)$v)));

		if ($hasHeader) {
			$noOfHeaders = count($headers);
			$noOfFirstRowData = count($firstRowData);
			if ($noOfHeaders > $noOfFirstRowData) {
				$firstRowData = array_merge($firstRowData, array_fill($noOfFirstRowData, $noOfHeaders - $noOfFirstRowData, ''));
			} elseif ($noOfHeaders < $noOfFirstRowData) {
				$firstRowData = array_slice($firstRowData, 0, $noOfHeaders, true);
			}
			$rowData = $this->arrayCombine($headers, $firstRowData);
		} else {
			$rowData = $firstRowData;
		}
		return $rowData;
	}

	public function read() {
		$sheet = $this->loadSheet();
		if (!$sheet) return;
		$status = $this->createTable();
		if (!$status) return;

		$fieldMapping = $this->request->get('field_mapping');
		$hasHeader = $this->hasHeader();
		$rows = $this->getRows($sheet);

		$i = -1;
		foreach ($rows as $data) {
			$i++;
			if ($hasHeader && $i == 0) continue;
			$mappedData = array();
			$allValuesEmpty = true;
			foreach ($fieldMapping as $fieldName => $index) {
				$fieldValue = isset($data[$index]) ? (string)$data[$index] : '';
				$mappedData[$fieldName] = $fieldValue;
				if ($fieldValue !== '') $allValuesEmpty = false;
			}
			if ($allValuesEmpty) continue;
			$this->addRecordToDB(array_keys($mappedData), array_values($mappedData));
		}
	}
}
