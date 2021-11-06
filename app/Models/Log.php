<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Log extends Model
{
    public $timestamps = false;

    const LEVEL_CRUD = 'CRUD';
    const LEVEL_EXCEPTION = 'EXCEPTION';
    const LEVEL_INFO = 'INFO';

    const ACTION_INSERT = 'INSERT';
    const ACTION_UPDATE = 'UPDATE';
    const ACTION_DELETE = 'DELETE';
    const ACTION_EXPORT = 'EXPORT';

    public static function add ($context, $context_id, $level = 'INFO', $action = null, $message = '', $payload = []) {
        $log = new Log();
        $log->context    = $context;
        $log->context_id = $context_id;
        $log->level      = $level;
        $log->action     = $action;
        $log->message    = $message;
        $log->payload    = json_encode($payload);
        $log->id_user    = Auth::user()->id;
        $log->id_unidade = Auth::user()->id_unidade;
        $log->save();
    }

    public static function crud($item, $action, $message = '') {
        Log::add($item->getTable(), $item->id, Log::LEVEL_CRUD, $action, $message, $item);
    }
}
