<?php

	require_once('./LINEBotTiny.php');
	require_once('./channelKey.php');
	require_once('./list_text.php');

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
	function format_return_text ($term_array, $chosen_array, $variation_total, $command_type){
		$command_format = "Command Type : " . $command_type ;
		$word_format = "> Word <\n" . $term_array['list'][$chosen_array]['word'];
		$definition_format = "> Definition <\n" . $term_array['list'][$chosen_array]['definition'];
		$example_format = "> Example <\n" . $term_array['list'][$chosen_array]['example'];
		
		$variation_format = 'This is variation ' . ($chosen_array + 1) .  ' of ' . $variation_total ;

		$tips_no1 = 'Type ++other <word> to get a random variation' ;
		$tips_no2 = 'Type ++define <word> to get the most voted variation' ;
		$tips_no3 = 'Type ++list to see all the command you can use' ;
		$tips_no4 = 'Type ++random to get a completely random word that i will describe' ;
		$tips_list = array($tips_no1, $tips_no2, $tips_no3, $tips_no4);
		$tips_choose = rand(0,count($tips_list)-1);
		$tips_format = 'Tips : ' . $tips_list[$tips_choose] ;

		$term_result_array = array ($command_format, $word_format, $definition_format, $example_format, $variation_format, $tips_format);
		$text_return = implode("\n\n",$term_result_array) . "";
		
		return $text_return ;
	}

	
	$client = new LINEBotTiny($channelAccessToken, $channelSecret);

	foreach ($client->parseEvents() as $event) {

	    switch ($event['type']) {

	        case 'message':
	            $message = $event['message'];

	            switch ($message['type']) {
	                case 'text':

	                	// Explode The Message So We Can Get The First Words
	               		$exploded_Message = explode(" ", $message['text']);
						
						try {

							if ($exploded_Message[0] == "++define") {
		                		if (count($exploded_Message) == 1) {
		                			$text_response = "No definition inputted" ;
		                		} else {
		                			$term = substr($message['text'], 9);
		                		}
							} elseif ($exploded_Message[0] == "++other") {
		                		$term = substr($message['text'], 8);
							} elseif ($exploded_Message[0] == "++random") {
								$text_response = random_term_picker();
							} elseif (substr_count($exploded_Message[0], "++var", 0, 5) == 1) {
								$choosen_variation = substr($exploded_Message, 5, strlen($exploded_Message));
								$exploded_Message[0] = "++var" ;
							} 

							if ($exploded_Message[0] == "++list") {
								$text_response = $list_text ;
							}

							if (empty($term)) {
								// Empty to compensate for list command
							} else {
		                		$term_array = define_term($term);
		                		if ($term_array['result_type'] == "no_results") {
		                			$text_response = "I'm sorry, no definition found" ;
		                		} elseif ($term_array['result_type'] == "exact") {
			                		$variation = count($term_array['list']);
									$lookup_value = 0 ;
									if ($exploded_Message[0] == "++other") {
										$lookup_value = rand(0,$variation-1);
										if ($lookup_value == 0){$lookup_value = rand(0,$variation-1);}
									} elseif ($exploded_Message[0] == "++var") {
										$lookup_value = $choosen_variation ;
									}
									$text_response = format_return_text($term_array, $lookup_value, $variation, $exploded_Message[0]);	
		                		}	
							}

						} catch (Exception $e) {
	                		$text_response = "Sorry, An Error Just Occured";	
						}

	                    $client->replyMessage(array(
	                        'replyToken' => $event['replyToken'],
	                        'messages' => array(
	                            array(
	                                'type' => 'text',
	                                'text' => $text_response
	                            )
	                        )
	                    ));
	                    
	                    $log = 	date("F j, Y, g:i a") . PHP_EOL . 	                    		
	                    		"User ID: " . $user_id . PHP_EOL . 
	                    		"Command: " . $exploded_Message[0] . PHP_EOL . 
	                    		"Word: " . $term_array['list'][$lookup_value]['word'] . PHP_EOL .
	                    		"-----------------------------" . PHP_EOL;  

	                    $dir = "./log" ;
	                    if (is_dir($dir) === false) {
	                    	mkdir($dir) ;
	                    }
	                    $log_file = fopen($dir . '/' . 'log.txt', "a") ;
	                    fwrite($log_file, $log);
	                    fclose($log_file);

	                    break;
	            
	                default:
	                    error_log("Unsupporeted message type: " . $message['type']);
	                    break;
	            }
	            break;
	
	        default:
	            error_log("Unsupporeted event type: " . $event['type']);
	            break;
	    }
	};
	
?>