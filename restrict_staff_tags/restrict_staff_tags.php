<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Restrict Staff Tags
Description: Restrict non-admin staff from creating new custom tags while allowing existing tags.
Version: 1.0.3
Requires at least: 2.3.*
Author: Custom
*/

define('RESTRICT_STAFF_TAGS_MODULE_NAME', 'restrict_staff_tags');

register_activation_hook(RESTRICT_STAFF_TAGS_MODULE_NAME, 'restrict_staff_tags_activation_hook');
register_uninstall_hook(RESTRICT_STAFF_TAGS_MODULE_NAME, 'restrict_staff_tags_uninstall_hook');

hooks()->add_action('app_admin_footer', 'restrict_staff_tags_admin_footer_alert_script');

function restrict_staff_tags_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

function restrict_staff_tags_uninstall_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/uninstall.php');
}

/**
 * Frontend helper for non-admin staff.
 * Backend protection remains in application/libraries/App_tags.php from the activation patch.
 */
function restrict_staff_tags_admin_footer_alert_script()
{
    if (function_exists('is_admin') && is_admin()) {
        return;
    }
    ?>
    <script>
    (function ($) {
        'use strict';

        var restrictStaffTagsMessage = 'Custom tags are not allowed. If you need to add, contact your admin.';
        var restrictStaffTagsLastAlert = {
            tag: '',
            time: 0
        };

        function normalizeTag(value) {
            return $.trim(String(value || '')).toLowerCase();
        }

        function isExistingPerfexTag(tagLabel) {
            if (typeof app === 'undefined' || !$.isArray(app.available_tags)) {
                return true;
            }

            var tag = normalizeTag(tagLabel);
            for (var i = 0; i < app.available_tags.length; i++) {
                if (normalizeTag(app.available_tags[i]) === tag) {
                    return true;
                }
            }
            return false;
        }

        function clearPendingTagText($input) {
            if (!$input || !$input.length) {
                return;
            }

            var widget = $input.data('ui-tagit');

            if (widget && widget.tagInput && widget.tagInput.length) {
                widget.tagInput.val('');
                widget.tagInput.removeData('autocomplete-open');

                if (widget.tagInput.data('ui-autocomplete')) {
                    widget.tagInput.autocomplete('close');
                }
            }

            // Keep the original hidden input unchanged, but trigger a change event
            // so Perfex UI helpers can refresh placeholders/states when needed.
            $input.trigger('change');
        }

        function showRestrictedTagAlertOnce(tagLabel) {
            var tag = normalizeTag(tagLabel);
            var now = new Date().getTime();

            // Prevent duplicate alert when Tag-it fires add checks from both Enter and blur.
            if (restrictStaffTagsLastAlert.tag === tag && (now - restrictStaffTagsLastAlert.time) < 1000) {
                return;
            }

            restrictStaffTagsLastAlert.tag = tag;
            restrictStaffTagsLastAlert.time = now;

            if (typeof alert_float === 'function') {
                alert_float('warning', restrictStaffTagsMessage);
            } else {
                alert(restrictStaffTagsMessage);
            }
        }

        function restrictOneTagInput($input) {
            if (!$input.length || !$input.data('ui-tagit')) {
                return;
            }

            var oldBeforeTagAdded = $input.tagit('option', 'beforeTagAdded');
            if (oldBeforeTagAdded && oldBeforeTagAdded.__restrictStaffTagsWrapped) {
                return;
            }

            var wrappedBeforeTagAdded = function (event, ui) {
                if (ui && ui.duringInitialization) {
                    if ($.isFunction(oldBeforeTagAdded)) {
                        return oldBeforeTagAdded.call(this, event, ui);
                    }
                    return true;
                }

                var tagLabel = ui && ui.tagLabel ? ui.tagLabel : '';
                var $currentInput = $(this);

                if (!isExistingPerfexTag(tagLabel)) {
                    clearPendingTagText($currentInput);
                    showRestrictedTagAlertOnce(tagLabel);
                    return false;
                }

                if ($.isFunction(oldBeforeTagAdded)) {
                    return oldBeforeTagAdded.call(this, event, ui);
                }

                return true;
            };
            wrappedBeforeTagAdded.__restrictStaffTagsWrapped = true;

            $input.tagit('option', 'beforeTagAdded', wrappedBeforeTagAdded);
        }

        function restrictAllTagInputs(context) {
            var $context = context ? $(context) : $('body');
            $context.find('input.tagsinput').each(function () {
                restrictOneTagInput($(this));
            });
        }

        $(function () {
            restrictAllTagInputs(document);
            setTimeout(function () { restrictAllTagInputs(document); }, 500);
            setTimeout(function () { restrictAllTagInputs(document); }, 1500);
        });

        // Cover Perfex modals/dynamic content where appTagsInput(element) is called later.
        if (typeof window.appTagsInput === 'function' && !window.appTagsInput.__restrictStaffTagsWrapped) {
            var originalAppTagsInput = window.appTagsInput;
            window.appTagsInput = function (element) {
                var result = originalAppTagsInput.apply(this, arguments);
                restrictAllTagInputs(element || document);
                return result;
            };
            window.appTagsInput.__restrictStaffTagsWrapped = true;
        }

        // Extra safety for dynamically inserted modals/content.
        $(document).on('shown.bs.modal', function (event) {
            restrictAllTagInputs(event.target);
        });
    })(jQuery);
    </script>
    <?php
}

// Important: This module intentionally does NOT override/load App_tags at runtime.
// The activation hook applies a small guarded core patch to block database creation.
