<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use QCod\AppSettings\Setting\AppSettings;
use App\Models\StockMovement;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'purchases';
        if($request->ajax()){
            $purchases = Purchase::get();
            return DataTables::of($purchases)
                ->addColumn('product',function($purchase){
                    $image = '';
                    if(!empty($purchase->image)){
                        $image = '<span class="avatar avatar-sm mr-2">
						<img class="avatar-img" src="'.asset("storage/purchases/".$purchase->image).'" alt="product">
					    </span>';
                    }                 
                    return $purchase->product.' ' . $image;
                })
                ->addColumn('category',function($purchase){
                    if(!empty($purchase->category)){
                        return $purchase->category->name;
                    }
                })
                ->addColumn('cost_price',function($purchase){
                    return $purchase->cost_price;
                })
                ->addColumn('supplier',function($purchase){
                    return $purchase->supplier->name;
                })
                ->addColumn('expiry_date',function($purchase){
                    return date_format(date_create($purchase->expiry_date),'d M, Y');
                })
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="'.route("purchases.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('purchases.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                    $returnBtn = '<button class="btn btn-warning btn-sm purchase-return-btn" data-id="'.$row->id.'" data-qty="'.$row->quantity.'" title="Retur ke supplier"><i class="fas fa-undo-alt"></i></button>';
                    $adjustBtn = '<button class="btn btn-info btn-sm adjust-btn text-white" data-id="'.$row->id.'" data-qty="'.$row->quantity.'" title="Penyesuaian stok"><i class="fas fa-balance-scale"></i></button>';
                    if (!auth()->user()->hasPermissionTo('edit-purchase')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-purchase')) {
                        $deletebtn = '';
                    }
                    $btn = $editbtn.' '.$deletebtn.' '.$returnBtn.' '.$adjustBtn;
                    return $btn;
                })
                ->rawColumns(['product','action'])
                ->make(true);
        }
        return view('admin.purchases.index',compact(
            'title'
        ));
    }

    /**
     * Lightweight search for purchases/batches (used by log stok filter).
     */
    public function search(Request $request)
    {
        $q = $request->query('q', '');
        if ($q === '') {
            return response()->json([]);
        }
        $rows = Purchase::select('id','product','batch_no','quantity')
            ->whereNotNull('product')
            ->where('product', 'like', "%{$q}%")
            ->orderBy('product')
            ->limit(20)
            ->get()
            ->map(function($p){
                return [
                    'id' => $p->id,
                    'text' => $p->product . ' | Batch ' . ($p->batch_no ?? '-') . ' | Stok ' . ($p->quantity ?? 0),
                ];
            });
        return response()->json($rows);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'create purchase';
        $categories = Category::get();
        $suppliers = Supplier::get();
        return view('admin.purchases.create',compact(
            'title','categories','suppliers'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'product'=>'required|max:200',
            'category'=>'required',
            'cost_price'=>'required|min:1',
            'quantity'=>'required|min:1',
            'expiry_date'=>'required',
            'supplier'=>'required',
            'image'=>'file|image|mimes:jpg,jpeg,png,gif',
            'batch_no' => 'required|string|max:100',
            'rack_location' => 'nullable|string|max:100',
            // optional selling fields
            'price_retail' => 'nullable|numeric|min:0',
            'price_wholesale' => 'nullable|numeric|min:0',
            'price_insurance' => 'nullable|numeric|min:0',
            'promo_name' => 'nullable|string|max:200',
            'promo_percent' => 'nullable|numeric|min:0|max:100',
            'bundle_qty' => 'nullable|integer|min:1',
            'bundle_price' => 'nullable|numeric|min:0',
        ]);
        $imageName = null;
        if($request->hasFile('image')){
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('storage/purchases'), $imageName);
        }
        $purchase = Purchase::create([
            'product'=>$request->product,
            'category_id'=>$request->category,
            'supplier_id'=>$request->supplier,
            'cost_price'=>$request->cost_price,
            'quantity'=>$request->quantity,
            'expiry_date'=>$request->expiry_date,
            'image'=>$imageName,
            'batch_no'=>$request->batch_no,
            'rack_location'=>$request->rack_location,
        ]);

        // Ensure a product row exists for pricing / POS
        $purchase->purchaseProduct()->updateOrCreate(
            ['purchase_id' => $purchase->id],
            [
                'price_retail' => $request->input('price_retail', $request->cost_price),
                'price_wholesale' => $request->input('price_wholesale'),
                'price_insurance' => $request->input('price_insurance'),
                'promo_name' => $request->input('promo_name'),
                'promo_percent' => $request->input('promo_percent'),
                'bundle_qty' => $request->input('bundle_qty'),
                'bundle_price' => $request->input('bundle_price'),
            ]
        );

        StockMovement::create([
            'purchase_id' => $purchase->id,
            'user_id' => $request->user()->id ?? null,
            'type' => 'in',
            'quantity' => (int)$request->quantity,
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
            'note' => 'Stock masuk dari pembelian',
        ]);

        $notifications = notify("Purchase has been added");
        return redirect()->route('purchases.index')->with($notifications);
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Purchase $purchase
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchase $purchase)
    {
        $title = 'edit purchase';
        $categories = Category::get();
        $suppliers = Supplier::get();
        return view('admin.purchases.edit',compact(
            'title','purchase','categories','suppliers'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Purchase $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Purchase $purchase)
    {
        $this->validate($request,[
            'product'=>'required|max:200',
            'category'=>'required',
            'cost_price'=>'required|min:1',
            'quantity'=>'required|min:1',
            'expiry_date'=>'required',
            'supplier'=>'required',
            'image'=>'file|image|mimes:jpg,jpeg,png,gif',
            'batch_no' => 'required|string|max:100',
            'rack_location' => 'nullable|string|max:100',
            'price_retail' => 'nullable|numeric|min:0',
            'price_wholesale' => 'nullable|numeric|min:0',
            'price_insurance' => 'nullable|numeric|min:0',
            'promo_name' => 'nullable|string|max:200',
            'promo_percent' => 'nullable|numeric|min:0|max:100',
            'bundle_qty' => 'nullable|integer|min:1',
            'bundle_price' => 'nullable|numeric|min:0',
        ]);
        $imageName = $purchase->image;
        if($request->hasFile('image')){
            $imageName = time().'.'.$request->image->extension();
            $request->image->move(public_path('storage/purchases'), $imageName);
        }
        $oldQty = (int) $purchase->quantity;
        $purchase->update([
            'product'=>$request->product,
            'category_id'=>$request->category,
            'supplier_id'=>$request->supplier,
            'cost_price'=>$request->cost_price,
            'quantity'=>$request->quantity,
            'expiry_date'=>$request->expiry_date,
            'image'=>$imageName,
            'batch_no'=>$request->batch_no,
            'rack_location'=>$request->rack_location,
        ]);

        $purchase->purchaseProduct()->updateOrCreate(
            ['purchase_id' => $purchase->id],
            [
                'price_retail' => $request->input('price_retail', $request->cost_price),
                'price_wholesale' => $request->input('price_wholesale'),
                'price_insurance' => $request->input('price_insurance'),
                'promo_name' => $request->input('promo_name'),
                'promo_percent' => $request->input('promo_percent'),
                'bundle_qty' => $request->input('bundle_qty'),
                'bundle_price' => $request->input('bundle_price'),
            ]
        );

        $delta = (int)$request->quantity - $oldQty;
        if ($delta !== 0) {
            StockMovement::create([
                'purchase_id' => $purchase->id,
                'user_id' => $request->user()->id ?? null,
                'type' => $delta > 0 ? 'in' : 'adjust',
                'quantity' => abs($delta),
                'reference_type' => 'purchase_update',
                'reference_id' => $purchase->id,
                'note' => 'Penyesuaian stok saat update pembelian',
            ]);
        }

        $notifications = notify("Purchase has been updated");
        return redirect()->route('purchases.index')->with($notifications);
    }

    public function reports(){
        $title ='purchase reports';
        return view('admin.purchases.reports',compact('title'));
    }

    public function generateReport(Request $request){
        $this->validate($request,[
            'from_date' => 'required',
            'to_date' => 'required'
        ]);
        $title = 'purchases reports';
        $purchases = Purchase::whereBetween(DB::raw('DATE(created_at)'), array($request->from_date, $request->to_date))->get();
        return view('admin.purchases.reports',compact(
            'purchases','title'
        ));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        return Purchase::findOrFail($request->id)->delete();
    }

    /**
     * Preview purchase batch detail.
     */
    public function preview(Purchase $purchase)
    {
        $purchase->load(['supplier','category']);
        return view('admin.stock-tools.partials.purchase-preview', compact('purchase'));
    }
}
