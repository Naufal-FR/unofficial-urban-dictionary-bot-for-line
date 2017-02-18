<?php
	$list_text = 
	"Here's all the command you can use right now ;\n\n" .
	"++define_<Word> :\n" . 
	"Search the meaning of <Word> in Urban Dictionary that has the most likes\n\n" .
	"++var<1-10>_<word> : *NEW*\n" .
	"I'll get a specific variation of choosen word if they are available\n\n" .
	"++other_<Word> : \n" .
	"Same as ++define but i'll give you a random one without looking at their likes count\n\n" .
	"++random :\n" .
	"I'll describe a completely random word for you. Unknown is fun sometimes\n\n" .
	"++list : \n" .
	"Listing all the commands you can give me" . PHP_EOL . PHP_EOL
	"* Replace underscore ( _ ) with space. For example if searching for \"Test\", \"++var3_Test\" becomes \"++var3 Test\"";
?>