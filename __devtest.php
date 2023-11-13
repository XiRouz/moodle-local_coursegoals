<?php

require_once('../../config.php');

require_admin();

$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

\local_coursegoals\Goal::recalculateTaskCompletionsForUser($userid, $courseid);

echo 'devtest';
