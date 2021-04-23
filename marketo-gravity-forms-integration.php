<?php
/**
 * Plugin Name: Marketo and Gravity form Integration
 * Description: This is custom plugin for Marketo and Gravity form integration.
 * Author: Tatvasoft Team
 * Author URI: https://www.tatvasoft.com/
 * Version: 1.0.0
 * Text Domain: gf-marketo-gravity-form-integration
 */

/*
 * Exit if accessed directly 
 */
if (!defined('ABSPATH')) {
    exit;
}

require_once (dirname(__FILE__) . '/class/class-marketo-get-programs.php');
require_once (dirname(__FILE__) . '/class/class-marketo-create-lead.php');
require_once (dirname(__FILE__) . '/marketo-send-entry.php');

/*
 * Add Form settings option for disable UTM cookies option and Form Intent Option
 */

add_filter('gform_form_settings', 'gf_marketo_add_custom_settings_fields', 10, 2);

if (!function_exists('gf_marketo_add_custom_settings_fields')) {

    function gf_marketo_add_custom_settings_fields($settings, $form) {

        /* Marketo programs Dropdown List */
        $marketo_programs = new GFMarketoGetMarketoPrograms();

        $rest_api_endpoint_url = get_field('rest_api_endpoint_url', 'options');
        $marketo_client_id = get_field('marketo_client_id', 'options');
        $marketo_secret_key = get_field('marketo_secret_key', 'options');
        $marketo_programs->maxReturn = 200;

        $programs_list = $marketo_programs->getProgramsData($rest_api_endpoint_url, $marketo_client_id, $marketo_secret_key);
        $marketo_programs_array = json_decode($programs_list, true);

        $gform_form_marketo_programs_checked = rgar($form, 'gform_form_marketo_programs');

        $options = '';

        $settings['Form Options']['gform_form_marketo_programs'] = '  <tr>
            <th>
                ' . __('Marketo Programs', 'gravityforms') . ' ' . gform_tooltip('gform_form_marketo_programs_tooltip', '', true) . '
            </th>
            <td>
                <select id="gform_form_marketo_programs" name="gform_form_marketo_programs">
                    <option value="">Select Marketo Programs</option>';

        foreach ($marketo_programs_array['result'] as $marketo_programs_item) {
            $program_name = $marketo_programs_item['name'];
            $program_name_webform = substr($program_name, 0, 8);

            if ($program_name_webform === "webform-" || $program_name_webform === "Webform-") {
                if ($program_name == $gform_form_marketo_programs_checked) {
                    $options .= '<option value="' . $program_name . '" selected>' . $program_name . '</option>';
                } else {
                    $options .= '<option value="' . $program_name . '">' . $program_name . '</option>';
                }
            }
        }

        $settings['Form Options']['gform_form_marketo_programs'] .= $options . '</select>
               
            </td>
        </tr>';

        /* End- Marketo programs Dropdown List */

        return $settings;
    }

}

/*
 * Filter to add a new tooltip
 */
add_filter('gform_tooltips', 'gf_marketo_add_encryption_tooltips');

if (!function_exists('gf_marketo_add_encryption_tooltips')) {

    function gf_marketo_add_encryption_tooltips($tooltips) {
        $tooltips['gform_form_marketo_programs_tooltip'] = "Marketo API <h6>Programs</h6>";
        return $tooltips;
    }

}

/*
 *  save your custom form setting
 */
add_filter('gform_pre_form_settings_save', 'gf_marketo_save_opencorp_gravity_form_custom_setting');

if (!function_exists('gf_marketo_save_opencorp_gravity_form_custom_setting')) {

    function gf_marketo_save_opencorp_gravity_form_custom_setting($form) {
        $form['gform_form_marketo_programs'] = rgpost('gform_form_marketo_programs');
        return $form;
    }

}

/*
 *  Add mapping field in fields advanced tab
 */

add_action('gform_field_advanced_settings', 'gf_marketo_advanced_settings', 15, 2);

if (!function_exists('gf_marketo_advanced_settings')) {

    function gf_marketo_advanced_settings($position, $form_id) {

        $rest_api_endpoint_url = get_field('rest_api_endpoint_url', 'options');
        $marketo_client_id = get_field('marketo_client_id', 'options');
        $marketo_secret_key = get_field('marketo_secret_key', 'options');

        $upsert = new GFMarketoGetMarketoPrograms12();
        $marketo_field_response = $upsert->getProgramsData12($rest_api_endpoint_url, $marketo_client_id, $marketo_secret_key);

        $marketo_field_response_array = json_decode($marketo_field_response, true);

        if ($position == -1) {
            ?>
            <br>
            <li class="map_api_setting">
                <label for="field_admin_label"><?php _e("Map Field to Marketo field", "gf-marketo-gravity-form-integration"); ?></label>
                <?php
                $MappingFields = array(
                    'address' => 'Address',
                    'annualRevenue' => 'Annual Revenue',
                    'anonymousIP' => 'Anonymous IP',
                    'billingCity' => 'Billing City',
                    'billingCountry' => 'Billing Country',
                    'billingPostalCode' => 'Billing Postal Code',
                    'billingState' => 'Billing State',
                    'billingStreet' => 'Billing Address',
                    'company' => 'Company Name',
                    'dateOfBirth' => 'Date of Birth',
                    'department' => 'Department',
                    'doNotCall' => 'Do Not Call',
                    'doNotCallReason' => 'Do Not Call Reason',
                    'email' => 'Email Address',
                    'fax' => 'Fax Number',
                    'firstName' => 'First Name',
                    'industry' => 'Industry',
                    'inferredCompany' => 'Inferred Company',
                    'inferredCountry' => 'Inferred Country',
                    'lastName' => 'Last Name',
                    'leadRole' => 'Role',
                    'leadScore' => 'Lead Score',
                    'leadSource' => 'Lead Source',
                    'leadStatus' => 'Lead Status',
                    'mainPhone' => 'Main Phone',
                    'jigsawContactId' => 'Marketo Data.com ID',
                    'jigsawContactStatus' => 'Marketo Data.com Status',
                    'facebookDisplayName' => 'Marketo Social Facebook Display Name',
                    'facebookId' => 'Marketo Social Facebook Id',
                    'facebookPhotoURL' => 'Marketo Social Facebook Photo URL',
                    'facebookProfileURL' => 'Marketo Social Facebook Profile URL',
                    'facebookReach' => 'Marketo Social Facebook Reach',
                    'facebookReferredEnrollments' => 'Marketo Social Facebook Referred Enrollments',
                    'facebookReferredVisits' => 'Marketo Social Facebook Referred Visits',
                    'gender' => 'Marketo Social Gender',
                    'lastReferredEnrollment' => 'Marketo Social Last Referred Enrollment',
                    'lastReferredVisit' => 'Marketo Social Last Referred Visit',
                    'linkedInDisplayName' => 'Marketo Social LinkedIn Display Name',
                    'linkedInId' => 'Marketo Social LinkedIn Id',
                    'linkedInPhotoURL' => 'Marketo Social LinkedIn Photo URL',
                    'linkedInProfileURL' => 'Marketo Social LinkedIn Profile URL',
                    'linkedInReach' => 'Marketo Social LinkedIn Reach',
                    'linkedInReferredEnrollments' => 'Marketo Social LinkedIn Referred Enrollments',
                    'linkedInReferredVisits' => 'Marketo Social LinkedIn Referred Visits',
                    'syndicationId' => 'Marketo Social Syndication ID',
                    'totalReferredEnrollments' => 'Marketo Social Total Referred Enrollments',
                    'totalReferredVisits' => 'Marketo Social Total Referred Visits',
                    'twitterDisplayName' => 'Marketo Social Twitter Display Name',
                    'twitterId' => 'Marketo Social Twitter Id',
                    'twitterPhotoURL' => 'Marketo Social Twitter Photo URL',
                    'twitterProfileURL' => 'Marketo Social Twitter Profile URL',
                    'twitterReach' => 'Marketo Social Twitter Reach',
                    'twitterReferredEnrollments' => 'Marketo Social Twitter Referred Enrollments',
                    'twitterReferredVisits' => 'Marketo Social Twitter Referred Visits',
                    'middleName' => 'Middle Name',
                    'mobilePhone' => 'Mobile Phone Number',
                    'numberOfEmployees' => 'Num Employees',
                    'phone' => 'Phone Number',
                    'rating' => 'Lead Rating',
                    'salutation' => 'Salutation',
                    'sicCode' => 'SIC Code',
                    'site' => 'Site',
                    'title' => 'Job Title',
                    'unsubscribed' => 'Unsubscribed',
                    'unsubscribedReason' => 'Unsubscribed Reason',
                    'website' => 'Website',
                    'createdAt' => 'Created At',
                    'updatedAt' => 'Updated At',
                    'emailInvalid' => 'Email Invalid',
                    'emailInvalidCause' => 'Email Invalid Cause',
                    'inferredCity' => 'Inferred City',
                    'inferredMetropolitanArea' => 'Inferred Metropolitan Area',
                    'inferredPhoneAreaCode' => 'Inferred Phone Area Code',
                    'inferredPostalCode' => 'Inferred Postal Code',
                    'inferredStateRegion' => 'Inferred State Region',
                    'isAnonymous' => 'Is Anonymous',
                    'priority' => 'Priority',
                    'relativeScore' => 'Relative Score',
                    'urgency' => 'Urgency',
                    'utm_source' => 'UTM Source',
                    'utm_medium' => 'UTM Medium',
                    'utm_term' => 'UTM Term',
                    'utm_content' => 'UTM Content',
                    'utm_campaign' => 'UTM Campaign'
                );
                ?> 
                <select id="field_map_API_setting" onChange="SetFieldProperty('map_API_settingField', this.value);" style="max-width: 100%;">
                    <option value="0">Don't Map</option>
                    <?php
                    foreach ($marketo_field_response_array['result'] as $marketo_field) {
                        ?>
                        <option value="<?php echo $marketo_field['rest']['name']; ?>"><?php echo $marketo_field['displayName']; ?></option>
                        <?php
                    }
                    ?>
                </select>

            </li>
            <?php
        }
    }
}

/*
 * Add Admin notice if Gravity form Plugin is not activated
 */

add_action('admin_notices', 'gf_ad_gform_admin_notice');

if (!function_exists('gf_ad_gform_admin_notice')) {

    function gf_ad_gform_admin_notice() {
        if (!is_plugin_active('gravityforms/gravityforms.php')) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e('<strong>Gravity Forms</strong> Plugin must be activated to use <strong>"Gravity Forms: Attachment Download on Form Submission"</strong> plugin.', 'gf-marketo-gravity-form-integration'); ?></p>
            </div>
            <?php
        }
    }

}

/*
 * Action to inject supporting script to the form editor page
 */
add_action('gform_editor_js', 'gf_marketo_editor_script');

if (!function_exists('gf_marketo_editor_script')) {

    function gf_marketo_editor_script() {
        ?>
        <script type='text/javascript'>
            fieldSettings["sageAPI"] += ", .map_api_setting";

            /* binding to the load field settings event to initialize the checkbox */
            jQuery(document).bind("gform_load_field_settings", function (event, field, form) {
                jQuery("#field_map_API_setting").val(field["map_API_settingField"]);
            });
        </script>
        <?php
    }

}