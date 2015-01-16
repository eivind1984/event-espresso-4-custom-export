<?php
/*
Template Name: Event Espresso Attendee Export
*/

	// Check permissions
	if (!current_user_can('delete_posts')) {
		echo "Invalid permissions";
		exit();
	}

	// Start output
	$fh = fopen('php://output', 'w');
	ob_start();

	// Get the event ID
	if (isset($_REQUEST['event_id'])) $event_id = esc_attr($_REQUEST['event_id']);

	// Exit if no event ID present
	if (!$event_id) {
		echo "No event ID";
		exit();
	}

	// Set delimiter
	$delim = ";";

	// Set newline
	$newline = "\n";

	// Write header - adjust to your needs
	$headers = array (
			'Eventnr',
			'Familienr',
			'Deltagernr',
			'Status',
			'Reg-tidspunkt',
			'Fornavn',
			'Etternavn',
			'Adresse',
			'Postnr',
			'Poststed',
			'Telefon',
			'Epost',
			'Deltageravgift',
			'Betalt',
			'Deltagelsestype',
			'Kommentar',
			'Foto',
	);

	foreach ($headers as $header) {
		echo $header . $delim;
		}
	echo $newline;

	// Fetch attendees for that event
	$attendees = $wpdb->get_results( $wpdb->prepare (
				"SELECT * FROM " . $wpdb->prefix . "esp_registration
					LEFT JOIN " . $wpdb->prefix . "esp_attendee_meta ON " . $wpdb->prefix . "esp_registration.ATT_ID = " . $wpdb->prefix . "esp_attendee_meta.ATT_ID
					LEFT JOIN " . $wpdb->prefix . "esp_status ON " . $wpdb->prefix . "esp_registration.STS_ID = " . $wpdb->prefix . "esp_status.STS_ID
					LEFT JOIN " . $wpdb->prefix . "esp_transaction ON " . $wpdb->prefix . "esp_transaction.TXN_ID = " . $wpdb->prefix . "esp_registration.TXN_ID
					LEFT JOIN " . $wpdb->prefix . "esp_ticket ON " . $wpdb->prefix . "esp_ticket.TKT_ID = " . $wpdb->prefix . "esp_registration.TKT_ID
				WHERE
					" . $wpdb->prefix . "esp_registration.EVT_ID = $event_id
				AND
					" . $wpdb->prefix . "esp_registration.REG_deleted = 0"
			) );

	$previous_group_id = 0;
	$group_count = 0;
	$attendee_count = 0;

	if ($attendees) {
		foreach ($attendees as $attendee) {

			$attendee_count = $attendee_count +1 ;
			$attendee->attendee_count = $attendee_count;

			// check if we have a new group, if so increase the count
			if ($previous_group_id != $attendee->TXN_ID) {
				$group_count = $group_count + 1;
			}

			$attendee->group_count = $group_count;
			$previous_group_id = $attendee->TXN_ID;

				// Print attendee
				echo_attendee($attendee); // pass the entire array to the function

		} // foreach attendee

	} // if attendees


// Output CSV TO USER
$filename = 'attendee_list-' . $event_id . "-"  . date('Ymd') .'_' . date('His');

// Output CSV-specific headers
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$filename.csv\";" );
header("Content-Transfer-Encoding: binary");

exit($string);

function echo_attendee($attendee) {
	// function that outputs the gathered data

	global $delim, $newline, $wpdb;

// Change these to whatever columns you need. Be sure to also update the header row, above.

			echo $attendee->EVT_ID;
			echo $delim;

			echo $attendee->group_count;
			echo $delim;

			echo $attendee->attendee_count;
			echo $delim;

			echo $attendee->STS_code;
			echo $delim;

			echo $attendee->REG_date;
			echo $delim;

			echo $attendee->ATT_fname;
			echo $delim;

			echo $attendee->ATT_lname;
			echo $delim;

			echo $attendee->ATT_address;
			echo $delim;

			echo $attendee->ATT_zip;
			echo $delim;

			echo $attendee->ATT_city;
			echo $delim;

			echo $attendee->ATT_phone;
			echo $delim;

			echo $attendee->ATT_email;
			echo $delim;

			echo number_format($attendee->TXN_total, 2, ',', '');
			echo $delim;

			echo number_format($attendee->TXN_paid, 2, ',', '');
			echo $delim;

			echo $attendee->TKT_name;
			echo $delim;

			// Get custom questions and answers
			// NOTE: In this example I only need question 13 and 14. Adjust the query below to match your needs.
			// You can find your question IDs in WordPress Dashboard --> Event Espresso --> Registration Form
			
                                        $hestefest = $wpdb->get_results( $wpdb->prepare (
                                                "SELECT * FROM " . $wpdb->prefix . "esp_answer, " . $wpdb->prefix . "esp_question
                                                        WHERE
                                                                " . $wpdb->prefix . "esp_answer.REG_ID = $attendee->REG_ID
                                                        AND
                                                                        " . $wpdb->prefix . "esp_question.QST_ID = " . $wpdb->prefix . "esp_answer.QST_ID
                                                        AND
                                                                        (" . $wpdb->prefix . "esp_question.QST_ID = 13 OR " . $wpdb->prefix . "esp_question.QST_ID = 14)
							ORDER BY
									" . $wpdb->prefix . "esp_question.QST_ID"
                                        ) );

       foreach ($hestefest as $singleline) {

              // Loop through the questions, output accordingly. Make tests to output custom content based on question type
							if ($singleline->QST_ID == 13)
								echo $singleline->ANS_value;
							if ($singleline->QST_ID == 14 && $singleline->ANS_value != '')
								echo "IKKE FOTO";

							echo $delim;
       }

			// echo newline
			echo $newline;
}
