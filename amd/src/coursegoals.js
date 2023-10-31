import ModalForm from 'core_form/modalform';
import {get_string as getString} from 'core/str';
import * as Notification from 'core/notification';

const SELECTORS = {
    COURSEGOALS_TAB_ID: '#cg_goals_tab',
    DETAILS_ID: 'cg_details_maintab',
    TASK_CHECKBOX_CLASS: '.cg-taskcb',
    TASK_DETAILS_ACTIVE: 'data-td-active',
};

export const initCourseGoalsTab = (parentelement, appendorder = 'last') => {
    try {
        /* Move the widget to the header area */
        let cg_tab = document.querySelector(SELECTORS.COURSEGOALS_TAB_ID);
        let cg_parentnodes = parentelement.split(',');
        for (let i in cg_parentnodes) {
            let cg_pageheader = document.querySelector(cg_parentnodes[i]);
            if (cg_pageheader) {
                if (appendorder === 'first') {
                    cg_pageheader.prepend(cg_tab);
                } else if (appendorder === 'last') {
                    cg_pageheader.appendChild(cg_tab);
                } else {
                    cg_pageheader.appendChild(cg_tab);
                }
                break;
            }
        }

        let cg_details_id = SELECTORS.DETAILS_ID;
        let cg_details_node = document.getElementById(cg_details_id);

        let cg_localDetailsAreOpen = window.localStorage.getItem("details-" + cg_details_id);
        if (cg_localDetailsAreOpen == "true" || cg_localDetailsAreOpen == null) {
            cg_details_node.setAttribute("open", "true");
        }

        cg_tab.style.display = "block"; // show tab after it is ready

        cg_details_node.addEventListener("toggle", function(e){
            let isOpen = event.target.getAttribute("open");
            window.localStorage.setItem("details-" + cg_details_id, typeof(isOpen) === "string" ? "true" : "false");
        });

        let taskCheckboxDivs = document.querySelectorAll(SELECTORS.TASK_CHECKBOX_CLASS);
        taskCheckboxDivs.forEach((cbdiv) => {
            let taskdetailsdiv = document.querySelector('[data-taskref="'+cbdiv.dataset.taskid+'"]');
            cbdiv.addEventListener('mouseenter', function(e) {
                let activetaskdetails = document.querySelector('['+SELECTORS.TASK_DETAILS_ACTIVE+'="active"]');
                if (activetaskdetails) {
                    activetaskdetails.style.display = 'none';
                    activetaskdetails.removeAttribute(SELECTORS.TASK_DETAILS_ACTIVE);
                }
                taskdetailsdiv.style.display = 'block';
                taskdetailsdiv.setAttribute(SELECTORS.TASK_DETAILS_ACTIVE, 'active')
            });

            // cbdiv.addEventListener('mouseleave', function(e) {
            //     taskdetailsdiv.style.display = 'none';
            // });
        });
    }
    catch (ignore) {}
};

export const setupTaskModalForm = (elementSelector, formClass) => {
    const elements = document.querySelectorAll(elementSelector);
    elements.forEach((element) => {
        element.addEventListener('click', function (e) {
            e.preventDefault();
            let action = e.target.getAttribute('data-action');
            let title = this.getAttribute('data-title') || this.innerHTML;
            let coursegoalid = this.getAttribute('data-coursegoalid');
            let taskid = this.getAttribute('data-taskid');

            const form = new ModalForm({
                formClass,
                args: {
                    taskid: taskid,
                    coursegoalid: coursegoalid,
                    action: action,
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
        })
    });
};

export const setupModals = (modals) => {
    modals.forEach((modaldata) => {
        console.log('setupModalsDeprecated, '+ modaldata.elementSelector);
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

