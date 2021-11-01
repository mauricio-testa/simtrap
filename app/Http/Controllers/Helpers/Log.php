<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Log as LogModel;

class Log extends Controller
{
    static $levelCrud = 'CRUD';
    static $levelException = 'EXCEPTION';
    static $levelInfo = 'INFO';

    static $actionInsert = 'INSERT';
    static $actionUpdate = 'UPDATE';
    static $actionDelete = 'DELETE';
    static $actionExport = 'EXPORT';

    public static function add ($context, $context_id, $level = 'INFO', $action = null, $message = '', $payload = []) {
        $log = new LogModel();
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

    public static function CRUDInsert ($table, $primary, $message = '', $payload = []) {
        self::add($table, $primary, self::$levelCrud, self::$actionInsert, $message, $payload);
    }
    public static function CRUDUpdate ($table, $primary, $message = '', $payload = []) {
        self::add($table, $primary, self::$levelCrud, self::$actionUpdate, $message, $payload);
    }
    public static function CRUDDelete ($table, $primary, $message = '', $payload = []) {
        self::add($table, $primary, self::$levelCrud, self::$actionDelete, $message, $payload);
    }

    public static function Exception ($message, $payload) {
        self::add('', null, self::$levelException, null, $message, $payload);
    }
}
