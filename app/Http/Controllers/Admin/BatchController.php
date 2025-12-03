<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Support\Carbon;

class BatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-batch');
    }

    /**
     * Display batch/lot list with expiry alerts and FEFO pick suggestion.
     */
    public function index()
    {
        $title = 'batches';
        $today = Carbon::today();
        $nearExpiryThreshold = $today->copy()->addDays(30);

        $purchases = Purchase::with(['supplier', 'category'])
            ->whereNotNull('batch_no')
            ->orderBy('expiry_date')
            ->get()
            ->map(function ($purchase) use ($today, $nearExpiryThreshold) {
                $expiry = $this->parseExpiry($purchase->expiry_date);
                $purchase->expiry_at = $expiry;
                $purchase->days_left = $expiry ? $today->diffInDays($expiry, false) : null;

                if (!$expiry) {
                    $status = 'unknown';
                } elseif ($expiry->isPast()) {
                    $status = 'expired';
                } elseif ($expiry->lte($nearExpiryThreshold)) {
                    $status = 'near';
                } else {
                    $status = 'ok';
                }

                $purchase->status = $status;
                return $purchase;
            });

        // Determine FEFO pick (earliest non-expired per product).
        $fefoPickIds = [];
        foreach ($purchases->groupBy('product') as $items) {
            $pick = $items->filter(function ($item) {
                return $item->expiry_at && !$item->expiry_at->isPast();
            })->sortBy('expiry_at')->first();

            if (!$pick) {
                $pick = $items->sortBy(function ($item) {
                    return $item->expiry_at ?: Carbon::maxValue();
                })->first();
            }

            if ($pick) {
                $fefoPickIds[$pick->id] = true;
            }
        }

        $batches = $purchases->map(function ($purchase) use ($fefoPickIds) {
            $purchase->fefo_pick = isset($fefoPickIds[$purchase->id]);
            return $purchase;
        });

        $expiredCount = $batches->where('status', 'expired')->count();
        $nearExpiryCount = $batches->where('status', 'near')->count();

        return view('admin.batches.index', compact(
            'title',
            'batches',
            'expiredCount',
            'nearExpiryCount'
        ));
    }

    /**
     * Parse expiry date safely, returning null on invalid formats.
     */
    private function parseExpiry(?string $value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
