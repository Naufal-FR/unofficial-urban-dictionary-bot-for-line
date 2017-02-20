<?php

	// Error Handling Mainly In Case No Term Found
	function exceptions_error_handler($severity, $message, $filename, $lineno) {
	  if (error_reporting() == 0) {
	    return;
	  }
	  if (error_reporting() & $severity) {
	    throw new ErrorException($message, 0, $severity, $filename, $lineno);
	  }
	}
	set_error_handler('exceptions_error_handler');

	// Finding The Definition From Urban Dictionary
	function define_term ($term){
		$term_url = 'http://api.urbandictionary.com/v0/define?term=' . str_replace(' ', '+', $term);
		$term_json = file_get_contents($term_url);
		$term_array = json_decode($term_json, true);
		return $term_array ;
	}

	function random_term_picker (){
		$term_url = 'http://api.urbandictionary.com/v0/random';
		$term_json = file_get_contents($term_url);
		$term_array = json_decode($term_json, true);
		$random_array_number = rand(0,count($term_array['list'])-1);
		$term_return = format_return_text($term_array, $random_array_number, count($term_array['list']), "++random" );
		return $term_return ;	
	}

	// Returned Text Format
	function format_return_text ($term_array, $chosen_array, $variation_total, $command_type, $tips_list){
		if (count($term_array['list']) - 1 < $chosen_array) {
			return "There's no variation " . ($chosen_array + 1) . ". Try other number." ;
		}
		$command_format = "Command Type : " . $command_type ;
		$word_format = "> Word <\n" . $term_array['list'][$chosen_array]['word'];
		$definition_format = "> Definition <\n" . $term_array['list'][$chosen_array]['definition'];
		$example_format = "> Example <\n" . $term_array['list'][$chosen_array]['example'];
		
		$variation_format = 'This is variation ' . ($chosen_array + 1) .  ' of ' . $variation_total ;

		$tips_choose = rand(0,count($tips_list)-1);
		$tips_format = 'Tips : ' . $tips_list[$tips_choose] ;

		$term_result_array = array ($command_format, $word_format, $definition_format, $example_format, $variation_format, $tips_format);
		$text_return = implode("\n\n",$term_result_array) . "";
		
		return $text_return ;
	}

	function create_log_data ($source, $command) {
		
		if (!isset($source['userId'])) {
			$choosenID = 'groupId' ;
			if (!isset($source['groupId'])) {
				$choosenID = 'roomId' ;
			} 
		} else {
			$choosenID = 'userId' ;
		}

		$log = 	
			date('d-m-Y h:i:s e') . PHP_EOL . 	                    		
    		"User ID: " . $source[$choosenID] . PHP_EOL . 
    		"Command: " . $command . PHP_EOL . 
    		"-----------------------------" . PHP_EOL; 

    	file_put_contents('./log.txt', $log, FILE_APPEND | LOCK_EX);
	}
	
?>