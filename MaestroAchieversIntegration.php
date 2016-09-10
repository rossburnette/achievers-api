<?php

include "CallAchieversAPI.php";

function getCompletedLearners($courseCodes){
/**
 * Calls the 'transcript' endpoint of the Maestro Data Extraction API to retrieve a list of users and their completed courses; then
 * parses list for those users who have completed a designated course before its due date (for the last 30 days only)
 * @param string $courseCodes Courses to search and return learners for
 * @return array|null Maestro usernames and corresponding course names if successful; null otherwise
*/

	try {
		$ch = curl_init();
		$urlPost = "URL";  // Replace with URL of your SumTotal Maestro instance
		$urlGet = "";
		$username = "USERNAME";  // Replace with your SumTotal Maestro username
		$password = "PASSWORD";  // Replace with your SumTotal Maestro password

		// Initial POST request to create a file
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $urlPost);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "header_required=0&date_type=completion");
		$resultPost = curl_exec($ch);
	
		if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 201) {
			$locationStart = strpos($resultPost, "Location:") + 10;
			$locationEnd = strpos($resultPost, ".csv", $locationStart) + 3;
			$urlGet = substr($resultPost, $locationStart, $locationEnd-$locationStart+1);

			// Subsequent GET request to retrieve file
			curl_setopt($ch, CURLOPT_HTTPGET, 1);
			curl_setopt($ch, CURLOPT_URL, $urlGet);
			curl_setopt($ch, CURLOPT_HEADER, false);
			$resultGet = curl_exec($ch);
			
			if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
				$transcript = array_map ( function ($_) { return explode (',', $_); }, explode("\n", $resultGet) );
				$learners = array();
				$index = 0;
				$today = date_create();
				$numFileColumns = 28;  // 'transcript' endpoint returns CSV file containing 28 columns

				foreach ($transcript as $key => $value) {
					if (count($value) < $numFileColumns || empty($value[1]) || !in_array(trim($value[5],'"'), $courseCodes)) {
						continue;
					} else {
						$completionDate = date_create_from_format('m/d/Y', trim($value[18],'"'));
						$dueDate = date_create_from_format('Y-m-d', trim(substr($value[26], 0, strpos($value[26], "T")), '"'));

						if ($completionDate < $dueDate && date_diff($completionDate, $today)->format('%a') <= 30) {
							$learners[$index]["username"] = trim($value[1],'"');
							$learners[$index]["course"] = $value[6];
							$index++;
						}
					}
				}

				return $learners;
			}
		}

		echo "Error : In getCompletedLearners() : HTTP Code " . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
		return null;

	} catch (Exeception $e) {
		echo "Exception: " . $e->getMessage() . "\n";
	} finally {
		curl_close($ch);
	}
}

$courseCodes = array("Health-1", "NewHire-1", "PM-1");  // Course codes in Maestro
$pointValue = 0;  // Point value of the recognition (0 for social; 500, 1000, 1500, etc. for points-based)
$recoCriterion = "Great Progress";
$learners = getCompletedLearners($courseCodes);
$nominatorID = getAchieversUserID("talent.development");

// Post a recognition for each user
if (!empty($learners) && !empty($nominatorID)) {
	foreach ($learners as $value) {
		if (strpos($value["username"], '@') !== false) {
			$value["username"] = substr($value["username"], 0, strpos($value["username"], '@'));
		}
		
		$nomineeID = getAchieversUserID($value["username"]);
		
		if ($nomineeID && $nominatorID != $nomineeID) {
			$criterionID = getAchieversCriteria($nominatorID, $nomineeID, $pointValue, $recoCriterion);
			
			if ($criterionID) {
				postAchieversRecognition($nominatorID, $nomineeID, $criterionID, $pointValue, "Successfully completed the " . $value["course"] . " course in Maestro!");
			}
		}
	}
}

?>
