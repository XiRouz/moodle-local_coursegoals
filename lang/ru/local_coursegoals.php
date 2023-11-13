<?php

defined('MOODLE_INTERNAL') || die();

/**
 * @package    local_coursegoals
 * @copyright  2023 Lavrentev Semyon
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Цели курса';

// General
$string['compruleid'] = 'Выберите правило выполнения';
$string['coursechoice'] = 'Цель курса будет связана с выбранным курсом и его элементами.';
$string['crule_params'] = 'Параметры правила выполнения';
$string['displayname'] = 'Цели курса';
$string['displayedname'] = 'Отображаемое имя';
$string['hide_tasks_info'] = 'Спрятать детали заданий';
$string['shared'] = 'Общая';
$string['section'] = 'Секция';
$string['section_coursegoalid'] = 'Связанная цель';
$string['section_coursegoalid_select'] = 'Выберите связанную цель';
$string['select_sectionid'] = 'Секция';
$string['sections'] = 'Секции';
$string['sortorder'] = 'Порядок';
$string['show_tasks_info'] = 'Показать детали заданий';
$string['task'] = 'Задание';
$string['tasks'] = 'Задания';
$string['withoutsection'] = 'Без секции';

// Actions
$string['create_goal'] = 'Создать цель';
$string['edit_goal'] = 'Редактировать цель';
$string['delete_goal'] = 'Удалить цель';
$string['ays_delete_goal'] = '<b>Вы уверены, что хотите удалить эту цель курса?</b>';
$string['activate_goal'] = 'Активировать цель';
$string['activate_goal_explained'] = 'Активация цели ведёт к отображению цели на странице курса. Редактирование целей и заданий внутри них после активации НЕ рекомендуется. Активировать цель? ';
$string['pause_goal'] = 'Приостановить цель';
$string['pause_goal_explained'] = 'Приостановка цели останавливает подсчёт выполнения её заданий. Вы уверены?';
$string['sections_explained'] = 'Секции выполняют роль лейблов или ярлыков для группировки заданий при их рендере.';
$string['create_section'] = 'Создать секцию';
$string['edit_section'] = 'Редактировать секцию';
$string['delete_section'] = 'Удалить секцию';
$string['ays_delete_section'] = '<b>Вы уверены, что хотите удалить эту секцию? Это отвяжет все задания с данной секцией от неё!</b>';
$string['create_task'] = 'Создать задание';
$string['edit_task'] = 'Редактировать задание';
$string['delete_task'] = 'Удалить задание';
$string['ays_delete_task'] = '<b>Вы уверены, что хотите удалить это задание?</b>';

// Statuses
$string['status_active'] = 'Активно';
$string['status_inactive'] = 'Неактивно';
$string['status_paused'] = 'Приостановлено';

// Help strings
$string['displayedname_help'] = 'Это поле поддерживает плагины форматирования. Отображаемое имя будет использоваться для рендера везде, как краткое название.';
$string['formatstring_naming_help'] = 'Это поле поддерживает плагины форматирования.';
$string['section_coursegoalid_select_help'] = '
Привязка секций к целям опциональна. Если связь существует, такие секции будут отображаться только при создании заданий для конкретной цели.
Если секция "общая", она будет отображаться для создания в любых заданиях.';
$string['sections_explained_help'] = 'Секции выполняют роль лейблов или ярлыков для группировки заданий при их рендере.';
$string['sortorder_help'] = 'Порядок определяет последовательность секций при рендере. Чем меньше число - тем раньше оно отрендерится.';

// Errors
$string['error:choose_course'] = 'Выберите курс';
$string['error:choose_comprule'] = 'Выберите правило выполнения';

// Config
$string['config:enable_viewtab'] = 'Включить вкладку просмотра';
$string['config:enable_viewtab_descr'] = 'Добавляет включение вкладки на странице курса, которая отображает цели пользователям.';
$string['config:tab_render_header'] = 'Заголовок для рендера';
$string['config:tab_render_header_descr'] = 'Выберите заголовок, к которому будет привязан рендер вкладки целей.';
$string['config:index_page'] = 'Страница создания целей';

// Capabilities
$string['coursegoals:manage_all_goals'] = 'Управлять всеми целями';
$string['coursegoals:manage_goals_in_course'] = 'Управлять целями в курсе';
$string['coursegoals:complete_goals'] = 'Выполнять цели';

// Other
$string['course_header'] = 'Заголовок курса';
$string['after_course_navigation'] = 'После панели навигации в курсе (начало содержимого курса)';

