<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use Carbon\Carbon;
use App\Notifications\ExpiryAlert;

class NotificationController extends Controller
{
    public function markAsRead(){
        auth()->user()->unreadNotifications->markAsRead();
        $notification = notify('Notifications marked as read');
        return back()->with($notification);
    }

    public function read(){
        auth()->user()->unreadNotifications->markAsRead();
        $notification = notify('Notification marked as read');
        return back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        auth()->user()->notifications()->delete();
        $notification = notify('Notification has been deleted');
        return back()->with($notification);
    }

    /**
     * Generate expiry notifications for purchases expiring <=7 days.
     */
    public function refreshExpiry()
    {
        $user = auth()->user();
        $soon = Carbon::now()->addDays(7)->toDateString();
        $purchases = Purchase::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $soon)
            ->where('quantity', '>', 0)
            ->get();

        foreach ($purchases as $purchase) {
            $exists = $user->notifications()
                ->where('type', ExpiryAlert::class)
                ->where('data->purchase_id', $purchase->id)
                ->exists();
            if (!$exists) {
                $user->notify(new ExpiryAlert($purchase));
            }
        }

        $notification = notify('Pemberitahuan ED < 7 hari telah diperbarui');
        return back()->with($notification);
    }
}
