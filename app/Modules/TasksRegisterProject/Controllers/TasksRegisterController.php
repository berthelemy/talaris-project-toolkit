<?php

/**
 * TasksRegisterProject module controller: TasksRegisterController.
 */

namespace App\Modules\TasksRegisterProject\Controllers;


/**
 * Project tasks register controller.
 */
class TasksRegisterController extends BaseProjectRaidController
{
    /**
     * @return string
     */
    protected function moduleSlug(): string
    {
        return 'tasks_register_project';
    }

    /**
     * @return string
     */
    protected function moduleRouteSegment(): string
    {
        return 'tasks-register';
    }

    /**
     * @return string
     */
    protected function moduleTitleLangKey(): string
    {
        return 'Module.tasksRegisterTitle';
    }

    /**
     * @return string
     */
    protected function moduleDescriptionLangKey(): string
    {
        return 'Module.tasksRegisterDescription';
    }
}
