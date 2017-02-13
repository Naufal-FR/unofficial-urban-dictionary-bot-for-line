<?php

	require_once('./LINEBotTiny.php');
	require_once('./channelKey.php');

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

	// Returned Text Format
	function text_array_result ($term_array, $chosen_array, $variation_number, $command_type){
		$word = "> Word <\n" . $term_array['list'][$chosen_array]['word'];
		$definition = "> Definition <\n" . $term_array['list'][$chosen_array]['definition'];
		$example = "> Example <\n" . $term_array['list'][$chosen_array]['example'];

		if ($command_type == '++define') {
			$suggested_variation = 'Type ++other <definition> to get a random variation' ;
		} elseif ($command_type == '++other') {
			$suggested_variation = 'Type ++define <definition> to get the most voted variation' ;
		}

		$variation = 'There are ' . $variation_number . ' variation for this definition.' . "\n" . $suggested_variation ;
		$term_result_array = array ($word, $definition, $example, $variation);
		return $term_result_array ;
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
		                		$term = substr($message['text'], 9);
							} elseif ($exploded_Message[0] == "++other") {
		                		$term = substr($message['text'], 8);
							} 

							if (empty($term)) {
								// Empty to compensate for pre-built LINE responses
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
									}
									$term_result_array = text_array_result($term_array, $lookup_value, $variation, $exploded_Message[0]);	
									$text_response = implode("\n\n",$term_result_array) . "";
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