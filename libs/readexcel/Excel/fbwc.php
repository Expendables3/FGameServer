<?php
		include("common.php");
		
		function get($ClubId)
		{	
			$key = "_FB_SYS_{$ClubId}_FOOTBALLERS";		
			return getXCache($key);
		}
		function set($ClubId,$data)
		{
			$key = "_FB_SYS_{$ClubId}_FOOTBALLERS";		
			return setXCache($key,$data);
		}
		function getFootballerById($fbId)
		{	
			$key = "_FB_SYS_{$fbId}_ONEFOOTBALLER";				
			return getXCache($key);
		}
		function setFootballerById($fbId,$data)
		{
			$key = "_FB_SYS_{$fbId}_ONEFOOTBALLER";		
			return setXCache($key,$data);
		}
		
		for($cld=1;$cld<33;$cld++)
		{
			set($cld,array());
		}
		
		//tuyentq
		$filename = "fbwctuyentq.csv";
		$row = 1;
		$FblId=1;
		$file = fopen($filename, "r");
		while (($data = fgetcsv($file, 8000, ",")) !== FALSE) {
			$num = count($data);
			$row++;
	
			$j=0;
			
			for ($c=0; $c < $num; $c++) 
			{
				if($data[$j])
				{

						$FblId++;
						$Name= mb_convert_encoding(trim($data[$j+3]), 'Shift_JIS',"UTF-8");
						$FullName= $data[$j+2];
						$ClubId= $data[$j+4];
						$Avatar= "";
						$PosId= 0;
						$Model3D="standard.DAE";
						if($data[$j] == "GK")
						{
							$PosId= 1;
							$Model3D= "standardgk.DAE";
						}
						if($data[$j] == "DF")
						{
							$PosId= 2;
							$Model3D= "standard.DAE";
						}	
						if($data[$j]== "MF")
						{
							$PosId= 3;
							$Model3D= "standard.DAE";
						}
						if($data[$j]== "FW")
						{
							$PosId= 4;
							$Model3D= "standard.DAE";
						}			
						$Age= 24;
						$Cost=$data[$j+8] * $data[$j+9];
						$BuyPercen= 0;
						$Point= 0;
						$StartCost=$data[$j+8] * $data[$j+9];
						$HStyleId= 1;
						$CSkinId= 1;			
						$HColorId= 1;
						$Default= 0;
						$AuctionNumber= $data[$j+1];;
					
						$datafb=array();
						$datafb['FblId'] =$FblId;
						$datafb['Name']=$Name;
						$datafb['FullName']=$FullName;
						$datafb['ClubId'] =$ClubId;
						$datafb['Avatar']=$Avatar;
						$datafb['PosId'] =$PosId;
						$datafb['Age'] =$Age;
						$datafb['Cost']=$Cost;
						$datafb['BuyPercen']=$BuyPercen;
						$datafb['Point'] =$Point;
						$datafb['StartCost']=$StartCost;
						$datafb['HStyleId'] =$HStyleId;
						$datafb['CSkinId'] =$CSkinId;
						$datafb['Model3D'] =$Model3D;
						$datafb['HColorId']=$HColorId;
						$datafb['Default'] =$Default;
						$datafb['AuctionNumber']=$AuctionNumber;
						  
						  $result=get($ClubId);	
						  if(count($result)==0)
						  {
							$result=array();
						  }	  
						  //echo $FblId;
						  $result[$FblId]=$datafb;
						  //set($ClubId,$result);
					  $datafb['team_ids']="";					 
					 //setFootballerById($FblId,$datafb);					
				} 	
				$j = $j + 11;  //so truong tao
			}
		
		}
		 print_r(get(20));	  

?>
