<?php

namespace App\Observers;

use App\Models\Viagem;
use App\Models\Log;

class ViagemObserver
{
    public function created(Viagem $item)
    {
        Log::crud($item, Log::ACTION_INSERT, 'Viagem cadastrada');
    }

    public function updated(Viagem $item)
    {
        Log::crud($item, Log::ACTION_UPDATE, 'Viagem alterada');
    }

    public function deleted(Viagem $item)
    {
        Log::crud($item, Log::ACTION_DELETE, 'Viagem deletada');
    }
}
