<?php

namespace Database\Seeders;

use App\Models\CashSession;
use App\Models\Category;
use App\Models\Compound;
use App\Models\CompoundItem;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\StockAdjustment;
use App\Models\StockOpname;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\PrescriptionItemComponent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PharmacySampleSeeder extends Seeder
{
    /**
     * Seed sample data across pharmacy modules.
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ([
            PrescriptionItemComponent::class,
            PrescriptionItem::class,
            Prescription::class,
            SaleItem::class,
            SaleReturn::class,
            Invoice::class,
            ReceivablePayment::class,
            Receivable::class,
            StockAdjustment::class,
            StockOpname::class,
            StockTransfer::class,
            PurchaseReturn::class,
            CashSession::class,
            CompoundItem::class,
            Compound::class,
            Product::class,
            Purchase::class,
            Category::class,
            Supplier::class,
            Patient::class,
        ] as $model) {
            if (class_exists($model)) {
                $model::truncate();
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $admin = User::first();

        // Master data
        $analgesik = Category::create(['name' => 'Analgesik']);
        $antibiotik = Category::create(['name' => 'Antibiotik']);
        $vitamin = Category::create(['name' => 'Vitamin']);
        $respiratory = Category::create(['name' => 'Respirasi']);
        $digestive = Category::create(['name' => 'Gastrointestinal']);
        $antihistamin = Category::create(['name' => 'Antihistamin']);

        $supplierA = Supplier::create([
            'name' => 'PT Sehat Sentosa',
            'phone' => '0800123123',
            'email' => 'supplier@apotik.test',
            'company' => 'Sehat Sentosa',
            'address' => 'Jl. Kebon Jeruk'
        ]);
        $supplierB = Supplier::create([
            'name' => 'CV Farma Jaya',
            'phone' => '0800456789',
            'email' => 'distributor@farmasi.test',
            'company' => 'Farma Jaya',
            'address' => 'Jl. Melati'
        ]);
        $supplierC = Supplier::create([
            'name' => 'PT Prima Medik',
            'phone' => '021555111',
            'email' => 'prima@distributor.test',
            'company' => 'Prima Medik',
            'address' => 'Jl. Palmerah No. 9'
        ]);
        $supplierD = Supplier::create([
            'name' => 'CV Anugrah Sehat',
            'phone' => '021888777',
            'email' => 'sales@anugrah.test',
            'company' => 'Anugrah Sehat',
            'address' => 'Jl. Raya Bogor Km. 22'
        ]);

        // Kasir sessions (1 closed, 1 masih terbuka untuk POS)
        $cashMorning = CashSession::create([
            'user_id' => $admin?->id,
            'opening_balance' => 500000,
            'status' => 'open',
            'opened_at' => now()->subHours(6),
            'note' => 'Shift pagi',
        ]);
        $cashMorning->update([
            'closing_balance' => 550000,
            'closed_at' => now()->subHours(3),
            'status' => 'closed',
        ]);
        $cashActive = CashSession::create([
            'user_id' => $admin?->id,
            'opening_balance' => 300000,
            'status' => 'open',
            'opened_at' => now()->subHour(),
            'note' => 'Shift siang (aktif)',
        ]);

        // Purchases (stok)
        $paracetamolPurchase = Purchase::create([
            'product' => 'Paracetamol 500mg',
            'category_id' => $analgesik->id,
            'supplier_id' => $supplierA->id,
            'cost_price' => 5000,
            'quantity' => 120,
            'expiry_date' => now()->addMonths(10)->toDateString(),
            'batch_no' => 'PARA-LOT-01',
            'unit_name' => 'Strip',
            'unit_size' => 10,
            'conversion_factor' => 1,
        ]);
        $amoxPurchase = Purchase::create([
            'product' => 'Amoxicillin 500mg',
            'category_id' => $antibiotik->id,
            'supplier_id' => $supplierB->id,
            'cost_price' => 8000,
            'quantity' => 80,
            'expiry_date' => now()->addMonths(6)->toDateString(),
            'batch_no' => 'AMOX-LOT-02',
            'unit_name' => 'Strip',
            'unit_size' => 10,
            'conversion_factor' => 1,
        ]);
        $vitCPurchase = Purchase::create([
            'product' => 'Vitamin C 500mg',
            'category_id' => $vitamin->id,
            'supplier_id' => $supplierA->id,
            'cost_price' => 6000,
            'quantity' => 60,
            'expiry_date' => now()->addMonths(12)->toDateString(),
            'batch_no' => 'VITC-01',
            'unit_name' => 'Strip',
            'unit_size' => 10,
            'conversion_factor' => 1,
        ]);
        $expiredPurchase = Purchase::create([
            'product' => 'Sirup Batuk Dewasa',
            'category_id' => $analgesik->id,
            'supplier_id' => $supplierB->id,
            'cost_price' => 9000,
            'quantity' => 5,
            'expiry_date' => now()->subMonths(1)->toDateString(),
            'batch_no' => 'COUGH-EXPIRED',
            'unit_name' => 'Botol',
            'unit_size' => 1,
            'conversion_factor' => 1,
        ]);
        $outstockPurchase = Purchase::create([
            'product' => 'Saline Infus 500ml',
            'category_id' => $vitamin->id,
            'supplier_id' => $supplierA->id,
            'cost_price' => 15000,
            'quantity' => 0,
            'expiry_date' => now()->addMonths(18)->toDateString(),
            'batch_no' => 'SALINE-0',
            'unit_name' => 'Botol',
            'unit_size' => 1,
            'conversion_factor' => 1,
        ]);
        $cetirizinePurchase = Purchase::create([
            'product' => 'Cetirizine 10mg',
            'category_id' => $antihistamin->id,
            'supplier_id' => $supplierC->id,
            'cost_price' => 7000,
            'quantity' => 150,
            'expiry_date' => now()->addMonths(14)->toDateString(),
            'batch_no' => 'CTZ-14',
            'unit_name' => 'Strip',
            'unit_size' => 10,
            'conversion_factor' => 1,
        ]);
        $omeprazolePurchase = Purchase::create([
            'product' => 'Omeprazole 20mg',
            'category_id' => $digestive->id,
            'supplier_id' => $supplierD->id,
            'cost_price' => 8500,
            'quantity' => 90,
            'expiry_date' => now()->addMonths(9)->toDateString(),
            'batch_no' => 'OME-09',
            'unit_name' => 'Strip',
            'unit_size' => 10,
            'conversion_factor' => 1,
        ]);
        $orsPurchase = Purchase::create([
            'product' => 'ORS Sachet',
            'category_id' => $digestive->id,
            'supplier_id' => $supplierD->id,
            'cost_price' => 2500,
            'quantity' => 300,
            'expiry_date' => now()->addMonths(20)->toDateString(),
            'batch_no' => 'ORS-20',
            'unit_name' => 'Sachet',
            'unit_size' => 1,
            'conversion_factor' => 1,
        ]);
        $salbutamolPurchase = Purchase::create([
            'product' => 'Salbutamol Nebulizer 2.5mg',
            'category_id' => $respiratory->id,
            'supplier_id' => $supplierC->id,
            'cost_price' => 12000,
            'quantity' => 70,
            'expiry_date' => now()->addMonths(8)->toDateString(),
            'batch_no' => 'SALB-NEB-08',
            'unit_name' => 'Ampul',
            'unit_size' => 1,
            'conversion_factor' => 1,
        ]);
        $zincPurchase = Purchase::create([
            'product' => 'Zinc 20mg',
            'category_id' => $vitamin->id,
            'supplier_id' => $supplierB->id,
            'cost_price' => 4000,
            'quantity' => 200,
            'expiry_date' => now()->addMonths(16)->toDateString(),
            'batch_no' => 'ZINC-16',
            'unit_name' => 'Tablet',
            'unit_size' => 10,
            'conversion_factor' => 1,
        ]);

        // Products (harga jual + tier)
        $paracetamol = Product::create([
            'purchase_id' => $paracetamolPurchase->id,
            'price' => 8500,
            'price_retail' => 8500,
            'price_wholesale' => 8000,
            'price_insurance' => 7500,
            'discount' => 0,
            'description' => 'Paracetamol tablet 500mg isi 10',
        ]);
        $amox = Product::create([
            'purchase_id' => $amoxPurchase->id,
            'price' => 12000,
            'price_retail' => 12000,
            'price_wholesale' => 11000,
            'price_insurance' => 9000,
            'discount' => 0,
            'description' => 'Amoxicillin 500mg isi 10',
        ]);
        $vitC = Product::create([
            'purchase_id' => $vitCPurchase->id,
            'price' => 9000,
            'price_retail' => 9000,
            'price_wholesale' => 8500,
            'price_insurance' => 8000,
            'discount' => 0,
            'description' => 'Vitamin C 500mg',
        ]);
        $expiredProduct = Product::create([
            'purchase_id' => $expiredPurchase->id,
            'price' => 12000,
            'price_retail' => 12000,
            'price_wholesale' => 11500,
            'price_insurance' => 11000,
            'discount' => 0,
            'description' => 'Sirup batuk (expired sample)',
        ]);
        $outstockProduct = Product::create([
            'purchase_id' => $outstockPurchase->id,
            'price' => 18000,
            'price_retail' => 18000,
            'price_wholesale' => 17000,
            'price_insurance' => 16500,
            'discount' => 0,
            'description' => 'Saline Infus 500ml (stok habis sample)',
        ]);
        $cetirizine = Product::create([
            'purchase_id' => $cetirizinePurchase->id,
            'price' => 12000,
            'price_retail' => 12000,
            'price_wholesale' => 11000,
            'price_insurance' => 10000,
            'discount' => 5,
            'promo_percent' => 10,
            'description' => 'Cetirizine 10mg tablet',
        ]);
        $omeprazole = Product::create([
            'purchase_id' => $omeprazolePurchase->id,
            'price' => 14000,
            'price_retail' => 14000,
            'price_wholesale' => 13000,
            'price_insurance' => 11500,
            'discount' => 0,
            'bundle_qty' => 3,
            'bundle_price' => 36000,
            'description' => 'Omeprazole 20mg kapsul',
        ]);
        $ors = Product::create([
            'purchase_id' => $orsPurchase->id,
            'price' => 5000,
            'price_retail' => 5000,
            'price_wholesale' => 4500,
            'price_insurance' => 4300,
            'discount' => 0,
            'description' => 'Oralit (ORS) sachet',
        ]);
        $salbutamol = Product::create([
            'purchase_id' => $salbutamolPurchase->id,
            'price' => 18000,
            'price_retail' => 18000,
            'price_wholesale' => 17000,
            'price_insurance' => 16500,
            'discount' => 0,
            'description' => 'Salbutamol nebule 2.5mg/2.5ml',
        ]);
        $zinc = Product::create([
            'purchase_id' => $zincPurchase->id,
            'price' => 7000,
            'price_retail' => 7000,
            'price_wholesale' => 6500,
            'price_insurance' => 6000,
            'discount' => 0,
            'description' => 'Zinc 20mg tablet',
        ]);

        // Compound templates
        $compound = Compound::create([
            'name' => 'Racikan Demam Dewasa',
            'service_fee' => 10000,
            'markup_percent' => 20,
            'price_override' => null,
        ]);
        CompoundItem::create([
            'compound_id' => $compound->id,
            'product_id' => $paracetamol->id,
            'quantity' => 1,
        ]);
        CompoundItem::create([
            'compound_id' => $compound->id,
            'product_id' => $vitC->id,
            'quantity' => 1,
        ]);

        $coughCompound = Compound::create([
            'name' => 'Racikan Batuk Anak',
            'service_fee' => 8000,
            'markup_percent' => 15,
            'price_override' => 28000,
        ]);
        CompoundItem::create([
            'compound_id' => $coughCompound->id,
            'product_id' => $cetirizine->id,
            'quantity' => 1,
        ]);
        CompoundItem::create([
            'compound_id' => $coughCompound->id,
            'product_id' => $salbutamol->id,
            'quantity' => 1,
        ]);

        $dyspepsiaCompound = Compound::create([
            'name' => 'Racikan Maag Dewasa',
            'service_fee' => 7000,
            'markup_percent' => 10,
            'price_override' => null,
        ]);
        CompoundItem::create([
            'compound_id' => $dyspepsiaCompound->id,
            'product_id' => $omeprazole->id,
            'quantity' => 1,
        ]);
        CompoundItem::create([
            'compound_id' => $dyspepsiaCompound->id,
            'product_id' => $ors->id,
            'quantity' => 2,
        ]);

        // Pasien
        $patientA = Patient::create([
            'name' => 'Budi Santoso',
            'phone' => '08123456789',
            'email' => 'budi@example.com',
            'address' => 'Jl. Merdeka No. 1',
            'notes' => 'Alergi penisilin ringan',
            'dob' => '1990-02-15',
        ]);
        $patientB = Patient::create([
            'name' => 'Sari Dewi',
            'phone' => '08129876543',
            'email' => 'sari@example.com',
            'address' => 'Jl. Kenanga No. 5',
            'notes' => null,
            'dob' => '1992-08-05',
        ]);
        $patientC = Patient::create([
            'name' => 'Andi Wijaya',
            'phone' => '081355566677',
            'email' => 'andi@example.com',
            'address' => 'Jl. Pahlawan No. 7',
            'notes' => 'BPJS',
            'dob' => '1988-11-30',
        ]);
        $patientD = Patient::create([
            'name' => 'Lestari Putri',
            'phone' => '081311122233',
            'email' => 'lestari@example.com',
            'address' => 'Jl. Flamboyan No. 2',
            'notes' => 'Hamil trimester 2',
            'dob' => '1995-04-20',
        ]);
        $patientE = Patient::create([
            'name' => 'Rudi Hartono',
            'phone' => '081377700000',
            'email' => 'rudi@example.com',
            'address' => 'Jl. Mawar No. 12',
            'notes' => 'Diabetes kontrol',
            'dob' => '1970-09-12',
        ]);

        // Resep dengan item compound & komponen
        $rx = Prescription::create([
            'patient_id' => $patientA->id,
            'doctor_name' => 'dr. Andi',
            'diagnosis' => 'Demam dan infeksi ringan',
            'status' => 'draft',
            'prescribed_at' => now()->subDay(),
        ]);
        $rxItem1 = PrescriptionItem::create([
            'prescription_id' => $rx->id,
            'product_id' => $paracetamol->id,
            'product_name' => 'Paracetamol 500mg',
            'quantity' => 2,
            'dosage' => '3x1 sesudah makan',
            'is_compound' => false,
        ]);
        $rxItem2 = PrescriptionItem::create([
            'prescription_id' => $rx->id,
            'product_id' => null,
            'compound_id' => $compound->id,
            'product_name' => 'Racikan Demam Dewasa',
            'quantity' => 1,
            'dosage' => '2x1',
            'is_compound' => true,
        ]);
        PrescriptionItemComponent::create([
            'prescription_item_id' => $rxItem2->id,
            'product_id' => $paracetamol->id,
            'product_name' => 'Paracetamol 500mg',
            'quantity' => 1,
            'is_full_pack' => false,
        ]);
        PrescriptionItemComponent::create([
            'prescription_item_id' => $rxItem2->id,
            'product_id' => $vitC->id,
            'product_name' => 'Vitamin C 500mg',
            'quantity' => 1,
            'is_full_pack' => false,
        ]);

        $rx2 = Prescription::create([
            'patient_id' => $patientB->id,
            'doctor_name' => 'dr. Sinta',
            'diagnosis' => 'Alergi ringan & batuk kering',
            'status' => 'approved',
            'prescribed_at' => now()->subHours(3),
        ]);
        $rx2Item1 = PrescriptionItem::create([
            'prescription_id' => $rx2->id,
            'product_id' => $cetirizine->id,
            'product_name' => 'Cetirizine 10mg',
            'quantity' => 1,
            'dosage' => '1x1 malam',
            'is_compound' => false,
        ]);
        $rx2Item2 = PrescriptionItem::create([
            'prescription_id' => $rx2->id,
            'compound_id' => $coughCompound->id,
            'product_name' => 'Racikan Batuk Anak',
            'quantity' => 1,
            'dosage' => '2x1',
            'is_compound' => true,
        ]);
        PrescriptionItemComponent::create([
            'prescription_item_id' => $rx2Item2->id,
            'product_id' => $cetirizine->id,
            'product_name' => 'Cetirizine 10mg',
            'quantity' => 1,
            'is_full_pack' => false,
        ]);
        PrescriptionItemComponent::create([
            'prescription_item_id' => $rx2Item2->id,
            'product_id' => $salbutamol->id,
            'product_name' => 'Salbutamol Nebulizer 2.5mg',
            'quantity' => 1,
            'is_full_pack' => false,
        ]);

        $rx3 = Prescription::create([
            'patient_id' => $patientC->id,
            'doctor_name' => 'dr. Rudi',
            'diagnosis' => 'Dispepsia',
            'status' => 'dispensed',
            'prescribed_at' => now()->subHours(6),
        ]);
        PrescriptionItem::create([
            'prescription_id' => $rx3->id,
            'compound_id' => $dyspepsiaCompound->id,
            'product_name' => 'Racikan Maag Dewasa',
            'quantity' => 1,
            'dosage' => '1x1 pagi',
            'is_compound' => true,
        ]);
        PrescriptionItem::create([
            'prescription_id' => $rx3->id,
            'product_id' => $omeprazole->id,
            'product_name' => 'Omeprazole 20mg',
            'quantity' => 1,
            'dosage' => '1x1 sebelum makan',
            'is_compound' => false,
            'is_controlled' => false,
        ]);

        // Jalankan alur dispense untuk resep yang disetujui/dispensed (kurangi stok + log pergerakan)
        // RX2 (approved -> dispensed)
        $rx2->update([
            'status' => 'dispensed',
            'dispensed_by' => $admin?->id,
            'dispensed_at' => now()->subHours(2),
        ]);
        $cetirizinePurchase->decrement('quantity', 2); // 1 produk + 1 komponen racikan
        $salbutamolPurchase->decrement('quantity', 1); // komponen racikan
        StockMovement::create([
            'purchase_id' => $cetirizinePurchase->id,
            'user_id' => $admin?->id,
            'type' => 'out',
            'quantity' => 2,
            'reference_type' => 'prescription_dispense',
            'reference_id' => $rx2->id,
            'note' => 'Dispense Rx Cetirizine + racikan batuk',
        ]);
        StockMovement::create([
            'purchase_id' => $salbutamolPurchase->id,
            'user_id' => $admin?->id,
            'type' => 'out',
            'quantity' => 1,
            'reference_type' => 'prescription_dispense',
            'reference_id' => $rx2->id,
            'note' => 'Dispense komponen Salbutamol racikan batuk',
        ]);

        // RX3 (dispensed)
        $rx3->update([
            'dispensed_by' => $admin?->id,
            'dispensed_at' => now()->subHours(1),
        ]);
        $omeprazolePurchase->decrement('quantity', 2); // 1 dari racikan + 1 item tunggal
        $orsPurchase->decrement('quantity', 2);        // komponen racikan
        StockMovement::create([
            'purchase_id' => $omeprazolePurchase->id,
            'user_id' => $admin?->id,
            'type' => 'out',
            'quantity' => 2,
            'reference_type' => 'prescription_dispense',
            'reference_id' => $rx3->id,
            'note' => 'Dispense Racikan Maag + Omeprazole',
        ]);
        StockMovement::create([
            'purchase_id' => $orsPurchase->id,
            'user_id' => $admin?->id,
            'type' => 'out',
            'quantity' => 2,
            'reference_type' => 'prescription_dispense',
            'reference_id' => $rx3->id,
            'note' => 'Dispense komponen ORS racikan maag',
        ]);

        // Invoice + penjualan (stok turun)
        $invoice = Invoice::create([
            'invoice_no' => 'INV-' . strtoupper(Str::random(6)),
            'user_id' => $admin?->id,
            'patient_id' => $patientA->id,
            'cash_session_id' => $cashActive?->id,
            'total_amount' => 52500,
            'paid_amount' => 30000,
            'payment_method' => 'cash',
            'status' => 'partial',
        ]);
        $saleParacetamol = SaleItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $paracetamol->id,
            'purchase_id' => $paracetamolPurchase->id,
            'quantity' => 3,
            'unit_price' => 8500,
            'total_price' => 25500,
        ]);
        $saleAmox = SaleItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $amox->id,
            'purchase_id' => $amoxPurchase->id,
            'quantity' => 1,
            'unit_price' => 12000,
            'total_price' => 12000,
        ]);
        $saleOrs = SaleItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $ors->id,
            'purchase_id' => $orsPurchase->id,
            'quantity' => 3,
            'unit_price' => 5000,
            'total_price' => 15000,
        ]);
        $paracetamolPurchase->decrement('quantity', 3);
        $amoxPurchase->decrement('quantity', 1);
        $orsPurchase->decrement('quantity', 3);
        StockMovement::create([
            'purchase_id' => $paracetamolPurchase->id,
            'user_id' => $admin?->id,
            'type' => 'out',
            'quantity' => 3,
            'reference_type' => 'invoice',
            'reference_id' => $invoice->id,
            'note' => 'Penjualan POS Paracetamol 500mg',
        ]);
        StockMovement::create([
            'purchase_id' => $amoxPurchase->id,
            'user_id' => $admin?->id,
            'type' => 'out',
            'quantity' => 1,
            'reference_type' => 'invoice',
            'reference_id' => $invoice->id,
            'note' => 'Penjualan POS Amoxicillin 500mg',
        ]);
        StockMovement::create([
            'purchase_id' => $orsPurchase->id,
            'user_id' => $admin?->id,
            'type' => 'out',
            'quantity' => 3,
            'reference_type' => 'invoice',
            'reference_id' => $invoice->id,
            'note' => 'Penjualan POS ORS sachet',
        ]);

        // Piutang + pembayaran
        $receivable = Receivable::create([
            'invoice_id' => $invoice->id,
            'patient_id' => $patientA->id,
            'total_due' => 52500,
            'paid_amount' => 30000,
            'due_date' => now()->addDays(14)->toDateString(),
            'status' => 'open',
        ]);
        ReceivablePayment::create([
            'receivable_id' => $receivable->id,
            'amount' => 30000,
            'paid_at' => now()->subHours(3),
            'user_id' => $admin?->id,
        ]);

        $invoice2 = Invoice::create([
            'invoice_no' => 'INV-' . strtoupper(Str::random(6)),
            'user_id' => $admin?->id,
            'patient_id' => $patientB->id,
            'cash_session_id' => $cashActive?->id,
            'total_amount' => 43000,
            'paid_amount' => 43000,
            'payment_method' => 'transfer',
            'status' => 'paid',
        ]);
        SaleItem::create([
            'invoice_id' => $invoice2->id,
            'product_id' => $cetirizine->id,
            'purchase_id' => $cetirizinePurchase->id,
            'quantity' => 2,
            'unit_price' => 12000,
            'total_price' => 24000,
        ]);
        SaleItem::create([
            'invoice_id' => $invoice2->id,
            'product_id' => $vitC->id,
            'purchase_id' => $vitCPurchase->id,
            'quantity' => 1,
            'unit_price' => 9000,
            'total_price' => 9000,
        ]);
        SaleItem::create([
            'invoice_id' => $invoice2->id,
            'product_id' => $ors->id,
            'purchase_id' => $orsPurchase->id,
            'quantity' => 2,
            'unit_price' => 5000,
            'total_price' => 10000,
        ]);
        $cetirizinePurchase->decrement('quantity', 2);
        $vitCPurchase->decrement('quantity', 1);
        $orsPurchase->decrement('quantity', 2);
        StockMovement::create([
            'purchase_id' => $cetirizinePurchase->id,
            'user_id' => $admin?->id,
            'type' => 'out',
            'quantity' => 2,
            'reference_type' => 'invoice',
            'reference_id' => $invoice2->id,
            'note' => 'Penjualan POS Cetirizine',
        ]);
        StockMovement::create([
            'purchase_id' => $vitCPurchase->id,
            'user_id' => $admin?->id,
            'type' => 'out',
            'quantity' => 1,
            'reference_type' => 'invoice',
            'reference_id' => $invoice2->id,
            'note' => 'Penjualan POS Vitamin C',
        ]);
        StockMovement::create([
            'purchase_id' => $orsPurchase->id,
            'user_id' => $admin?->id,
            'type' => 'out',
            'quantity' => 2,
            'reference_type' => 'invoice',
            'reference_id' => $invoice2->id,
            'note' => 'Penjualan POS ORS',
        ]);

        $invoice3 = Invoice::create([
            'invoice_no' => 'INV-' . strtoupper(Str::random(6)),
            'user_id' => $admin?->id,
            'patient_id' => $patientD->id,
            'cash_session_id' => $cashActive?->id,
            'total_amount' => 67000,
            'paid_amount' => 25000,
            'payment_method' => 'cash',
            'status' => 'partial',
        ]);
        SaleItem::create([
            'invoice_id' => $invoice3->id,
            'product_id' => $omeprazole->id,
            'purchase_id' => $omeprazolePurchase->id,
            'quantity' => 2,
            'unit_price' => 14000,
            'total_price' => 28000,
        ]);
        SaleItem::create([
            'invoice_id' => $invoice3->id,
            'product_id' => $zinc->id,
            'purchase_id' => $zincPurchase->id,
            'quantity' => 3,
            'unit_price' => 7000,
            'total_price' => 21000,
        ]);
        SaleItem::create([
            'invoice_id' => $invoice3->id,
            'product_id' => $salbutamol->id,
            'purchase_id' => $salbutamolPurchase->id,
            'quantity' => 1,
            'unit_price' => 18000,
            'total_price' => 18000,
        ]);
        $omeprazolePurchase->decrement('quantity', 2);
        $zincPurchase->decrement('quantity', 3);
        $salbutamolPurchase->decrement('quantity', 1);
        StockMovement::create([
            'purchase_id' => $omeprazolePurchase->id,
            'user_id' => $admin?->id,
            'type' => 'out',
            'quantity' => 2,
            'reference_type' => 'invoice',
            'reference_id' => $invoice3->id,
            'note' => 'Penjualan POS Omeprazole',
        ]);
        StockMovement::create([
            'purchase_id' => $zincPurchase->id,
            'user_id' => $admin?->id,
            'type' => 'out',
            'quantity' => 3,
            'reference_type' => 'invoice',
            'reference_id' => $invoice3->id,
            'note' => 'Penjualan POS Zinc',
        ]);
        StockMovement::create([
            'purchase_id' => $salbutamolPurchase->id,
            'user_id' => $admin?->id,
            'type' => 'out',
            'quantity' => 1,
            'reference_type' => 'invoice',
            'reference_id' => $invoice3->id,
            'note' => 'Penjualan POS Salbutamol Nebulizer',
        ]);

        $receivable2 = Receivable::create([
            'invoice_id' => $invoice3->id,
            'patient_id' => $patientD->id,
            'total_due' => 67000,
            'paid_amount' => 40000,
            'due_date' => now()->addDays(10)->toDateString(),
            'status' => 'open',
        ]);
        ReceivablePayment::create([
            'receivable_id' => $receivable2->id,
            'amount' => 25000,
            'paid_at' => now()->subMinutes(45),
            'user_id' => $admin?->id,
        ]);
        ReceivablePayment::create([
            'receivable_id' => $receivable2->id,
            'amount' => 15000,
            'paid_at' => now()->subHour(),
            'user_id' => $admin?->id,
        ]);

        // Retur penjualan (kembalikan 1 strip paracetamol)
        $saleReturn = SaleReturn::create([
            'invoice_id' => $invoice->id,
            'sale_item_id' => $saleParacetamol->id,
            'user_id' => $admin?->id,
            'quantity' => 1,
            'refund_amount' => 8500,
            'reason' => 'Salah dosis',
        ]);
        $paracetamolPurchase->increment('quantity', 1);
        StockMovement::create([
            'purchase_id' => $paracetamolPurchase->id,
            'user_id' => $admin?->id,
            'type' => 'in',
            'quantity' => 1,
            'reference_type' => 'sale_return',
            'reference_id' => $saleReturn->id,
            'note' => 'Return Paracetamol (refund)',
        ]);

        // Retur pembelian (2 strip amox)
        $purchaseReturn = PurchaseReturn::create([
            'purchase_id' => $amoxPurchase->id,
            'user_id' => $admin?->id,
            'quantity' => 2,
            'reason' => 'Kemasan rusak',
        ]);
        $amoxPurchase->decrement('quantity', 2);
        StockMovement::create([
            'purchase_id' => $amoxPurchase->id,
            'user_id' => $admin?->id,
            'type' => 'out',
            'quantity' => 2,
            'reference_type' => 'purchase_return',
            'reference_id' => $purchaseReturn->id,
            'note' => 'Retur pembelian Amoxicillin (rusak)',
        ]);

        // Penyesuaian & opname & transfer
        StockAdjustment::create([
            'purchase_id' => $vitCPurchase->id,
            'user_id' => $admin?->id,
            'delta' => 2,
            'reason' => 'Koreksi penerimaan',
        ]);
        $vitCPurchase->increment('quantity', 2);

        StockAdjustment::create([
            'purchase_id' => $omeprazolePurchase->id,
            'user_id' => $admin?->id,
            'delta' => -1,
            'reason' => 'Tablet pecah',
        ]);
        $omeprazolePurchase->decrement('quantity', 1);

        StockOpname::create([
            'purchase_id' => $paracetamolPurchase->id,
            'user_id' => $admin?->id,
            'system_quantity' => 118,
            'counted_quantity' => 120,
            'delta' => 2,
            'note' => 'Hitung fisik shift pagi',
        ]);

        StockOpname::create([
            'purchase_id' => $cetirizinePurchase->id,
            'user_id' => $admin?->id,
            'system_quantity' => $cetirizinePurchase->quantity,
            'counted_quantity' => $cetirizinePurchase->quantity,
            'delta' => 0,
            'note' => 'Opname akhir bulan',
        ]);

        StockTransfer::create([
            'purchase_id' => $amoxPurchase->id,
            'user_id' => $admin?->id,
            'from_rack' => 'Rak A1',
            'to_rack' => 'Rak B2',
            'quantity_snapshot' => $amoxPurchase->quantity,
            'note' => 'Pindah rak depan',
        ]);

        StockTransfer::create([
            'purchase_id' => $salbutamolPurchase->id,
            'user_id' => $admin?->id,
            'from_rack' => 'Gudang belakang',
            'to_rack' => 'Rak C1',
            'quantity_snapshot' => $salbutamolPurchase->quantity,
            'note' => 'Siapkan area nebulizer',
        ]);

        // Tambah pasien lain tanpa transaksi untuk uji pencarian
        Patient::create([
            'name' => 'Doni Saputra',
            'phone' => '081377788899',
            'email' => 'doni@example.com',
            'address' => 'Jl. Anggrek No. 9',
            'dob' => '1985-07-18',
        ]);
        Patient::create([
            'name' => 'Aulia Rahman',
            'phone' => '081299900011',
            'email' => 'aulia@example.com',
            'address' => 'Jl. Jambu No. 3',
            'dob' => '2001-01-25',
        ]);
        Patient::create([
            'name' => 'Michael Gunawan',
            'phone' => '081233344455',
            'email' => 'michael@example.com',
            'address' => 'Jl. Kemanggisan No. 45',
            'dob' => '1979-06-02',
        ]);
    }
}
