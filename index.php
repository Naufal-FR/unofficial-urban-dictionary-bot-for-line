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

	function define_term ($term){
		$term_url = 'http://api.urbandictionary.com/v0/define?term=' . str_replace(' ', '+', $term);
		$term_json = file_get_contents($term_url);
		$term_array = json_decode($term_json, true);
		return $term_array ;
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

		                		$term_array = define_term($term);
								$no_result = count($term_array['list']);		

								$word = 'Word : ' . $term_array['list'][0]['word'];
								$definition = 'Definition : ' . $term_array['list'][0]['definition'];
								$example = 'Example : ' . $term_array['list'][0]['example'];
								$variation = 'There are ' . $no_result . ' variation for this definition.' . "\n" .'Type ++other <definition> to get a random variation' ;
								$term_result_array = array ($word, $definition, $example, $variation);

								$term_return = implode("\n\n",$term_result_array) . "";
	                		} 

	                		elseif ($exploded_Message[0] == "++other"){
		                		$term = substr($message['text'], 8);

		                		$term_array = define_term($term);
								$no_result = count($term_array['list']);
								
								$lookup_value = rand(0,$no_result-1);

								$word = 'Word : ' . $term_array['list'][$lookup_value]['word'];
								$definition = 'Definition : ' . $term_array['list'][$lookup_value]['definition'];
								$example = 'Example : ' . $term_array['list'][$lookup_value]['example'];
								$variation = 'There are ' . $no_result . ' variation for this definition.' . "\n" . 'Type ++define <definition> to get the most voted variation' ;
								$term_result_array = array ($word, $definition, $example, $variation);

								$term_return = implode("\n\n",$term_result_array) . "";
	                		}

						} catch (Exception $e) {
	                		$term_return = "Sorry, No Definition Found or Error Occured";	
						}

	                    $client->replyMessage(array(
	                        'replyToken' => $event['replyToken'],
	                        'messages' => array(
	                            array(
	                                'type' => 'text',
	                                'text' => $term_return
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