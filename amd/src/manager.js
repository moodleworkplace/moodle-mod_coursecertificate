// This file is part of the mod_coursecertificate plugin for Moodle - http://moodle.org/
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
 * This module instantiates the functionality for actions on course certificates.
 *
 * @module      mod_coursecertificate/manager
 * @package     mod_coursecertificate
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([
    'core/ajax',
    'core/notification',
    'core/templates',
    'core/str'
], function(
    Ajax,
    Notification,
    Templates,
    Str
) {

    /** @type {Object} The list of selectors for the coursecertificate module. */
    const SELECTORS = {
        AUTOMATICSENDREGION: "[data-region='automaticsend-alert']",
        HIDDENWARNING: ".hidden-warning",
        NOAUTOSENDINFO: ".noautosend-info",
        REPORTREGION: "[data-region='issues-report']",
        TOGGLEAUTOMATICSEND: "[data-action='toggle-automaticsend']",
        REVOKEISSUE: "[data-action='revoke-issue']",
        LOADING: ".loading-overlay"
    },
    /** @type {Object} The list of templates for the coursecertificate module. */
    TEMPLATES = {
        AUTOMATICSENDALERT: 'mod_coursecertificate/automaticsend_alert',
        ISSUESREPORT: 'mod_coursecertificate/issues_report'
    },
    /** @type {Object} The list of services for the coursecertificate module. */
    SERVICES = {
        UPDATEAUTOMATICSEND: 'mod_coursecertificate_update_automaticsend',
        REVOKEISSUE: 'tool_certificate_revoke_issue',
    };

    /**
     * Show/Hide selector.
     *
     * @param {string} selector
     * @param {boolean} visibile
     */
    function setVisibility(selector, visibile) {
        if (visibile) {
            document.querySelector(selector).classList.remove('d-none');
            document.querySelector(selector).classList.remove('invisible');
        } else {
            document.querySelector(selector).classList.add('d-none');
            document.querySelector(selector).classList.add('invisible');
        }
    }

    /**
     * Toggle the automaticsend setting on/off for coursecertificate.
     *
     * @param {Element} automaticsendregion
     */
    function toggleAutomaticSend(automaticsendregion) {
        const {certificateid, automaticsend} =
            automaticsendregion.querySelector(SELECTORS.TOGGLEAUTOMATICSEND).dataset;
        const newstatus = automaticsend === '0';
        const strings = newstatus
        // Load strings depending on newstatus.
        ? [{'key': 'confirmation', component: 'admin'},
            {'key': 'enableautomaticsendpopup', component: 'coursecertificate'},
            {'key': 'confirm'},
            {'key': 'cancel'}]
        : [{'key': 'confirmation', component: 'admin'},
            {'key': 'disableautomaticsend', component: 'coursecertificate'},
            {'key': 'confirm'},
            {'key': 'cancel'}];
        Str.get_strings(strings).then((s) => {
            // Show confirm notification.
            Notification.confirm(s[0], s[1], s[2], s[3], () => {
                M.util.js_pending('mod_coursecertificate_toggle_automaticsend');
                // Show loading template.
                setVisibility(SELECTORS.LOADING, true);
                // Call to webservice.
                Ajax.call([{methodname: SERVICES.UPDATEAUTOMATICSEND,
                    args: {id: certificateid, automaticsend: newstatus}}])[0]
                // Reload automatic send alert template.
                .then((result) => {
                    let {showhiddenwarning, shownoautosendinfo} = result;
                    Templates.render(TEMPLATES.AUTOMATICSENDALERT,
                        {certificateid: certificateid, automaticsend: newstatus}, '')
                        .then((html) => {
                            automaticsendregion.innerHTML = html;
                            setVisibility(SELECTORS.HIDDENWARNING, showhiddenwarning);
                            setVisibility(SELECTORS.NOAUTOSENDINFO, shownoautosendinfo);
                            M.util.js_complete('mod_coursecertificate_toggle_automaticsend');
                            return null;
                        })
                        .fail(Notification.exception);
                    return null;
                })
                .fail(Notification.exception);
            });
            return null;
        }).fail(Notification.exception);
    }

    /**
     * Revoke the issue.
     *
     * @param {int} issueid
     */
    function revokeIssue(issueid) {
        const strings = [{'key': 'confirmation', component: 'admin'},
            {'key': 'revokeissue', component: 'coursecertificate'},
            {'key': 'confirm'},
            {'key': 'cancel'}];
        Str.get_strings(strings).then((s) => {
            // Show confirm notification.
            Notification.confirm(s[0], s[1], s[2], s[3], () => {
                M.util.js_pending('mod_coursecertificate_revoke_issue');
                // Call to webservice to revoke issue.
                Ajax.call([{methodname: SERVICES.REVOKEISSUE, args: {id: issueid}}])[0]
                // Call to webservice to get updated table.
                .then(() => {
                    M.util.js_complete('mod_coursecertificate_revoke_issue');
                    window.location.reload();
                    return null;
                })
                .fail(Notification.exception);
            });
            return null;
        }).fail(Notification.exception);
    }

    return {
        init: function() {
            const automaticsendregion = document.querySelector(SELECTORS.AUTOMATICSENDREGION);
            if (automaticsendregion) {
                automaticsendregion.addEventListener('click', (e) => {
                    if (e.target && e.target.closest(SELECTORS.TOGGLEAUTOMATICSEND)) {
                        e.preventDefault();
                        toggleAutomaticSend(automaticsendregion);
                    }
                });
            }
            const reportregion = document.querySelector(SELECTORS.REPORTREGION);
            if (reportregion) {
                reportregion.addEventListener('click', (e) => {
                    const target = e.target && e.target.closest(SELECTORS.REVOKEISSUE);
                    if (target) {
                        e.preventDefault();
                        const {issueid} = target.dataset;
                        revokeIssue(issueid);
                    }
                });
            }
        }
    };
});