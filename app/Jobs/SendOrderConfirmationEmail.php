<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    /**
     * No real mailer is wired up for this task (per the brief). We just log
     * what would have been sent, which is enough to prove the job ran async.
     */
    public function handle(): void
    {
        $this->order->loadMissing('customer');

        Log::info(sprintf(
            'Order confirmation email sent to %s for order #%d (grand total: %s)',
            $this->order->customer->email,
            $this->order->id,
            $this->order->grand_total
        ));
    }
}
