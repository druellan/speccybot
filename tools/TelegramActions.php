<?php
/**
 * Some handy tools for later use inside the commands
 */

namespace Longman\Actions;

use Longman\TelegramBot\Exception\TelegramLogException;

class Actions
{

    /**
     * List of actions for the current command
     * @var array
     */
    static protected $actionList;

    /**
     * Try to detect inline actions, filters and modificators
     *
     * @param $string command line
     * @return array proper alias + sanitized command line
     */
    public static function detect() {

    }

    public static function setActions($actions) {
        $this->actionList = $actions;
    }

}
