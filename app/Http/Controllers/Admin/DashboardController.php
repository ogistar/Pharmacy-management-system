<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use App\Models\SaleItem;
use App\Models\Category;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-reports');
    }

    public function index(Request $request){
        $title = 'dashboard';
        $days = max((int) $request->query('days', 7), 1);
        $startInput = $request->query('start_date');
        $endInput = $request->query('end_date');

        try {
            if ($startInput && $endInput) {
                $startDate = Carbon::parse($startInput)->startOfDay();
                $endDate = Carbon::parse($endInput)->endOfDay();
            } else {
                $endDate = Carbon::now()->endOfDay();
                $startDate = (clone $endDate)->startOfDay()->subDays($days - 1);
            }
        } catch (\Exception $e) {
            $endDate = Carbon::now()->endOfDay();
            $startDate = (clone $endDate)->startOfDay()->subDays($days - 1);
        }

        $rangeLabel = $startDate->toDateString() . ' s/d ' . $endDate->toDateString();

        $invoiceRangeQuery = Invoice::whereBetween('created_at', [$startDate, $endDate]);
        $latest_sales = (clone $invoiceRangeQuery)->with('patient')->orderBy('created_at','desc')->limit(10)->get();
        $today_sales = (clone $invoiceRangeQuery)->sum('total_amount');
        $total_sales = (clone $invoiceRangeQuery)->count();

        $total_purchases = Purchase::whereBetween('created_at', [$startDate, $endDate])->count();
        $total_categories = Category::count();
        $total_suppliers = Supplier::count();
        
        $pieChart = app()->chartjs
                ->name('pieChart')
                ->type('pie')
                ->size(['width' => 400, 'height' => 200])
                ->labels(['Purchases (range)', 'Suppliers (total)','Invoices (range)'])
                ->datasets([
                    [
                        'backgroundColor' => ['#FF6384', '#36A2EB','#7bb13c'],
                        'hoverBackgroundColor' => ['#FF6384', '#36A2EB','#7bb13c'],
                        'data' => [$total_purchases, $total_suppliers,$total_sales]
                    ]
                ])
                ->options([]);
        
        $total_expired_products = Purchase::whereDate('expiry_date', '<=', Carbon::now()->toDateString())->count();
        return view('admin.dashboard',compact(
            'title','pieChart','total_expired_products',
            'latest_sales','today_sales','total_categories',
            'startDate','endDate','days','rangeLabel'
        ));
    }
}
