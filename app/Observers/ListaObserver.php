<?php

namespace App\Observers;

use App\Models\Lista;
use App\Models\Log;

class ListaObserver
{
    public function created(Lista $item)
    {
        Log::crud($item, Log::ACTION_INSERT, 'Passageiro inserido');
    }

    public function updated(Lista $item)
    {
        Log::crud($item, Log::ACTION_UPDATE, 'Passageiro atualizado');
    }

    public function deleted(Lista $item)
    {
        Log::crud($item, Log::ACTION_DELETE, 'Passageiro deletado');
    }
}
