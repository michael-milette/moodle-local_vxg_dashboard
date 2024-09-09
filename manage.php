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
 * Dashboard manager.
 *
 * @package local_vxg_dashboard
 * @copyright 2021 Alex Morris
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once(__DIR__ . '/locallib.php');

global $DB;

require_login();
require_capability('local/vxg_dashboard:managedashboard', context_system::instance());

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (empty($returnurl)) {
    $returnurl = new moodle_url('/my');
} else {
    // Unescape any ampersands, etc.
    $returnurl = urldecode($returnurl);
}

$url = new moodle_url('/local/vxg_dashboard/manage.php');

$heading = get_string('manage', 'local_vxg_dashboard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->set_url($url);
$PAGE->set_pagelayout('incourse');
$PAGE->navbar->add(get_string('manage', 'local_vxg_dashboard'));

$dashboardsettings = $DB->get_records('local_vxg_dashboard');

$table = new table_sql('local_vxg_dashboard_table');

echo $OUTPUT->header();

$tableheaders = [
    get_string('name', 'local_vxg_dashboard'),
    get_string('roles', 'local_vxg_dashboard'),
    get_string('contextlevel', 'local_vxg_dashboard'),
    get_string('edit'),
];

$table->define_headers($tableheaders);

$table->define_columns(['name', 'roles', 'contextlevel', 'edit']);
$table->define_baseurl($url);
$table->sortable(false);
$table->collapsible(false);
$table->setup();
$class = '';

foreach ($dashboardsettings as $dashboardsetting) {
    $row = [];
    $class = '';

    $linkurl = new moodle_url('/local/vxg_dashboard/index.php', ['id' => $dashboardsetting->id]);

    $editpicurl = new moodle_url('/pix/t/editinline.svg');
    $editurl    = new moodle_url('/local/vxg_dashboard/edit.php',
        ['id' => $dashboardsetting->id, 'returnurl' => $returnurl]);

    $editlinkpic = html_writer::link($editurl, $OUTPUT->pix_icon('t/editinline', 'Edit'));

    $deletepicurl = new moodle_url('/pix/t/delete.svg');
    $deleteurl    = new moodle_url('/local/vxg_dashboard/delete.php',
        ['id' => $dashboardsetting->id, 'sesskey' => sesskey(), 'returnurl' => $returnurl]);

    $deletelink = html_writer::link($deleteurl,
    $OUTPUT->pix_icon('t/delete', get_string('delete', 'local_vxg_dashboard')));

    if (!empty($dashboardsetting->dashboard_name)) {
        $dashboardname = $dashboardsetting->dashboard_name;
    } else {
        $dashboardname = get_string('dashboard', 'local_vxg_dashboard');
    }
    $row[] = $editlink = html_writer::link($linkurl, $dashboardname);
    $roles = local_vxg_dashboard_get_access_roles($dashboardsetting->id);
    if (empty($roles)) {
        $roles = '&lt;' . get_string('all') . '&gt;';
    }
    $row[] = $roles;
    $row[] = \context_helper::get_level_name($dashboardsetting->contextlevel);
    $row[] = local_vxg_dashboard_get_access_roles($dashboardsetting->id);
    $row[] = $editlinkpic . $deletelink;

    $table->add_data($row, $class);
}

$newurl = new moodle_url('/local/vxg_dashboard/edit.php', ['returnurl' => $returnurl]);
$newbtn = html_writer::link($newurl,
    html_writer::tag('button', get_string('add_new', 'local_vxg_dashboard'),
        ['class' => 'btn btn-primary', 'style' => 'margin:5px;']));

$returnbtn = html_writer::link($returnurl,
    html_writer::tag('button', get_string('back', 'local_vxg_dashboard'),
        ['class' => 'btn btn-secondary', 'style' => 'margin:5px;float:right;']));

echo $newbtn;
echo $returnbtn;

$table->finish_output();
echo $OUTPUT->footer();
