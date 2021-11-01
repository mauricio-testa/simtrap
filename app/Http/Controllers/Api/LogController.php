<?php

namespace App\Http\Controllers\Api;

use App\Models\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function index (Request $request)
    {
        $query = DB::table('logs')
            ->select('logs.*', 'users.name')
            ->leftJoin('users', 'logs.id_user','=','users.id')
            ->orderBy('created_at', 'DESC');

        if ('null' != $request->level)
        $query->where('logs.level', $request->level);

        if ('null' != $request->action)
        $query->where('logs.action', $request->action);

        if ($request->id_user > 0)
        $query->where('logs.id_user', $request->id_user);

        if ('null' != $request->context)
        $query->where('logs.context', $request->context);

        if ($request->context_id > 0)
        $query->where('logs.context_id', $request->context_id);

        return $query->paginate(config('constants.PAGINATION_SIZE'));
    }
}
