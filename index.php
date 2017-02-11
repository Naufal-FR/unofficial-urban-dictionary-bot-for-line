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

								$term_url = 'http://api.urbandictionary.com/v0/define?term=' . str_replace(' ', '+', $term);

								$term_json = file_get_contents($term_url);
								$term_array = json_decode($term_json, true);

								$word = 'Word : ' . $term_array['list'][0]['word'];
								$definition = 'Definition : ' . $term_array['list'][0]['definition'];
								$example = 'Example : ' . $term_array['list'][0]['example'];

								$term_result_array = array ($word, $definition, $example);

								$term_return = implode("\n\n",$term_result_array) . "";
	                		}
						} catch (Exception $e) {
	                		$term_return = "Sorry,No Definition Found";	
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