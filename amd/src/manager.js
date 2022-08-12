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
 * @copyright   2020 Mikel Martín <mikel@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

"use strict";

import Notification from 'core/notification';
import {get_strings as getStrings} from 'core/str';
import Templates from 'core/templates';
import Ajax from 'core/ajax';
import Pending from 'core/pending';

/** @type {Object} The list of selectors for the coursecertificate module. */
const SELECTORS = {
    AUTOMATICSENDREGION: "[data-region='automaticsend-alert']",
    HIDDENWARNING: ".hidden-warning",
    NOAUTOSENDINFO: ".noautosend-info",
    REPORTREGION: "[data-region='issues-report']",
    TOGGLEAUTOMATICSEND: "[data-action='toggle-automaticsend']",
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
};

/**
 * Show/Hide selector.
 *
 * @param {string} selector
 * @param {boolean} visible
 */
function setVisibility(selector, visible) {
    document.querySelector(selector).classList.toggle('invisible', !visible);
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
    let showhiddenwarning, shownoautosendinfo, pendingPromise;

    // Build list of strings.
    const strings = [
        {'key': 'confirmation', component: 'admin'},
        {'key': 'confirm', component: 'moodle'},
    ];
    if (newstatus) {
        strings.push({'key': 'enableautomaticsendpopup', component: 'coursecertificate'});
    } else {
        strings.push({'key': 'disableautomaticsend', component: 'coursecertificate'});
    }

    pendingPromise = new Pending('mod_coursecertificate/manager:toggleAutomaticSendgetStrings');
    getStrings(strings).then(([title, saveLabel, question]) => {
        pendingPromise.resolve();
        return Notification.saveCancelPromise(title, question, saveLabel);
    }).then(() => {
        pendingPromise = new Pending('mod_coursecertificate/manager:toggleAutomaticSend');
        // Show loading template.
        setVisibility(SELECTORS.LOADING, true);
        // Call to webservice.
        return Ajax.call([{
            methodname: SERVICES.UPDATEAUTOMATICSEND,
            args: {id: certificateid, automaticsend: newstatus}
        }])[0];
    }).then((result) => {
        ({showhiddenwarning, shownoautosendinfo} = result);
        return Templates.render(TEMPLATES.AUTOMATICSENDALERT,
            {certificateid: certificateid, automaticsend: newstatus}, '');
    }).then((html) => {
        Templates.replaceNodeContents(automaticsendregion, html, '');
        setVisibility(SELECTORS.HIDDENWARNING, showhiddenwarning);
        setVisibility(SELECTORS.NOAUTOSENDINFO, shownoautosendinfo);
        return pendingPromise.resolve();
    }).catch((e) => {
        if (e.type === 'modal-save-cancel:cancel') {
            // Clicked cancel.
            return;
        }
        Notification.exception(e);
    });
}

/**
 * Initialise module
 */
export function init() {
    const automaticsendregion = document.querySelector(SELECTORS.AUTOMATICSENDREGION);
    if (automaticsendregion) {
        automaticsendregion.addEventListener('click', (e) => {
            if (e.target.closest(SELECTORS.TOGGLEAUTOMATICSEND)) {
                e.preventDefault();
                toggleAutomaticSend(automaticsendregion);
            }
        });
    }
}
