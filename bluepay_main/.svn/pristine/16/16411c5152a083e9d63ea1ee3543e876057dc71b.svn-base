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
 * BluePay Express enrolments plugin settings and presets.
 *
 * @package    enrol
 * @subpackage bluepay
 * @copyright  2012 Mark V Madsen www.starportmedia.com
 * @author     www.starportmedia.com - based on code by Eugene Venter, Petr Skoda, and others
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {

    //--- settings ------------------------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_bluepay_settings', '', get_string('pluginname_desc', 'enrol_bluepay')));
 
    $settings->add(new admin_setting_configtext('enrol_bluepay/bluepayacctid', get_string('string_accountid', 'enrol_bluepay'), get_string('anaccountid', 'enrol_bluepay'), '', PARAM_RAW));

    $settings->add(new admin_setting_configtext('enrol_bluepay/bluepaysecretkey', get_string('string_secretkey', 'enrol_bluepay'), get_string('antrankey', 'enrol_bluepay'), '', PARAM_RAW));

    $settings->add(new admin_setting_configtext('enrol_bluepay/moduleurl', get_string('string_module_url', 'enrol_bluepay'), get_string('anmoduleurl', 'enrol_bluepay'), '', PARAM_RAW));
    
    $bluepaymode = array('LIVE' => 'LIVE Mode',
                              'TEST' => 'TEST Mode',
                        );
    $settings->add(new admin_setting_configselect('enrol_bluepay/mode', get_string('mode', 'enrol_bluepay'), '', 'LIVE', $bluepaymode));
    
    $settings->add(new admin_setting_configcheckbox('enrol_bluepay/mailstudents', get_string('mailstudents', 'enrol_bluepay'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_bluepay/mailteachers', get_string('mailteachers', 'enrol_bluepay'), '', 0));

    $settings->add(new admin_setting_configcheckbox('enrol_bluepay/mailadmins', get_string('mailadmins', 'enrol_bluepay'), '', 0));

    //--- enrol instance defaults ----------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('enrol_bluepay_defaults',
        get_string('enrolinstancedefaults', 'admin'), get_string('enrolinstancedefaults_desc', 'admin')));

    $options = array(ENROL_INSTANCE_ENABLED  => get_string('yes'),
                     ENROL_INSTANCE_DISABLED => get_string('no'));
    $settings->add(new admin_setting_configselect('enrol_bluepay/status',
        get_string('status', 'enrol_bluepay'), get_string('status_desc', 'enrol_bluepay'), ENROL_INSTANCE_DISABLED, $options));

    $settings->add(new admin_setting_configtext('enrol_bluepay/cost', get_string('cost', 'enrol_bluepay'), '', 0, PARAM_FLOAT, 4));

    $bluepaycurrencies = array('USD' => 'US Dollars',
                              'EUR' => 'Euros',
                              'JPY' => 'Japanese Yen',
                              'GBP' => 'British Pounds',
                              'CAD' => 'Canadian Dollars',
                              'AUD' => 'Australian Dollars'
                             );
    $settings->add(new admin_setting_configselect('enrol_bluepay/currency', get_string('currency', 'enrol_bluepay'), '', 'USD', $bluepaycurrencies));

    if (!during_initial_install()) {
        $options = get_default_enrol_roles(get_context_instance(CONTEXT_SYSTEM));
        $student = get_archetype_roles('student');
        $student = reset($student);
        $settings->add(new admin_setting_configselect('enrol_bluepay/roleid',
            get_string('defaultrole', 'enrol_bluepay'), get_string('defaultrole_desc', 'enrol_bluepay'), $student->id, $options));
    }

    $settings->add(new admin_setting_configtext('enrol_bluepay/enrolperiod',
        get_string('enrolperiod', 'enrol_bluepay'), get_string('enrolperiod_desc', 'enrol_bluepay'), 0, PARAM_INT));
}
