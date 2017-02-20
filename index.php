<?php

	require_once( __DIR__ . '/src/LINEBotTiny.php');
	require_once( __DIR__ . '/func/func_main.php');
	require_once( __DIR__ . '/conf/channel_key.php');
	require_once( __DIR__ . "/text/text_main.php");

	set_error_handler('exceptions_error_handler');
	
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
		                			$exec_command = '++define' ;
		                		}
							} elseif ($exploded_Message[0] == "++other") {
		                		$term = substr($message['text'], 8);
		                		$exec_command = '++other' ;
							} elseif ($exploded_Message[0] == "++random") {
								$text_response = random_term();
								$exec_command = '++random' ;
							} elseif (substr_count($exploded_Message[0], "++var", 0, strlen($exploded_Message[0])) == 1) {
								$choosen_variation = substr($exploded_Message[0], 5, strlen($exploded_Message[0])) - 1 ;
								if ($choosen_variation > 9) {
									$text_response = "There's no more variation above 10" ; 
								} else {
									$term = substr($message['text'], strlen($exploded_Message[0])) ;
									$exploded_Message[0] = "++var" ;
									$exec_command = '++var' ;
								}
							} 

							if ($exploded_Message[0] == "++list") {
								$text_response = $list_text ;
								$exec_command = '++list' ;
							}

							if ($exploded_Message[0] == "++debug") {
								// $result = is_dir("./conf") ;
								$result = file_exists("./func/func_main.php") ;
								// $text_response = __DIR__ . "/text/text_main.php" ;
								$text_response = $result ;
								$exec_command = "++debug" ;
							}
							
							create_log_data($event['source'], $exec_command);

							if (empty($term)) {
								// Empty to compensate for list command
							} else {
		                		$term_array = exact_term($term);
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
	                		$text_response = "Sorry, An Error Just Occured" . PHP_EOL . $e->getMessage();	
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