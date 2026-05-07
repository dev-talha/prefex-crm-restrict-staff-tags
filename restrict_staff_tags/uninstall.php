<?php

defined('BASEPATH') or exit('No direct script access allowed');

$file = APPPATH . 'libraries/App_tags.php';
if (!file_exists($file) || !is_writable($file)) {
    log_message('error', 'Restrict Staff Tags: App_tags.php missing or not writable during uninstall: ' . $file);
    return;
}

$contents = file_get_contents($file);
if ($contents === false || strpos($contents, 'RESTRICT_STAFF_TAGS_PATCH_START') === false) {
    return;
}

$patchedBlock = <<<'PHPBLOCK'
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
PHPBLOCK;

$originalBlock = <<<'PHPBLOCK'
                } else {
                    $tag_id = $this->create(['name' => $tag]);
                    hooks()->do_action('new_tag_created', $tag_id);
                }
PHPBLOCK;

$restored = str_replace($patchedBlock, $originalBlock, $contents, $count);
if ($count > 0) {
    file_put_contents($file, $restored, LOCK_EX);
    log_message('debug', 'Restrict Staff Tags: App_tags.php patch removed successfully.');
}
