<?php

/* Send Entries to Marketo API */

add_filter('gform_confirmation', 'gf_marketo_send_entries_to_marketo', 10, 4);

if (!function_exists('gf_marketo_send_entries_to_marketo')) {

    function gf_marketo_send_entries_to_marketo($confirmation, $form, $entry, $ajax) {

        /* Send entrires to Marketo API */

        $objectData = array();
        $address_segment_array = array();
        $address_marketo_array = array();

        if ($form['gform_form_marketo_programs'] != "") {
            foreach ($form['fields'] as $allField) {
                if ($allField['map_API_settingField']) {
                    $FieldID = $allField['id'];
                    $MapFieldName = $allField['map_API_settingField'];

                    /* Replace CRM map field names with segment/REST API field name */
                    if ($allField['inputs'] && $allField['type'] == 'name') {
                        $NameMappingFields = array(
                            '2' => 'salutation',
                            '3' => 'firstName',
                            '4' => 'middleName',
                            '6' => 'lastName',
                        );

                        foreach ($NameMappingFields as $code => $SubFieldName) {
                            $subEntryNameID = $FieldID . "." . $code;
                            if ($entry[$subEntryNameID]) {
                                $objectData[$SubFieldName] = $entry[$subEntryNameID];
                            }
                        }
                    } else if ($allField['inputs'] && $allField['type'] == 'address') {
                        $addressArray['address'] = array();
                        $AddressMappingFields = array(
                            '1' => 'address',
                            '2' => 'address2',
                            '3' => 'city',
                            '4' => 'state',
                            '5' => 'postalCode',
                            '6' => 'country'
                        );

                        $StateMappingFields = array(
                            'Victoria' => 'VIC',
                            'New South Wales' => 'NSW',
                            'Queensland' => 'QLD',
                            'Tasmania' => 'TAS',
                            'Norther Territory' => 'NT',
                            'Western Australia' => 'WA',
                            'Australian Capital Territory' => 'ACT',
                            'South Australia' => 'SA',
                        );

                        foreach ($AddressMappingFields as $code => $SubFieldName) {
                            $subEntryID = $FieldID . "." . $code;
                            if ($entry[$subEntryID]) {
                                if ($SubFieldName == "state" && $entry[$subEntryID] != "") {
                                    foreach ($StateMappingFields as $StateName => $StateCode) {
                                        if ($entry[$subEntryID] == $StateName) {
                                            $entry[$subEntryID] = $StateCode;
                                        }
                                    }
                                }
                                if ($SubFieldName == "address2") {
                                    $AddressLine1 = $address_marketo_array['address'];
                                    $address_marketo_array['address'] = $AddressLine1 . ", " . $entry[$subEntryID];
                                } else {
                                    $address_marketo_array[$SubFieldName] = $entry[$subEntryID];
                                }

                                if ($SubFieldName == "address") {
                                    $addressArray['address']['street'] = $entry[$subEntryID];
                                } else {
                                    $addressArray['address'][$SubFieldName] = $entry[$subEntryID];
                                }
                            }

                            if ($code == '1') {
                                $address_segment_array['street_address'] = $entry[$subEntryID];
                            }
                        }
                        $address_segment_array['address'] = $addressArray['address'];
                    } else if ($allField['inputs'] && $allField['type'] == 'checkbox') {
                        $choices_counter = 1;
                        $subEntryIDChoicesValue = array();
                        foreach ($allField['choices'] as $choices) {
                            $subEntryChoiceID = $FieldID . "." . $choices_counter;
                            if ($entry[$subEntryChoiceID] != '') {
                                $subEntryIDChoicesValue[] = $entry[$subEntryChoiceID];
                            }
                            $choices_counter++;
                        }
                        $subEntryIDChoicesValueImploded = implode(", ", $subEntryIDChoicesValue);
                        $objectData[$MapFieldName] = $subEntryIDChoicesValueImploded;
                    } else {
                        if ($entry[$FieldID] != "") {
                            $objectData[$MapFieldName] = $entry[$FieldID];
                        }
                        if ($MapFieldName == 'email') {
                            $userId = $entry[$FieldID];
                        }
                    }
                }
            }

            $object_data_for_marketo = array_merge($objectData, $address_marketo_array);

            /* Push Entry to marketo */
            $upsert = new GFMarketoPushLeads();

            /* Get Marketo Fields values from custom fields */
            $rest_api_endpoint_url = get_field('rest_api_endpoint_url', 'options');
            $marketo_client_id = get_field('marketo_client_id', 'options');
            $marketo_secret_key = get_field('marketo_secret_key', 'options');

            /* Set data for send to marketo API */
            $marketo_program_name = $form['gform_form_marketo_programs'];

            $upsert->programName = $marketo_program_name;
            $upsert->lookupField = "email";

            /* Call Function to Push Entry to marketo */
            $upsert->input = array($object_data_for_marketo);
            $marketo_lead_response = $upsert->postMarketoData($rest_api_endpoint_url, $marketo_client_id, $marketo_secret_key);
        }

        /* Send entrires to Marketo API */

        return $confirmation;
    }

}