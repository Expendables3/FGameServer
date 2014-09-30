<?php
		define('CMS_ADMIN', true);
		require('./Excel/reader.php');
		/**
		 * Phần config cho tưng file
		 * @var unknown_type
		 */
		$filename = "ZingFish_Data.xls";
		/*
		 * Đọc file
		 */

		$data = new Spreadsheet_Excel_Reader();
		$data->setUTFEncoder('iconv');
		$data->setOutputEncoding('utf-8');
		$data->read($filename);

?>