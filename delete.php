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
 * Delete a dashboard.
 *
 * @package local_vxg_dashboard
 * @copyright 2021 Alex Morris
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_vxg_dashboard\event\vxg_dashboard_deleted;

require_once('../../config.php');
require_once(__DIR__ . '/locallib.php');

global $DB;

$id = required_param('id', PARAM_INT);
$delete = optional_param('delete', false, PARAM_BOOL);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

if (!empty($returnurl)) {
    // Unescape any ampersands, etc.
    $returnurl = urldecode($returnurl);
}

require_login();
require_sesskey();
require_capability('local/vxg_dashboard:managedashboard', context_system::instance());

$heading = get_string('delete', 'local_vxg_dashboard');
$PAGE->set_context(context_system::instance());
$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->set_url('/local/vxg_dashboard/delete.php', ['id' => $id]);
$PAGE->set_pagelayout('incourse');
$PAGE->navbar->add(get_string('manage', 'local_vxg_dashboard'), '/local/vxg_users/index.php');

$dashboard = $DB->get_record('local_vxg_dashboard', ['id' => $id]);
$redirecturl = new moodle_url('/local/vxg_dashboard/manage.php', ['returnurl' => $returnurl]);

if ($delete) {
    $DB->delete_records('local_vxg_dashboard', ['id' => $id]);
    local_vxg_dashboard_delete_dashboard_blocks($id);
    // Trigger event, vxg dashboard deleted.
    $eventparams = ['context' => $PAGE->context, 'objectid' => $id];
    $event = vxg_dashboard_deleted::create($eventparams);
    $event->trigger();
    redirect($redirecturl);
}

echo $OUTPUT->header();
echo $OUTPUT->confirm(get_string('delete_confirm', 'local_vxg_dashboard', $dashboard->dashboard_name)),
    new moodle_url('/local/vxg_dashboard/delete.php', ['id' => $id, 'delete' => true, 'sesskey' => sesskey()]),
    new moodle_url('/local/vxg_dashboard/manage.php', ['returnurl' => $returnurl]);
echo $OUTPUT->footer();
