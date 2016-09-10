<?php

function callAchieversAPI($endpoint, $httpMethod, $parameters, $postData = NULL) {
/**
 * Calls the specified endpoint of the Achievers Open API
 * @param string $endpoint Endpoint to call
 * @param string $httpMethod HTTP method to use (e.g., "GET", "POST")
 * @param string $parameters Request parameters to include 
 * @param array $postData - Data to post (if applicable)
 * @return array|null Decoded API response if successful (HTTP status code 200/201); null otherwise
*/

	$ch = curl_init();
	$url = "https://PROGRAM.achievers.com/api/v3/" . $endpoint . $parameters;  // Replace with your Achievers program URL
	$username = "USERNAME"; // Replace with your Achievers partner key
	$password = "PASSWORD"; // Replace with your Achievers program key
	$httpHeaders = array (
		"Accept: application/json",
		"Content-Type: application/json"
	);
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeaders);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $httpMethod);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
	
	try {
		$resultArray = json_decode(curl_exec($ch),true);
		
		if ($resultArray["status"]["http_code"] == 200 || $resultArray["status"]["http_code"] == 201) {
			return $resultArray;
		} else {
			echo "In callAchieversAPI() : " . "HTTP " . $resultArray["status"]["http_code"] . " : " . $resultArray["content"]["message"] . "\n";
			return null;
		}	
	} catch (Exeception $e) {
		echo "Exception: " . $e->getMessage() . "\n";
	} finally {
		curl_close($ch);
	}
}

function getAchieversUserID($searchString) {
/**
 * Calls the 'user' endpoint of the Achievers Open API to search for a member
 * @param string $searchString User search criteria
 * @return string|null User PK of the member if exactly one member is found; null otherwise
*/

	$resultArray = callAchieversAPI("user", "GET", "?q='" . $searchString . "'");
	
	if(isset($resultArray["content"]["count"])){
		if($resultArray["content"]["count"] == 1) {
			return $resultArray["content"]["items"][0]["id"];
		} else if ($resultArray["content"]["count"] == 0) {
			echo "Error : In getAchieversUserID() : No user found ('" . $searchString . "')\n";
		} else {
			echo "Error : In getAchieversUserID() : Multiple users found ('"  . $searchString . "')\n";
		}
	}
		
	return null;
}

function getAchieversCriteria($nominatorID, $nomineeID, $pointValue, $criterion) {
/**
 * Calls the 'criteriaPermission' endpoint of the Achievers Open API to determine if a nominator is eligible
 * to send a recognition to a nominee
 * @param string $nominatorID User PK of the nominator
 * @param string $nomineeID User PK of the nominee
 * @param string $pointValue - Point value of the recognition (0 for social)
 * @param string $criterion - Name of the recognition criterion
 * @return string|null ID of the recognition criterion if eligible; null otherwise
*/

	$resultArray = callAchieversAPI("criteriaPermission", "GET", "?from=" . $nominatorID . "&to=" . $nomineeID);
	
	if(isset($resultArray["content"]["items"])) {
		// Parse results to find criterion
		foreach ($resultArray["content"]["items"] as $value) {
			if ($value["suggestedAmount"] == $pointValue && strcasecmp($value["criterionName"], $criterion) == 0) {
				return $value["id"];
			}
		}
		
		echo "In getAchieversCriteria() : " . $criterion . " : Criterion and/or point value not found\n";
	} 
	
	return null;
}

function postAchieversRecognition($nominatorID, $nomineeID, $criterionID, $pointValue, $recognitionText) { 
/** 
 * Calls the 'recognition' endpoint of the Achievers Open API to post a recognition
 * @param string $nominatorID User PK of the nominator
 * @param string $nomineeID User PK of the nominee
 * @param string $criterionID ID of the recognition criterion
 * @param string $pointValue Point value of the recognition
 * @param string $recognitionText Details of (reason for) the recognition
*/

	$postData = array (
		"from" => $nominatorID,
		"to" => $nomineeID,
		"criterionId" => $criterionID,
		"recognitionText" => $recognitionText,
		"pointValue" => $pointValue
	);
	
	$resultArray = callAchieversAPI("recognition", "POST", "", $postData);
}

?>