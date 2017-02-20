<?php
	$list_text = 
	"Here's all the command you can use right now ;\n\n" .
	"++define  <Word> :\n" . 
	"Search the meaning of <Word> in Urban Dictionary that has the most likes\n\n" .
	"++var<1-10>  <word> : *NEW*\n" .
	"I'll get a specific variation of choosen word if they are available\n\n" .
	"++other  <Word> : \n" .
	"Same as ++define but i'll give you a random one without looking at their likes count\n\n" .
	"++random : *NEW*\n" .
	"I'll describe a completely random word for you. Unknown is fun sometimes\n\n" .
	"++list : \n" .
	"Listing all the commands you can give me";

	$tips_no1 = 'Type ++other <word> to get a random variation' ;
	$tips_no2 = 'Type ++define <word> to get the most voted variation' ;
	$tips_no3 = 'Type ++list to see all the command you can use' ;
	$tips_no4 = 'Type ++random to get a completely random word that i will describe' ;
	$tips_list = array($tips_no1, $tips_no2, $tips_no3, $tips_no4);
?>