<?php

use \local_coursegoals\helper;

/**
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know or string for the module purpose.
 */
function comprules_cmcompleteddb_supports($feature) {
    switch($feature) {
        case helper::FEATURE_CUSTOMVIEW:            return true;
        case helper::FEATURE_CUSTOMTASKDETAILS:     return true;

        default: return null;
    }
}