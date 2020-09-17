<?php
	//Number 1
	$arr =[1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
    
    //Number 2
	for($i = 0; $i < count($arr); $i++){
    	echo "$arr[$i]\n";
    }
    
    //Number 3
    echo "<br>\n";
    for($i = 0; $i < count($arr); $i++){
    	if($arr[$i]%2 == 0){
        	echo "$arr[$i]\n";
        }
    }
    
    //Number 4
    echo "<br>\n";
    echo "I used the modulus operator in order to find the remainder of dividing by 2.
    <br>\n Because if dividing a number by 2 results in a remainder of 0, it's even, if it's 1, then it's odd."
    
    /*
    Number 5
    I'll submit the screenshot.
    
    Number 6
    https://github.com/NJITneg5/IT202-009/blob/master/phpLoopHW.php
    */
	
?>
