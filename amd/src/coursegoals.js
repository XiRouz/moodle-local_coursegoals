import ModalForm from 'core_form/modalform';
import {get_string as getString} from 'core/str';
import Notification from 'core/notification';

export const initCourseGoalTab = (parentelement) => {
    try {
        /* Move the widget to the header area */
        let _enrolmentstabs = document.querySelector(".sicblock-enrolments-tabs");
        let sb_parentnodes = "{{parentelement}}".split(',');
        for (let i in sb_parentnodes) {
            let sb_pageheader = document.querySelector(sb_parentnodes[i]);
            if (sb_pageheader) {
                sb_pageheader.appendChild(sb_enrolmentstabs);
                break;
            }
        }

        let sb_details_id = "sb_details";
        let sb_details_node = document.getElementById(sb_details_id);

        let sb_localDetailsIsOpen = window.localStorage.getItem("details-" + sb_details_id);
        if (sb_localDetailsIsOpen == "true" || sb_localDetailsIsOpen == null) {
            sb_details_node.setAttribute("open", "true");
        }

        sb_enrolmentstabs.style.display = "block";

        sb_details_node.addEventListener("toggle", function(e){
            let isOpen = event.target.getAttribute("open");
            window.localStorage.setItem("details-" + sb_details_id, typeof(isOpen) === "string" ? "true" : "false");
        });
    }
    catch (ignore) {}
};

