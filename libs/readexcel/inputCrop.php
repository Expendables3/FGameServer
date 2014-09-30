<?php
		require('config.php');		
		$index = 1;
		$nStart=3;
		$nEnd = 114;	
		$sheetIndex=0;
		$numrows = $data->sheets[$sheetIndex]['numRows'];
		/*
		 * duyet file
		 */
		echo"<pre>";
		for($i = $nStart; $i <= $nEnd ; $i++)
		{
			$key = 'NA';
			$me = 'NA';
			$type = 'NA';
			$id = 'NA';	
			$stt = $data->sheets[$sheetIndex]['cells'][$i][3];
			switch($stt)
			{
			case 1://an trom 1 loai qua nao do, id tam thoi de 1
				$key = 'HARVEST';
				$me = 0;
				$type = 1;
				$id = 1;
				break;
			case 2://cham soc vat nuoi cua minh
				$key = 'HAPPY';
				$me = 1;
				$type = 8;
				$id = 0;
				break;
			case 3://cham soc vat nuoi cua ban
				$key = 'HAPPY';
				$me = 0;
				$type = 8;
				$id = 0;
				break;
			case 4://tang fan bon, loai?, id tam thoi de 1
				$key = 'GIVE';
				$me = 0;
				$type = 3;
				$id = 1;	
				break;
			case 5://tang thuc an vat nuoi
				$key = 'GIVE';
				$me = 0;	
				$type = 3;
				$id = 4;	
				break;
			case 6://tang 1 loai qua nao do, id tam thoi de 1
				$key = 'GIVE';
				$me = 0;
				$type = 1;
				$id = 1;
				break;
			case 7://bon 1 loai fan nao do, id tam thoi de 1
				$key = 'FERTILIZER';
				$me = 1;
				$type = 3;
				$id = 1;
				break;
			case 8://diet sau tren nha hang xom
				$key = 'KILLPEST';
				$me = 0;
				$type = 10;
				$id = 1;
				break;
			case 9://diet sau tren nha minh
				$key = 'KILLPEST';
				$me = 1;
				$type = 10;
				$id = 1;
				break;
			case 10://tha sau o nha hang xom
				$key = 'ADDPEST';
				$me = 0;
				$type = 10;
				$id = 1;
				break;
			case 11://diet co tren nha hang xom
				$key = 'CLEARWEED';
				$me = 0;
				$type = 10;
				$id = 0;
				break;
			case 12://diet co tren nha minh
				$key = 'CLEARWEED';
				$me = 1;
				$type = 10;
				$id = 0;
				break;
			case 13://tha co tren nha hang xom
				$key = 'ADDWEED';
				$me = 0;
				$type = 10;
				$id = 0;
				break;
			case 14://viet thu
				$key = 'WRITE';
				$me = 1;
				$type = 13;
				$id = 1;
				break;
			}
		
echo " $index =>array
(		
		//rank
		'rank'=>'".$data->sheets[$sheetIndex]['cells'][$i][1]."',	
		//stt
		'stt'=>'".$data->sheets[$sheetIndex]['cells'][$i][3]."',	
		//name
		'name'=>'".$data->sheets[$sheetIndex]['cells'][$i][4]."',	
		//desc
		'desc'=>'".$data->sheets[$sheetIndex]['cells'][$i][5]."',	
		
		// nhiem vu
		'act'=>array(												
					'key'=>'".$key."',								
					'me'=>".$me.",	 							
					'type'=>".$type.",							
					'id'=>".$id.",								
					'num'=>".$data->sheets[$sheetIndex]['cells'][$i][6].",					
					),	
		// qua tang	
		'gift'=>array
		(
		//gold
				1=>array(											
					'id'=>1,										
					'type'=>6,										
					'num'=>".$data->sheets[$sheetIndex]['cells'][$i][7].",),	
		//exp
				2=>array(											
					'id'=>1,										
					'type'=>7,										
					'num'=>".$data->sheets[$sheetIndex]['cells'][$i][8].",),	
		//phan cap 1
				3=>array(											
					'id'=>1,										
					'type'=>3,										
					'num'=>".$data->sheets[$sheetIndex]['cells'][$i][9].",),	
		//phan cap 2
				4=>array(											
					'id'=>2,										
					'type'=>3,										
					'num'=>".$data->sheets[$sheetIndex]['cells'][$i][10].",),	
		//phan cap 3
				5=>array(											
					'id'=>3,										
					'type'=>3,										
					'num'=>".$data->sheets[$sheetIndex]['cells'][$i][11].",),	
		//thuc an gia suc
				6=>array(											
					'id'=>4,										
					'type'=>3,										
					'num'=>".$data->sheets[$sheetIndex]['cells'][$i][12].",),
		//hat giong
				7=>array(											
					'id'=>1,										
					'type'=>1,										
					'num'=>".$data->sheets[$sheetIndex]['cells'][$i][13].",),	
		),					
),";			  	
			echo "<br><br>";
			$index ++;				
		}
		echo"</pre>";
		
	
?>
