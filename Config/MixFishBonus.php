<?php
return array (
'LevelSkill' => array(
      'Mastery' => array (
            'Level0'=> array(   // level ca dem lai == level user
                'Over0'=> 10,   
                'Over1'=> 20,   // ca vuot 1 cap so voi level cua ca dem lai
                'Over2'=> 40,  // ca vuot 2 cap so voi level cua ca dem lai
              ),
            'Level1'=> array(   // level ca dem lai > 1 level so voi level user
                'Over0'=> 12,   
                'Over1'=> 24,   // ca vuot 1 cap so voi level cua ca dem lai
                'Over2'=> 48,  // ca vuot 2 cap so voi level cua ca dem lai
              ),
            'Level2'=> array(   
                'Over0'=> 14,   
                'Over1'=> 28,   
                'Over2'=> 56,  
              ),
             'Level3'=> array(   
                'Over0'=> 16,   
                'Over1'=> 32,   
                'Over2'=> 64,  
              ),
             'Level4'=> array(   
                'Over0'=> 18,   
                'Over1'=> 36,   
                'Over2'=> 72,  
              ),
             'Level5'=> array(   
                'Over0'=> 20,   
                'Over1'=> 40,   
                'Over2'=> 80,  
              ),     

        ),
      'Exp' => array(
          'Over0'=> 5,   
          'Over1'=> 15,   // ca vuot 1 cap so voi level cua ca dem lai
          'Over2'=> 15,  // ca vuot 2 cap so voi level cua ca dem lai
      ),
  ),
'SpecialSkill' =>array(
      'Mastery' => array (
            'Level0'=> array(   // level ca dem lai == level user
                'FishType0'=> 10,   // ra ca binh thuong
                'FishType1'=> 50,  // ra ca dac biet 
                'FishType2'=> 10,     // ra ca quy
              ),
            'Level1'=> array(   // level ca dem lai > 1 level so voi level user
                'FishType0'=> 10,   // ra ca binh thuong
                'FishType1'=> 50,  // ra ca dac biet 
                'FishType2'=> 10,     // ra ca quy
              ),
            'Level2'=> array(   
                'FishType0'=> 10,   
                'FishType1'=> 50,  
                'FishType2'=> 10,    
              ),
            'Level3'=> array(   
                'FishType0'=> 10,   
                'FishType1'=> 50,  
                'FishType2'=> 10,        
              ),
            'Level4'=> array(   
                'FishType0'=> 10,   
                'FishType1'=> 50,  
                'FishType2'=> 10,    
              ),
            'Level5'=> array(   
                'FishType0'=> 10,   
                'FishType1'=> 50,  
                'FishType2'=> 10,        
              ),

        ),
      'Exp' => array(
          'FishType0'=> 5,   
          'FishType1'=> 15,  
          'FishType2'=> 5,    
      ),
  ),
'RareSkill' =>array(
      'Mastery' => array (
            'Level0'=> array(   // level ca dem lai == level user
                'FishType0'=> 10,   // ra ca binh thuong
                'FishType1'=> 10,  // ra ca dac biet 
                'FishType2'=> 100,     // ra ca quy
              ),
            'Level1'=> array(   // level ca dem lai > 1 level so voi level user
                'FishType0'=> 10,   // ra ca binh thuong
                'FishType1'=> 10,  // ra ca dac biet 
                'FishType2'=> 100,     // ra ca quy
              ),
            'Level2'=> array(   
                'FishType0'=> 10,   
                'FishType1'=> 10,  
                'FishType2'=> 100,    
              ),
            'Level3'=> array(   
                'FishType0'=> 10,   
                'FishType1'=> 10,  
                'FishType2'=> 100,       
              ),
            'Level4'=> array(   
                 'FishType0'=> 10,   
                'FishType1'=> 10,  
                'FishType2'=> 100,        
              ),
            'Level5'=> array(   
                 'FishType0'=> 10,   
                'FishType1'=> 10,  
                'FishType2'=> 100,        
              ),

        ),
      'Exp' => array(
          'FishType0'=> 5,   
          'FishType1'=> 5,  
          'FishType2'=> 15,    
      ),
  ),
'MoneySkill' =>array(
    'Mastery' => array (
        'Level0'=> 10,   
        'Level1'=> 10,   
        'Level2'=> 10,  
        'Level3'=> 10,
        'Level4'=> 10,
        'Level5'=> 10,
      ), 
    'Exp' => 0, 
  ),
); 
?>
