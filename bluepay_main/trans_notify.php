<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Listens for Instant Payment Notification from BluePay Express
 *
 * This script waits for Payment notification from BluePay Express,
 * then double checks that data by sending it back to BluePay Express.
 * If BluePay Express verifies this then it sets up the enrolment for that
 * user.
 *
 * @package    enrol
 * @subpackage bluepay
 * @copyright  2012 Mark V Madsen www.starportmedia.com
 * @author     www.starportmedia.com - based on code by Eugene Venter, Petr Skoda, and others
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require("../../config.php");
require_once("lib.php");
require_once($CFG->libdir.'/eventslib.php');
require_once($CFG->libdir.'/enrollib.php');
require_once($CFG->libdir.'/moodlelib.php');

/// Keep out casual intruders
if (empty($_POST) or !empty($_GET)) {
    print_error("Sorry, you can not use the script that way.");
}

/// read all the data from BluePay Express trans_notify and then check it further down this script;
/// you need to alert BluePay dev support to link back to this file with their trans_notify script.

$req = 'cmd=_notify-validate';

$data = new stdClass();

foreach ($_POST as $key => $value) {
    $req .= "&$key=".urlencode($value);
    $data->$key = $value;
}

// if you want to check the array of what is being sent from Bluepay. Prints to a file for you.
$myFile = "array_contents.txt";
$fh = fopen($myFile, 'w') or die("can't open file");
foreach ($data as $key => $value) {
  fwrite($fh, $key . '=>' . $value);  
}
// for testing purposes

//$custom = explode('-', $data->custom); // if you need to open a variable array sent by trans_notify
$data->courseid         = $data->custom_id1;
$data->userid           = $data->custom_id2;
$data->instanceid       = $data->invoice_id;
$data->payment_gross    = $data->amount;
$data->payment_currency = 'USD';
$data->timeupdated      = time();

/// get the user and course records

if (! $user = $DB->get_record("user", array("id"=>$data->userid))) {
    message_bluepay_error_to_admin("Not a valid user id", $data);
    die;
}

if (! $course = $DB->get_record("course", array("id"=>$data->courseid))) {
    message_bluepay_error_to_admin("Not a valid course id", $data);
    die;
}

if (! $context = get_context_instance(CONTEXT_COURSE, $course->id)) {
    message_bluepay_error_to_admin("Not a valid context id", $data);
    die;
}

if (! $plugin_instance = $DB->get_record("enrol", array("id"=>$data->instanceid, "status"=>0))) {
    message_bluepay_error_to_admin("Not a valid instance id", $data);
    die;
}

$plugin = enrol_get_plugin('bluepay');

/// SECURITY:: Now read the response and check if everything is OK.

$secret_key = $plugin->get_config('bluepaysecretkey');
echo 'bluepaysecretkey = ' . $secret_key .'<br>';

//$secure_stamp_sum = $secret_key . $data->trans_id . $data->trans_status . $data->trans_type . $data->payment_gross;
$secure_stamp_sum = $secret_key . $data->rrno . $data->issue_date . $data->result;

$secure_stamp = bin2hex( md5($secure_stamp_sum, true) );

// change to CAPS format which BluePay uses for it's bp_stamp
$secure_stamp_uc = strtoupper ($secure_stamp);
echo 'secure_stamp = ' . $secure_stamp_uc .'<br>';

if ($secure_stamp_uc == $data->BP_STAMP)
    { // SECURITY:: BluePay stamp is crosschecked by you with their bp_stamp variable

        // check the trans_status and payment_reason

        // If status is not completed or pending then unenrol the student if already enrolled
        // and notify admin

        if ($data->trans_status != "1" and $data->trans_status != "E") {
            $plugin->unenrol_user($plugin_instance, $data->userid);
            message_bluepay_error_to_admin("Status not completed or pending. User unenrolled from course", $data);
            die;
        }

        // If currency is incorrectly set then someone maybe trying to cheat the system

        /*if ($data->mc_currency != $plugin_instance->currency) {
            message_bluepay_error_to_admin("Currency does not match course settings, received: ".$data->mc_currency, $data);
            die;
        }*/

        // If status is pending and reason is other than echeck then we are on hold until further notice
        // Email user to let them know. Email admin.

        if ($data->trans_status == "E" and $data->payment_type != "ACH") { // not using electronic check and error
            $eventdata = new stdClass();
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_bluepay';
            $eventdata->name              = 'bluepay_enrolment';
            $eventdata->userfrom          = get_admin();
            $eventdata->userto            = $user;
            $eventdata->subject           = "Moodle: BluePay Express payment";
            $eventdata->fullmessage       = "Your BluePay Express payment is pending.";
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);

            message_bluepay_error_to_admin("Payment pending", $data);
            die;
        }

        // If our status is not completed or not pending on an echeck clearance then ignore and die
        // This check is redundant at present but may be useful if bluepay extend the return codes in the future

        if (! ( $data->trans_status == "1" or
               ($data->trans_status == "E" and $data->payment_type == "ACH") ) ) { // no electronic check and Declined
            die;
        }

        // At this point we only proceed with a status of completed or pending with a reason of echeck

        if ($existing = $DB->get_record("enrol_bluepay", array("trans_id"=>$data->trans_id))) {   // Make sure this transaction doesn't exist already
            message_bluepay_error_to_admin("Transaction $data->trans_id is being repeated!", $data);
            die;

        }

        if (!$user = $DB->get_record('user', array('id'=>$data->userid))) {   // Check that user exists
            message_bluepay_error_to_admin("User $data->userid doesn't exist", $data);
            die;
        }

        if (!$course = $DB->get_record('course', array('id'=>$data->courseid))) { // Check that course exists
            message_bluepay_error_to_admin("Course $data->courseid doesn't exist", $data);;
            die;
        }

        $coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);

        // Check that amount paid is the correct amount
        if ( (float) $plugin_instance->cost <= 0 ) {
            $cost = (float) $plugin->get_config('cost');
        } else {
            $cost = (float) $plugin_instance->cost;
        }

        if ($data->payment_gross < $cost) {
            $cost = format_float($cost, 2);
            message_bluepay_error_to_admin("Amount paid is not enough ($data->payment_gross < $cost))", $data);
            die;

        }

        // ALL CLEAR !

        $DB->insert_record("enrol_bluepay", $data);

        if ($plugin_instance->enrolperiod) {
            $timestart = time();
            $timeend   = $timestart + $plugin_instance->enrolperiod;
        } else {
            $timestart = 0;
            $timeend   = 0;
        }

        // Enrol user
        $plugin->enrol_user($plugin_instance, $user->id, $plugin_instance->roleid, $timestart, $timeend);

        // Pass $view=true to filter hidden caps if the user cannot see them
        if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC',
                                             '', '', '', '', false, true)) {
            $users = sort_by_roleassignment_authority($users, $context);
            $teacher = array_shift($users);
        } else {
            $teacher = false;
        }

        $mailstudents = $plugin->get_config('mailstudents');
        $mailteachers = $plugin->get_config('mailteachers');
        $mailadmins   = $plugin->get_config('mailadmins');
        $shortname = format_string($course->shortname, true, array('context' => $context));


        if (!empty($mailstudents)) {
            $a->coursename = format_string($course->fullname, true, array('context' => $coursecontext));
            $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id";

            $eventdata = new stdClass();
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_bluepay';
            $eventdata->name              = 'bluepay_enrolment';
            $eventdata->userfrom          = $teacher;
            $eventdata->userto            = $user;
            $eventdata->subject           = get_string("enrolmentnew", '', $shortname);
            $eventdata->fullmessage       = get_string('welcometocoursetext', '', $a);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);

        }

        if (!empty($mailteachers)) {
            $a->course = format_string($course->fullname, true, array('context' => $coursecontext));
            $a->user = fullname($user);

            $eventdata = new stdClass();
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_bluepay';
            $eventdata->name              = 'bluepay_enrolment';
            $eventdata->userfrom          = $user;
            $eventdata->userto            = $teacher;
            $eventdata->subject           = get_string("enrolmentnew", '', $shortname);
            $eventdata->fullmessage       = get_string('enrolmentnewuser', '', $a);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);
        }

        if (!empty($mailadmins)) {
            $a->course = format_string($course->fullname, true, array('context' => $coursecontext));
            $a->user = fullname($user);
            $admins = get_admins();
            foreach ($admins as $admin) {
                $eventdata = new stdClass();
                $eventdata->modulename        = 'moodle';
                $eventdata->component         = 'enrol_bluepay';
                $eventdata->name              = 'bluepay_enrolment';
                $eventdata->userfrom          = $user;
                $eventdata->userto            = $admin;
                $eventdata->subject           = get_string("enrolmentnew", '', $shortname);
                $eventdata->fullmessage       = get_string('enrolmentnewuser', '', $a);
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml   = '';
                $eventdata->smallmessage      = '';
                message_send($eventdata);
            }
        }

    } else { // ERROR
        $DB->insert_record("enrol_bluepay", $data, false);
        message_bluepay_error_to_admin("Received an invalid payment notification!! (Fake payment?)", $data);
    }
exit;


//--- HELPER FUNCTIONS --------------------------------------------------------------------------------------


function message_bluepay_error_to_admin($subject, $data) {
    echo $subject;
    $admin = get_admin();
    $site = get_site();

    $message = "$site->fullname:  Transaction failed.\n\n$subject\n\n";

    foreach ($data as $key => $value) {
        $message .= "$key => $value\n";
    }

    $eventdata = new stdClass();
    $eventdata->modulename        = 'moodle';
    $eventdata->component         = 'enrol_bluepay';
    $eventdata->name              = 'bluepay_enrolment';
    $eventdata->userfrom          = $admin;
    $eventdata->userto            = $admin;
    $eventdata->subject           = "BluePay Express ERROR: ".$subject;
    $eventdata->fullmessage       = $message;
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml   = '';
    $eventdata->smallmessage      = '';
    message_send($eventdata);
}


