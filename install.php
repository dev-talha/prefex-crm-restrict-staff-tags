<?php

defined('BASEPATH') or exit('No direct script access allowed');

$file = APPPATH . 'libraries/App_tags.php';

if (!file_exists($file) || !is_writable($file)) {
    log_message('error', 'Restrict Staff Tags: App_tags.php missing or not writable: ' . $file);
    return;
}

$contents = file_get_contents($file);
if ($contents === false) {
    log_message('error', 'Restrict Staff Tags: Unable to read App_tags.php');
    return;
}

if (strpos($contents, 'RESTRICT_STAFF_TAGS_PATCH_START') !== false) {
    return; // Already patched.
}

$search = <<<'PHPSEARCH'
                } else {
                    $tag_id = $this->create(['name' => $tag]);
                    hooks()->do_action('new_tag_created', $tag_id);
                }
PHPSEARCH;

$replace = <<<'PHPREPLACE'
                } else {
                    // RESTRICT_STAFF_TAGS_PATCH_START
                    // Only admins can create new global tags. Non-admin staff can still use existing tags.
                    if (function_exists('is_admin') && !is_admin()) {
                        $order++;
                        continue;
                    }
                    // RESTRICT_STAFF_TAGS_PATCH_END
                    $tag_id = $this->create(['name' => $tag]);
                    hooks()->do_action('new_tag_created', $tag_id);
                }
PHPREPLACE;

if (strpos($contents, $search) === false) {
    log_message('error', 'Restrict Staff Tags: Patch target block not found in App_tags.php');
    return;
}

$backup = $file . '.restrict_staff_tags_backup_' . date('Ymd_His');
@copy($file, $backup);

$patched = str_replace($search, $replace, $contents, $count);

if ($count < 1) {
    log_message('error', 'Restrict Staff Tags: Patch replacement failed');
    return;
}

if (file_put_contents($file, $patched, LOCK_EX) === false) {
    log_message('error', 'Restrict Staff Tags: Unable to write patched App_tags.php');
    return;
}

log_message('debug', 'Restrict Staff Tags: App_tags.php patched successfully. Backup: ' . $backup);
