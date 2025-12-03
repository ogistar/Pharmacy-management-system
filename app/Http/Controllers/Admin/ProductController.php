<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use QCod\AppSettings\Setting\AppSettings;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-products')->only(['index','create','edit']);
        $this->middleware('permission:create-product')->only(['store']);
        $this->middleware('permission:edit-product')->only(['update']);
        $this->middleware('permission:destroy-product')->only(['destroy']);
        $this->middleware('permission:view-expired-products')->only(['expired']);
        $this->middleware('permission:view-outstock-products')->only(['outstock']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'products';
        if ($request->ajax()) {
            $products = Product::latest();
            return DataTables::of($products)
                ->addColumn('product',function($product){
                    $image = '';
                    if(!empty($product->purchase)){
                        $image = null;
                        if(!empty($product->purchase->image)){
                            $image = '<span class="avatar avatar-sm mr-2">
                            <img class="avatar-img" src="'.asset("storage/purchases/".$product->purchase->image).'" alt="image">
                            </span>';
                        }
                        return $product->purchase->product. ' ' . $image;
                    }
                })

                ->addColumn('category',function($product){
                    $category = null;
                    if(!empty($product->purchase->category)){
                        $category = $product->purchase->category->name;
                    }
                    return $category;
                })
                ->addColumn('price',function($product){
                    $price = $product->price_retail ?? $product->price;
                    return $price;
                })
                ->addColumn('discount',function($product){
                    return $product->discount ?? 0;
                })
                ->addColumn('quantity',function($product){
                    if(!empty($product->purchase)){
                        return $product->purchase->quantity;
                    }
                })
                ->addColumn('expiry_date',function($product){
                    if(!empty($product->purchase)){
                        return date_format(date_create($product->purchase->expiry_date),'d M, Y');
                    }
                })
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="'.route("products.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('products.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                    if (!auth()->user()->hasPermissionTo('edit-product')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-purchase')) {
                        $deletebtn = '';
                    }
                    $btn = $editbtn.' '.$deletebtn;
                    return $btn;
                })
                ->rawColumns(['product','action'])
                ->make(true);
        }
        return view('admin.products.index',compact(
            'title'
        ));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'add product';
        $purchases = Purchase::get();
        return view('admin.products.create',compact(
            'title','purchases'
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
            'price_retail'=>'required|numeric|min:0',
            'price_wholesale'=>'nullable|numeric|min:0',
            'price_insurance'=>'nullable|numeric|min:0',
            'discount'=>'nullable|numeric|min:0',
            'promo_percent' => 'nullable|numeric|min:0|max:100',
            'bundle_qty' => 'nullable|integer|min:1',
            'bundle_price' => 'nullable|numeric|min:0',
            'description'=>'nullable|max:255',
        ]);
        $priceRetail = $request->price_retail;
        $legacyPrice = $priceRetail; // maintain legacy price column for backward compatibility
        if($request->discount >0){
           $legacyPrice = $request->discount * $priceRetail;
        }
        Product::create([
            'purchase_id'=>$request->product,
            'price'=>$legacyPrice,
            'price_retail'=>$priceRetail,
            'price_wholesale'=>$request->price_wholesale,
            'price_insurance'=>$request->price_insurance,
            'discount'=>$request->discount,
            'description'=>$request->description,
            'promo_name'=>$request->promo_name,
            'promo_percent'=>$request->promo_percent ?? 0,
            'bundle_qty'=>$request->bundle_qty,
            'bundle_price'=>$request->bundle_price,
        ]);
        $notification = notify("Product has been added");
        return redirect()->route('products.index')->with($notification);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $title = 'edit product';
        $purchases = Purchase::get();
        return view('admin.products.edit',compact(
            'title','product','purchases'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $this->validate($request,[
            'product'=>'required|max:200',
            'price_retail'=>'required|numeric|min:0',
            'price_wholesale'=>'nullable|numeric|min:0',
            'price_insurance'=>'nullable|numeric|min:0',
            'discount'=>'nullable|numeric|min:0',
            'promo_percent' => 'nullable|numeric|min:0|max:100',
            'bundle_qty' => 'nullable|integer|min:1',
            'bundle_price' => 'nullable|numeric|min:0',
            'description'=>'nullable|max:255',
        ]);

        $priceRetail = $request->price_retail;
        $legacyPrice = $priceRetail;
        if($request->discount >0){
           $legacyPrice = $request->discount * $priceRetail;
        }
       $product->update([
            'purchase_id'=>$request->product,
            'price'=>$legacyPrice,
            'price_retail'=>$priceRetail,
            'price_wholesale'=>$request->price_wholesale,
            'price_insurance'=>$request->price_insurance,
            'discount'=>$request->discount,
            'description'=>$request->description,
            'promo_name'=>$request->promo_name,
            'promo_percent'=>$request->promo_percent ?? 0,
            'bundle_qty'=>$request->bundle_qty,
            'bundle_price'=>$request->bundle_price,
        ]);
        $notification = notify('product has been updated');
        return redirect()->route('products.index')->with($notification);
    }

     /**
     * Display a listing of expired resources.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function expired(Request $request){
        $title = "expired Products";
        if($request->ajax()){
            $products = Product::whereHas('purchase', function($q){
                $q->whereDate('expiry_date','<=', Carbon::now());
            })->with('purchase.category')->get();

            return DataTables::of($products)
                ->addColumn('product',function($product){
                    $image = '';
                    if(!empty($product->purchase) && !empty($product->purchase->image)){
                        $image = '<span class="avatar avatar-sm mr-2">
                        <img class="avatar-img" src="'.asset("storage/purchases/".$product->purchase->image).'" alt="image">
                        </span>';
                    }
                    $name = $product->purchase->product ?? $product->description ?? 'Produk';
                    return $name.' '.$image;
                })
                ->addColumn('category',function($product){
                    return $product->purchase->category->name ?? null;
                })
                ->addColumn('price',function($product){
                    return $product->price_retail ?? $product->price;
                })
                ->addColumn('discount',function($product){
                    return $product->discount ?? 0;
                })
                ->addColumn('quantity',function($product){
                    return $product->purchase->quantity ?? 0;
                })
                ->addColumn('expiry_date',function($product){
                    return $product->purchase?->expiry_date ? date_format(date_create($product->purchase->expiry_date),'d M, Y') : null;
                })
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="'.route("products.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('products.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                    if (!auth()->user()->hasPermissionTo('edit-product')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-purchase')) {
                        $deletebtn = '';
                    }
                    return trim($editbtn.' '.$deletebtn);
                })
                ->rawColumns(['product','action'])
                ->make(true);
        }

        return view('admin.products.expired',compact(
            'title',
        ));
    }

    /**
     * Display a listing of out of stock resources.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function outstock(Request $request){
        $title = "outstocked Products";
        if($request->ajax()){
            $products = Product::whereHas('purchase', function($q){
                return $q->where('quantity', '<=', 0);
            })->with('purchase.category')->get();
            return DataTables::of($products)
                ->addColumn('product',function($product){
                    $image = '';
                    if(!empty($product->purchase)){
                        $image = null;
                        if(!empty($product->purchase->image)){
                            $image = '<span class="avatar avatar-sm mr-2">
                            <img class="avatar-img" src="'.asset("storage/purchases/".$product->purchase->image).'" alt="image">
                            </span>';
                        }
                        return ($product->purchase->product ?? $product->description ?? 'Produk').' ' . $image;
                    }
                })
               
                ->addColumn('category',function($product){
                    $category = null;
                    if(!empty($product->purchase->category)){
                        $category = $product->purchase->category->name;
                    }
                    return $category;
                })
                ->addColumn('price',function($product){
                    $price = $product->price_retail ?? $product->price;
                    return $price;
                })
                ->addColumn('discount',function($product){
                    return $product->discount ?? 0;
                })
                ->addColumn('quantity',function($product){
                    if(!empty($product->purchase)){
                        return $product->purchase->quantity;
                    }
                })
                ->addColumn('expiry_date',function($product){
                    if(!empty($product->purchase)){
                        return date_format(date_create($product->purchase->expiry_date),'d M, Y');
                    }
                })
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="'.route("products.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('products.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                    if (!auth()->user()->hasPermissionTo('edit-product')) {
                        $editbtn = '';
                    }
                    if (!auth()->user()->hasPermissionTo('destroy-purchase')) {
                        $deletebtn = '';
                    }
                    $btn = $editbtn.' '.$deletebtn;
                    return $btn;
                })
                ->rawColumns(['product','action'])
                ->make(true);
        }
        $product = Purchase::where('quantity', '<=', 0)->first();
        return view('admin.products.outstock',compact(
            'title',
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
        return Product::findOrFail($request->id)->delete();
    }
}
