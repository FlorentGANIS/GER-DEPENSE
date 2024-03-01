<?php

namespace App\Observers;

use App\Models\Envelope;

class EnvelopeObserver
{
    /**
     * Handle the Envelope "created" event.
     */
    public function created(Envelope $envelope): void
    {
        //
    }

    /**
     * Handle the Envelope "updated" event.
     */
    public function updated(Envelope $envelope): void
    {
        //
    }

    /**
     * Handle the Envelope "deleted" event.
     */
    public function deleted(Envelope $envelope): void
    {
        //
    }

    /**
     * Handle the Envelope "restored" event.
     */
    public function restored(Envelope $envelope): void
    {
        //
    }

    /**
     * Handle the Envelope "force deleted" event.
     */
    public function forceDeleted(Envelope $envelope): void
    {
        //
    }
}
