<?php

namespace App\Core\Events;

use App\Modules\Inventory\Models\StockTransaction;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a stock transaction is posted.
 * Other modules can listen to this to react to inventory changes.
 */
class StockPosted
{
    use Dispatchable, SerializesModels;

    public StockTransaction $transaction;

    public function __construct(StockTransaction $transaction)
    {
        $this->transaction = $transaction;
    }
}
