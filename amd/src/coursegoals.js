import ModalForm from 'core_form/modalform';
import {get_string as getString} from 'core/str';
import Notification from 'core/notification';

export const initCourseGoalsTab = (parentelement) => {
    try {
        /* Move the widget to the header area */
        let cg_tab = document.querySelector("#cg_goals_tab");
        let cg_parentnodes = parentelement.split(',');
        for (let i in cg_parentnodes) {
            let cg_pageheader = document.querySelector(cg_parentnodes[i]);
            if (cg_pageheader) {
                cg_pageheader.appendChild(cg_tab);
                break;
            }
        }

        let cg_details_id = "cg_details_maintab";
        let cg_details_node = document.getElementById(cg_details_id);

        let cg_localDetailsAreOpen = window.localStorage.getItem("details-" + cg_details_id);
        if (cg_localDetailsAreOpen == "true" || cg_localDetailsAreOpen == null) {
            cg_details_node.setAttribute("open", "true");
        }

        cg_tab.style.display = "block";

        cg_details_node.addEventListener("toggle", function(e){
            let isOpen = event.target.getAttribute("open");
            window.localStorage.setItem("details-" + cg_details_id, typeof(isOpen) === "string" ? "true" : "false");
        });
    }
    catch (ignore) {}
};

export const setupModals = (modals) => {
    modals.forEach((modaldata) => {
        let elements = document.querySelectorAll(modaldata.elementSelector);
        elements.forEach((element) => {
            element.addEventListener('click', function (e) {
                let title = this.getAttribute('data-title') || this.innerHTML;
                let formClass = modaldata.formClass;
                let dataID = this.getAttribute('data-id') || null;
                let dataParentID = this.getAttribute('data-parentid') || null;
                let action = this.getAttribute('data-action') || null;
                let form = new ModalForm({
                    formClass,
                    args: {
                        action: action,
                        dataid: dataID,
                        dataparentid: dataParentID,
                    },
                    modalConfig: {
                        title: title,
                        large: true,
                    },
                    saveButtonText: title,
                    returnFocus: e.target,
                });
                form.addEventListener(form.events.FORM_SUBMITTED, (event) => {
                    if (event.detail.result) {
                        if (event.detail.returnaction) {
                            // todo
                        }
                        if (event.detail.redirecturl) {
                            document.location = event.detail.redirecturl;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        Notification.addNotification({
                            type: 'error',
                            message: event.detail.errors.join('<br>')
                        });
                    }
                });
                form.show();
            });
        });
    });
};

