<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\inventtables;
use App\Models\rboinventtables;
use App\Models\inventtablemodules;
use App\Models\importproducts;
use App\Models\barcodes;
use App\Models\inventitembarcodes;
use App\Models\rboinventitemretailgroups;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class ItemsController extends Controller
{
public function index()
{
    try {
        $rboinventitemretailgroups = DB::table('rboinventitemretailgroups')->get();

        
        $items = DB::table('inventtablemodules as a')
        ->select(
            'a.ITEMID as itemid',
            'c.Activeondelivery as Activeondelivery',
            'b.itemname as itemname',
            'c.itemgroup as itemgroup',
            'c.itemdepartment as specialgroup',
            'c.production as production',
            'c.moq as moq',
            
            'c.default1 as default1',
            'c.default2 as default2',
            'c.default3 as default3',
            DB::raw('CAST(a.priceincltax as float) as price'),
            DB::raw('CAST(COALESCE(a.manilaprice, 0) as float) as manilaprice'),
            DB::raw('CAST(COALESCE(a.grabfood, 0) as float) as grabfoodprice'),
            DB::raw('CAST(COALESCE(a.foodpanda, 0) as float) as foodpandaprice'),
            DB::raw('CAST(COALESCE(a.mallprice, 0) as float) as mallprice'),
            
            DB::raw('CAST(COALESCE(a.foodpandamall, 0) as float) as foodpandamallprice'),
            DB::raw('CAST(COALESCE(a.grabfoodmall, 0) as float) as grabfoodmallprice'),
            DB::raw('CAST(a.price as float) as cost'),
            
            DB::raw("CASE 
                WHEN c.barcode IS NOT NULL AND c.barcode != '' THEN c.barcode 
                WHEN d.ITEMBARCODE IS NOT NULL AND d.ITEMBARCODE != '' THEN d.itembarcode 
                ELSE 'N/A' 
            END as barcode")
        )
        ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
        ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
        ->leftJoin('inventitembarcodes as d', function($join) {
            $join->on('c.itemid', '=', 'd.itemid')
                 ->orOn('c.barcode', '=', 'd.ITEMBARCODE');
        })
        ->where('c.itemdepartment', '=', 'REGULAR PRODUCT') 
        ->whereNotNull('b.itemid') 
        ->whereNotNull('c.itemid') 
        ->get();

        
        \Log::info('Items index query executed', [
            'total_items_found' => $items->count(),
            'sample_item' => $items->first()
        ]);

        return Inertia::render('Items/Index', [
            'items' => $items, 
            'rboinventitemretailgroups' => $rboinventitemretailgroups
        ]);

    } catch (\Exception $e) {
        \Log::error('Error in items index', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        
        return Inertia::render('Items/Index', [
            'items' => collect([]), 
            'rboinventitemretailgroups' => collect([]),
            'error' => 'Error loading items: ' . $e->getMessage()
        ]);
    }
}

    public function create()
    {
        
    }

    public function store(Request $request)
{
    try {
        $request->validate([
            'itemid'=> 'required|string|unique:inventtables,itemid',
            'itemname'=> 'required|string',
            'itemdepartment'=> 'required|string',
            'itemgroup'=> 'required|string',
            'barcode' => 'required|numeric|digits:13|unique:barcodes,barcode',
            'cost'=> 'required|numeric|min:0',
            'price'=> 'required|numeric|min:0',
        ]);

        
        DB::beginTransaction();

        inventtablemodules::create([
            'itemid'=> $request->itemid,
            'moduletype'=> '1',
            'unitid'=> '1',
            'price'=> $request->cost,
            'priceunit'=> '1',
            'priceincltax'=> $request->price,
            'blocked'=> '0',
            'inventlocationid'=> 'S0001',
            'pricedate'=> Carbon::now(),
            'taxitemgroupid'=> '1',
            
            'manilaprice'=> 0,
            'grabfood'=> 0,
            'foodpanda'=> 0,
            'mallprice'=> 0,
            'foodpandamall'=> 0,
            'grabfoodmall'=> 0,                      
        ]);

        inventtables::create([
            'itemgroupid'=> '1',
            'itemid'=> $request->itemid,
            'itemname'=> $request->itemname,
            'itemtype'=> '1',
            'notes'=> 'NA',
        ]);

        rboinventtables::create([
            'itemid'=> $request->itemid,
            'itemgroup'=> $request->itemgroup,
            'itemdepartment'=> $request->itemdepartment,
            'barcode'=> $request->barcode,
            'activeondelivery'=> '1',
            'production'=> 'NEWCOM',
            'moq'=> null,
            
            'default1'=> 0,
            'default2'=> 0,
            'default3'=> 0,
        ]);

        $name = Auth::user()->name;

        barcodes::create([
            'barcode'=> $request->barcode,
            'description'=> $request->itemname,
            'generateby'=> $name,
            'generatedate'=> Carbon::now(),
            'modifiedby'=> $name,
            'IsUse'=> 1,
        ]);

        inventitembarcodes::create([
            'itembarcode'=> $request->barcode,
            'itemid'=> $request->itemid,
            'description'=> $request->itemname,
            'blocked'=> '0',
            'modifiedby'=> $name,
            'qty'=> 0,
            'unitid'=> '1',
            'rbovariantid'=> '',
            'barcodesetupid'=> '',
        ]);

        DB::commit();

        
        if ($request->itemgroup === 'BW PROMO') {
            return redirect()->route('item-links.index', $request->itemid)
                ->with('message', 'Product created successfully. Configure item links for this promo item.')
                ->with('isSuccess', true);
        }

        return redirect()->route('items.index')
            ->with('message', 'Product created successfully')
            ->with('isSuccess', true);

    } catch (ValidationException $e) {
        DB::rollBack();
        return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', 'Validation failed')
            ->with('isSuccess', false);
    } catch (\Exception $e) {
        DB::rollBack();
        return back()
            ->with('message', 'Error creating product: ' . $e->getMessage())
            ->with('isSuccess', false);
    }
}

    public function show(string $id)
    {
        
    }

    public function edit(string $id)
    {
        
    }

    public function update(Request $request, string $itemid)
    {
        try {
            
            $request->validate([
                'itemid' => 'required|string',
                'itemname' => 'required|string|max:255',
                'itemgroup' => 'required|string', 
                'cost' => 'required|numeric|min:0',
                'price' => 'required|numeric|min:0',
                'manilaprice' => 'required|numeric|min:0',
                'foodpandaprice' => 'required|numeric|min:0',
                'grabfoodprice' => 'required|numeric|min:0',
                'mallprice' => 'required|numeric|min:0',
                'foodpandamallprice' => 'required|numeric|min:0',
                'grabfoodmallprice' => 'required|numeric|min:0',
                'production' => 'required|string',
                'moq' => 'nullable|numeric|min:0', 
                
                'default1' => 'boolean',
                'default2' => 'boolean',
                'default3' => 'boolean',
                'confirm_defaults' => 'required|accepted', 
            ]);

            DB::beginTransaction();

            
            $itemExists = inventtables::where('itemid', $itemid)->exists();
            if (!$itemExists) {
                throw new \Exception('Item not found');
            }

            
            inventtables::where('itemid', $itemid)
                ->update([
                    'itemname' => $request->itemname,
                    'updated_at' => now(),
                ]);

            
            inventtablemodules::where('itemid', $itemid)
                ->update([
                    'price' => $request->cost,
                    'priceincltax' => $request->price,
                    'manilaprice' => $request->manilaprice,
                    'foodpanda' => $request->foodpandaprice,
                    'grabfood' => $request->grabfoodprice,
                    'mallprice' => $request->mallprice,
                    'foodpandamall' => $request->foodpandamallprice,
                    'grabfoodmall' => $request->grabfoodmallprice,
                    'pricedate' => Carbon::now(),
                ]);

            
            rboinventtables::where('itemid', $itemid)
                ->update([
                    'itemgroup' => $request->itemgroup, 
                    'production' => $request->production,
                    'moq' => $request->moq, 
                    
                    'default1' => $request->default1 ? 1 : 0,
                    'default2' => $request->default2 ? 1 : 0,
                    'default3' => $request->default3 ? 1 : 0,
                ]);

            DB::commit();

            return redirect()->route('items.index')
                ->with('message', 'Item updated successfully')
                ->with('isSuccess', true);

        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())
                ->withInput()
                ->with('message', "Validation failed")
                ->with('isSuccess', false);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('message', 'Error updating item: ' . $e->getMessage())
                ->with('isSuccess', false);
        }
    }

    public function destroy(string $id, Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:items,id',
            ]);

            DB::beginTransaction();

            
            Item::where('id', $request->id)->delete();

            DB::commit();

            return redirect()->route('items.index')
            ->with('message', 'Item deleted successfully')
            ->with('isSuccess', true);

        } catch (ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())
            ->withInput()
            ->with('message', 'Validation failed')
            ->with('isSuccess', false);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
            ->with('message', 'Error deleting item: ' . $e->getMessage())
            ->with('isSuccess', false);
        }
    }

    public function export(Request $request)
    {
        $items = DB::table('inventtablemodules as a')
        ->select(
            'a.ITEMID as itemid',
            'b.itemname as itemname',
            'c.itemgroup as itemgroup',
            'c.itemdepartment as specialgroup',
            'c.production as production',
            'c.moq as moq',
            'a.price as cost',
            'a.priceincltax as price',
            'a.manilaprice as manilaprice',
            'a.grabfood as grabfoodprice',
            'a.foodpanda as foodpandaprice',
            'a.mallprice as mallprice',
            'a.foodpandamall as foodpandamallprice',
            'a.grabfoodmall as grabfoodmallprice',
            'c.default1 as default1',
            'c.default2 as default2',
            'c.default3 as default3',
            'c.Activeondelivery as Activeondelivery',
            'd.itembarcode as barcode'
        )
        ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
        ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
        ->leftJoin('inventitembarcodes as d', 'c.barcode', '=', 'd.ITEMBARCODE')
        ->where('c.itemdepartment', '=', 'REGULAR PRODUCT')
        ->get();

        return response()->json($items);
    }

    public function downloadTemplate()
{
    try {
        
        $items = DB::table('inventtablemodules as a')
            ->select(
                'a.ITEMID as itemid',
                'b.itemname as itemname',
                DB::raw("CASE 
                    WHEN c.barcode IS NOT NULL AND c.barcode != '' THEN c.barcode 
                    WHEN d.ITEMBARCODE IS NOT NULL AND d.ITEMBARCODE != '' THEN d.itembarcode 
                    ELSE '' 
                END as barcode"),
                'c.itemgroup as itemgroup',
                'c.itemdepartment as specialgroup',
                'c.production as production',
                'c.moq as moq',
                DB::raw('CAST(a.price as float) as cost'),
                DB::raw('CAST(a.priceincltax as float) as price'),
                DB::raw('CAST(COALESCE(a.manilaprice, 0) as float) as manilaprice'),
                DB::raw('CAST(COALESCE(a.mallprice, 0) as float) as mallprice'),
                DB::raw('CAST(COALESCE(a.grabfood, 0) as float) as grabfoodprice'),
                DB::raw('CAST(COALESCE(a.foodpanda, 0) as float) as foodpandaprice'),
                DB::raw('CAST(COALESCE(a.foodpandamall, 0) as float) as foodpandamallprice'),
                DB::raw('CAST(COALESCE(a.grabfoodmall, 0) as float) as grabfoodmallprice'),
                'c.default1 as default1',
                'c.default2 as default2',
                'c.default3 as default3',
                'c.Activeondelivery as Activeondelivery'
            )
            ->leftJoin('inventtables as b', 'a.ITEMID', '=', 'b.itemid')
            ->leftJoin('rboinventtables as c', 'b.itemid', '=', 'c.itemid')
            ->leftJoin('inventitembarcodes as d', function($join) {
                $join->on('c.itemid', '=', 'd.itemid')
                     ->orOn('c.barcode', '=', 'd.ITEMBARCODE');
            })
            ->whereNotNull('b.itemid')
            ->whereNotNull('c.itemid')
            ->limit(1000) 
            ->get();

        $headers = [
            'itemid',
            'itemname', 
            'barcode',
            'itemgroup',
            'specialgroup',
            'production',
            'moq',
            'cost',
            'price',
            'manilaprice',
            'mallprice', 
            'grabfoodprice',
            'foodpandaprice',
            'foodpandamallprice',
            'grabfoodmallprice',
            'default1',
            'default2', 
            'default3',
            'Activeondelivery'
        ];

        $filename = 'items_template_with_data_' . date('Y-m-d_H-i-s') . '.csv';
        
        $callback = function() use ($headers, $items) {
            $file = fopen('php:
            
            
            fwrite($file, "\xEF\xBB\xBF");
            
            
            fputcsv($file, $headers);
            
            
            fputcsv($file, [
                '# SAMPLE ROWS - DELETE THESE BEFORE IMPORTING',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ]);
            
            fputcsv($file, [
                'ACC-SUP-036',
                'GIFT TAG',
                '1234567890123',
                'MERCHANDISE',
                'REGULAR PRODUCT',
                'NEWCOM',
                '5',
                '2.00',
                '15.00',
                '16.00',
                '17.00',
                '18.00',
                '19.00',
                '20.00',
                '21.00',
                '0',
                '0',
                '0',
                '1'
            ]);
            
            fputcsv($file, [
                'BEV-TRA-001',
                'COKE 1.5 liters',
                '0245698563542',
                'BEVERAGES',
                'REGULAR PRODUCT',
                'NEWCOM',
                '10',
                '85.00',
                '99.00',
                '0.00',
                '0.00',
                '0.00',
                '0.00',
                '0.00',
                '0.00',
                '0',
                '0',
                '0',
                '1'
            ]);
            
            
            fputcsv($file, [
                '# CURRENT ITEMS DATA - MODIFY AS NEEDED',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ]);
            
            
            foreach ($items as $item) {
                fputcsv($file, [
                    $item->itemid ?? '',
                    $item->itemname ?? '',
                    $item->barcode ?? '',
                    $item->itemgroup ?? '',
                    $item->specialgroup ?? 'REGULAR PRODUCT',
                    $item->production ?? 'NEWCOM',
                    $item->moq ?? '',
                    number_format((float)($item->cost ?? 0), 2, '.', ''),
                    number_format((float)($item->price ?? 0), 2, '.', ''),
                    number_format((float)($item->manilaprice ?? 0), 2, '.', ''),
                    number_format((float)($item->mallprice ?? 0), 2, '.', ''),
                    number_format((float)($item->grabfoodprice ?? 0), 2, '.', ''),
                    number_format((float)($item->foodpandaprice ?? 0), 2, '.', ''),
                    number_format((float)($item->foodpandamallprice ?? 0), 2, '.', ''),
                    number_format((float)($item->grabfoodmallprice ?? 0), 2, '.', ''),
                    $item->default1 ?? '0',
                    $item->default2 ?? '0',
                    $item->default3 ?? '0',
                    $item->Activeondelivery ?? '1'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);

    } catch (\Exception $e) {
        \Log::error('Error generating template with data', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        
        return $this->downloadBasicTemplate();
    }
}

private function downloadBasicTemplate()
{
    $headers = [
        'itemid',
        'itemname', 
        'barcode',
        'itemgroup',
        'specialgroup',
        'production',
        'moq',
        'cost',
        'price',
        'manilaprice',
        'mallprice', 
        'grabfoodprice',
        'foodpandaprice',
        'foodpandamallprice',
        'grabfoodmallprice',
        'default1',
        'default2', 
        'default3',
        'Activeondelivery'
    ];

    $filename = 'items_import_template_basic.csv';
    
    $callback = function() use ($headers) {
        $file = fopen('php:
        fputcsv($file, $headers);
        
        
        fputcsv($file, [
            'ACC-SUP-036',
            'GIFT TAG',
            '1234567890123',
            'MERCHANDISE',
            'REGULAR PRODUCT',
            'NEWCOM',
            '5',
            '2.00',
            '15.00',
            '16.00',
            '17.00',
            '18.00',
            '19.00',
            '20.00',
            '21.00',
            '0',
            '0',
            '0',
            '1'
        ]);
        
        fclose($file);
    };

    return response()->stream($callback, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);
}
    
public function import(Request $request)
{
    try {
        
        \Log::info('===== IMPORT STARTED =====', [
            'user' => Auth::user()->name ?? 'Unknown',
            'timestamp' => now()->toDateTimeString()
        ]);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('file');
        \Log::info('File received', [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType()
        ]);

        
        $csvContent = file($file->getRealPath());
        \Log::info('CSV file read', [
            'total_lines' => count($csvContent),
            'first_line' => isset($csvContent[0]) ? trim($csvContent[0]) : 'No content'
        ]);

        $csv = array_map('str_getcsv', $csvContent);
        $header = array_shift($csv); 

        
        $header = array_map(function($h) {
            return trim(str_replace("\xEF\xBB\xBF", '', $h));
        }, $header);

        \Log::info('CSV parsed', [
            'header' => $header,
            'data_rows' => count($csv)
        ]);

        
        $headerMapping = [
            'itemid' => ['itemid', 'item_id', 'ITEMID'],
            'itemname' => ['itemname', 'item_name', 'ITEMNAME', 'name'],
            'barcode' => ['barcode', 'BARCODE'],
            'itemgroup' => ['itemgroup', 'item_group', 'ITEMGROUP', 'category'],
            'specialgroup' => ['specialgroup', 'special_group', 'SPECIALGROUP', 'itemdepartment', 'department'],
            'production' => ['production', 'PRODUCTION'],
            'moq' => ['moq', 'MOQ', 'minimum_order_quantity'],
            'cost' => ['cost', 'COST', 'price'],
            'price' => ['price', 'PRICE', 'selling_price', 'priceincltax'],
            'manilaprice' => ['manilaprice', 'manila_price', 'MANILAPRICE'],
            'mallprice' => ['mallprice', 'mall_price', 'MALLPRICE'],
            'grabfoodprice' => ['grabfoodprice', 'grabfood_price', 'GRABFOODPRICE', 'grabfood'],
            'foodpandaprice' => ['foodpandaprice', 'foodpanda_price', 'FOODPANDAPRICE', 'foodpanda'],
            'foodpandamallprice' => ['foodpandamallprice', 'foodpandamall_price', 'FOODPANDAMALLPRICE', 'foodpandamall'],
            'grabfoodmallprice' => ['grabfoodmallprice', 'grabfoodmall_price', 'GRABFOODMALLPRICE', 'grabfoodmall'],
            'default1' => ['default1', 'DEFAULT1'],
            'default2' => ['default2', 'DEFAULT2'],
            'default3' => ['default3', 'DEFAULT3'],
            'Activeondelivery' => ['Activeondelivery', 'activeondelivery', 'ACTIVEONDELIVERY', 'active_on_delivery', 'active']
        ];

        
        $mappedHeaders = [];
        $headerIndexMapping = [];
        
        foreach ($header as $index => $actualHeader) {
            $mapped = false;
            foreach ($headerMapping as $expectedHeader => $variations) {
                if (in_array($actualHeader, $variations)) {
                    $mappedHeaders[] = $expectedHeader;
                    $headerIndexMapping[$expectedHeader] = $index;
                    $mapped = true;
                    break;
                }
            }
            if (!$mapped) {
                $mappedHeaders[] = $actualHeader; 
                $headerIndexMapping[$actualHeader] = $index;
            }
        }

        \Log::info('Header mapping completed', [
            'original_headers' => $header,
            'mapped_headers' => $mappedHeaders,
            'mapping' => $headerIndexMapping
        ]);

        
        $requiredHeaders = ['itemid', 'itemname'];
        $missingRequired = [];
        
        foreach ($requiredHeaders as $required) {
            if (!isset($headerIndexMapping[$required])) {
                $missingRequired[] = $required;
            }
        }

        if (!empty($missingRequired)) {
            $errorMessage = 'CSV header format is incorrect. Missing required headers: ' . implode(', ', $missingRequired) . 
                           '. Available headers: ' . implode(', ', $header) . 
                           '. Please ensure your CSV contains at least itemid and itemname columns.';
            
            \Log::error('Required header validation failed', [
                'required' => $requiredHeaders,
                'received' => $header,
                'missing_required' => $missingRequired
            ]);
            
            return back()->with('message', $errorMessage)->with('isSuccess', false);
        }

        \Log::info('Required header validation passed');

        DB::beginTransaction();
        \Log::info('Database transaction started');

        $successCount = 0;
        $updateCount = 0;
        $createCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($csv as $rowIndex => $row) {
            $actualRowNumber = $rowIndex + 2; 
            
            
            if (empty($row) || (isset($row[0]) && (empty(trim($row[0])) || strpos(trim($row[0]), '#') === 0))) {
                \Log::info("Skipping row {$actualRowNumber}: Empty or comment row");
                continue;
            }
            
            \Log::info("Processing row {$actualRowNumber}", [
                'row_data' => $row,
                'row_count' => count($row),
                'header_count' => count($header)
            ]);

            
            $data = [];
            foreach ($headerIndexMapping as $expectedHeader => $originalIndex) {
                $data[$expectedHeader] = isset($row[$originalIndex]) ? trim($row[$originalIndex]) : '';
            }
            
            
            $data['barcode'] = $data['barcode'] ?? '';
            $data['itemgroup'] = $data['itemgroup'] ?? 'GENERAL';
            $data['specialgroup'] = $data['specialgroup'] ?? 'REGULAR PRODUCT';
            $data['production'] = $data['production'] ?? 'NEWCOM';
            $data['moq'] = $data['moq'] ?? '';
            $data['cost'] = $data['cost'] ?? '0';
            $data['price'] = $data['priceincltax'] ?? '0';
            $data['manilaprice'] = $data['manilaprice'] ?? '0';
            $data['mallprice'] = $data['mallprice'] ?? '0';
            $data['grabfoodprice'] = $data['grabfoodprice'] ?? '0';
            $data['foodpandaprice'] = $data['foodpandaprice'] ?? '0';
            $data['foodpandamallprice'] = $data['foodpandamallprice'] ?? '0';
            $data['grabfoodmallprice'] = $data['grabfoodmallprice'] ?? '0';
            $data['default1'] = $data['default1'] ?? '0';
            $data['default2'] = $data['default2'] ?? '0';
            $data['default3'] = $data['default3'] ?? '0';
            $data['Activeondelivery'] = $data['Activeondelivery'] ?? '1';
            
            \Log::info("Row {$actualRowNumber} data prepared", ['data' => $data]);
            
            try {
                $name = Auth::user()->name;
                
                
                if (empty($data['itemid']) || empty($data['itemname'])) {
                    throw new \Exception("Required fields 'itemid' or 'itemname' are empty");
                }

                
                $barcode = !empty($data['barcode']) ? trim($data['barcode']) : null;
                $itemgroup = !empty($data['itemgroup']) ? trim($data['itemgroup']) : 'GENERAL';
                $specialgroup = !empty($data['specialgroup']) ? trim($data['specialgroup']) : 'REGULAR PRODUCT';
                $production = !empty($data['production']) ? trim($data['production']) : 'NEWCOM';
                
                
                $cost = is_numeric($data['cost']) ? floatval($data['cost']) : 0;
                $price = is_numeric($data['price']) ? floatval($data['price']) : 0;
                $moq = !empty($data['moq']) && is_numeric($data['moq']) ? intval($data['moq']) : null;
                
                \Log::info("Row {$actualRowNumber}: Field validation passed", [
                    'barcode' => $barcode,
                    'barcode_provided' => !is_null($barcode)
                ]);

                $itemExists = inventtables::where('itemid', $data['itemid'])->exists();
                \Log::info("Row {$actualRowNumber}: Item existence check", [
                    'itemid' => $data['itemid'],
                    'exists' => $itemExists
                ]);

                if ($itemExists) {
                    
                    \Log::info("Row {$actualRowNumber}: Starting UPDATE process for existing item");
                    
                    
                    if (!is_null($barcode)) {
                        $barcodeConflict = barcodes::where('barcode', $barcode)
                            ->whereNotExists(function($query) use ($data) {
                                $query->select(DB::raw(1))
                                      ->from('rboinventtables')
                                      ->whereRaw('rboinventtables.barcode = barcodes.barcode')
                                      ->where('rboinventtables.itemid', $data['itemid']);
                            })
                            ->exists();

                        if ($barcodeConflict) {
                            throw new \Exception("Barcode {$barcode} already exists for another item");
                        }
                    }

                    \Log::info("Row {$actualRowNumber}: Barcode conflict check passed");

                    
                    $inventtablesUpdated = inventtables::where('itemid', $data['itemid'])
                        ->update([
                            'itemname' => $data['itemname'],
                            'notes' => 'Updated via import',
                            'updated_at' => now(),
                        ]);
                    
                    \Log::info("Row {$actualRowNumber}: inventtables update", [
                        'affected_rows' => $inventtablesUpdated
                    ]);

                    
                    $inventmodulesUpdated = inventtablemodules::where('itemid', $data['itemid'])
                        ->update([
                            'price' => $cost,
                            'priceincltax' => $price,
                            'manilaprice' => floatval($data['manilaprice'] ?? 0),
                            'grabfood' => floatval($data['grabfoodprice'] ?? 0),
                            'foodpanda' => floatval($data['foodpandaprice'] ?? 0),
                            'mallprice' => floatval($data['mallprice'] ?? 0),
                            'foodpandamall' => floatval($data['foodpandamallprice'] ?? 0),
                            'grabfoodmall' => floatval($data['grabfoodmallprice'] ?? 0),
                            'pricedate' => Carbon::now(),
                        ]);

                    \Log::info("Row {$actualRowNumber}: inventtablemodules update", [
                        'affected_rows' => $inventmodulesUpdated
                    ]);

                    
                    $rboinventUpdated = rboinventtables::where('itemid', $data['itemid'])
                        ->update([
                            'itemgroup' => $itemgroup,
                            'itemdepartment' => $specialgroup,
                            'barcode' => $barcode, 
                            'activeondelivery' => $data['Activeondelivery'] == '1' ? 1 : 0,
                            'production' => $production,
                            'moq' => $moq,
                            'default1' => $data['default1'] == '1' ? 1 : 0,
                            'default2' => $data['default2'] == '1' ? 1 : 0,
                            'default3' => $data['default3'] == '1' ? 1 : 0,
                        ]);

                    \Log::info("Row {$actualRowNumber}: rboinventtables update", [
                        'affected_rows' => $rboinventUpdated
                    ]);

                    
                    if (!is_null($barcode)) {
                        
                        $currentBarcode = rboinventtables::where('itemid', $data['itemid'])->value('barcode');
                        \Log::info("Row {$actualRowNumber}: Current barcode retrieved", [
                            'current_barcode' => $currentBarcode,
                            'new_barcode' => $barcode
                        ]);
                        
                        
                        if (!is_null($currentBarcode)) {
                            $barcodesUpdated = barcodes::where('barcode', $currentBarcode)
                                ->update([
                                    'barcode' => $barcode,
                                    'description' => $data['itemname'],
                                    'modifiedby' => $name,
                                    'updated_at' => Carbon::now(),
                                ]);
                        } else {
                            
                            barcodes::create([
                                'barcode' => $barcode,
                                'description' => $data['itemname'],
                                'generateby' => $name,
                                'generatedate' => Carbon::now(),
                                'modifiedby' => $name,
                                'IsUse' => 1,
                            ]);
                            $barcodesUpdated = 1;
                        }

                        \Log::info("Row {$actualRowNumber}: barcodes update/create", [
                            'affected_rows' => $barcodesUpdated
                        ]);

                        
                        $inventbarcodeUpdated = inventitembarcodes::where('itemid', $data['itemid'])
                            ->update([
                                'itembarcode' => $barcode,
                                'description' => $data['itemname'],
                                'modifiedby' => $name,
                                'updated_at' => Carbon::now(),
                            ]);

                        
                        if ($inventbarcodeUpdated === 0) {
                            \Log::info("Row {$actualRowNumber}: Creating new inventitembarcodes record");
                            inventitembarcodes::create([
                                'itembarcode' => $barcode,
                                'itemid' => $data['itemid'],
                                'description' => $data['itemname'],
                                'blocked' => '0',
                                'modifiedby' => $name,
                                'qty' => 0,
                                'unitid' => '1',
                                'rbovariantid' => '',
                                'barcodesetupid' => '',
                            ]);
                            $inventbarcodeUpdated = 1;
                        }

                        \Log::info("Row {$actualRowNumber}: inventitembarcodes update/create", [
                            'affected_rows' => $inventbarcodeUpdated
                        ]);
                    }

                    $updateCount++;
                    \Log::info("Row {$actualRowNumber}: UPDATE completed successfully");

                } else {
                    
                    \Log::info("Row {$actualRowNumber}: Starting CREATE process for new item");
                    
                    
                    if (!is_null($barcode) && barcodes::where('barcode', $barcode)->exists()) {
                        throw new \Exception("Barcode {$barcode} already exists");
                    }

                    \Log::info("Row {$actualRowNumber}: Barcode availability check passed");

                    
                    $inventModule = inventtablemodules::create([
                        'itemid' => $data['itemid'],
                        'moduletype' => '1',
                        'unitid' => '1', 
                        'price' => $cost,
                        'priceunit' => '1',
                        'priceincltax' => $price,
                        'blocked' => '0',
                        'inventlocationid' => 'S0001',
                        'pricedate' => Carbon::now(),
                        'taxitemgroupid' => '1',
                        'manilaprice' => floatval($data['manilaprice'] ?? 0),
                        'grabfood' => floatval($data['grabfoodprice'] ?? 0),
                        'foodpanda' => floatval($data['foodpandaprice'] ?? 0),
                        'mallprice' => floatval($data['mallprice'] ?? 0),
                        'foodpandamall' => floatval($data['foodpandamallprice'] ?? 0),
                        'grabfoodmall' => floatval($data['grabfoodmallprice'] ?? 0),
                    ]);

                    \Log::info("Row {$actualRowNumber}: inventtablemodules created", [
                        'id' => $inventModule->id ?? 'No ID'
                    ]);

                    
                    $inventTable = inventtables::create([
                        'itemgroupid' => '1',
                        'itemid' => $data['itemid'],
                        'itemname' => $data['itemname'],
                        'itemtype' => '1',
                        'notes' => 'Imported',
                    ]);

                    \Log::info("Row {$actualRowNumber}: inventtables created");

                    
                    $rboInventTable = rboinventtables::create([
                        'itemid' => $data['itemid'],
                        'itemgroup' => $itemgroup,
                        'itemdepartment' => $specialgroup,
                        'barcode' => $barcode, 
                        'activeondelivery' => $data['Activeondelivery'] == '1' ? 1 : 0,
                        'production' => $production,
                        'moq' => !empty($data['moq']) ? intval($data['moq']) : null,
                        'default1' => $data['default1'] == '1' ? 1 : 0,
                        'default2' => $data['default2'] == '1' ? 1 : 0,
                        'default3' => $data['default3'] == '1' ? 1 : 0,
                    ]);

                    \Log::info("Row {$actualRowNumber}: rboinventtables created");

                    
                    if (!is_null($barcode)) {
                        
                        $barcodeRecord = barcodes::create([
                            'barcode' => $barcode,
                            'description' => $data['itemname'],
                            'generateby' => $name,
                            'generatedate' => Carbon::now(),
                            'modifiedby' => $name,
                            'IsUse' => 1,
                        ]);

                        \Log::info("Row {$actualRowNumber}: barcodes created");

                        
                        $inventItemBarcode = inventitembarcodes::create([
                            'itembarcode' => $barcode,
                            'itemid' => $data['itemid'],
                            'description' => $data['itemname'],
                            'blocked' => '0',
                            'modifiedby' => $name,
                            'qty' => 0,
                            'unitid' => '1',
                            'rbovariantid' => '',
                            'barcodesetupid' => '',
                        ]);

                        \Log::info("Row {$actualRowNumber}: inventitembarcodes created");
                    } else {
                        \Log::info("Row {$actualRowNumber}: Skipped barcode-related table creation (no barcode provided)");
                    }

                    $createCount++;
                    \Log::info("Row {$actualRowNumber}: CREATE completed successfully");
                }

                $successCount++;

            } catch (\Exception $e) {
                $errorMsg = "Row {$actualRowNumber}: " . $e->getMessage();
                $errors[] = $errorMsg;
                $errorCount++;
                
                \Log::error("Row {$actualRowNumber} failed", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'data' => $data ?? 'No data available'
                ]);
            }
        }

        DB::commit();
        \Log::info('Database transaction committed successfully');

        $message = "Import completed. Total processed: {$successCount} (Created: {$createCount}, Updated: {$updateCount}), Errors: {$errorCount}";
        if (!empty($errors)) {
            $message .= "\n\nErrors:\n" . implode("\n", array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $message .= "\n... and " . (count($errors) - 10) . " more errors.";
            }
        }

        \Log::info('===== IMPORT COMPLETED =====', [
            'total_processed' => $successCount,
            'created' => $createCount,
            'updated' => $updateCount,
            'errors' => $errorCount,
            'error_details' => array_slice($errors, 0, 5) 
        ]);

        return redirect()->route('items.index')
            ->with('message', $message)
            ->with('isSuccess', $successCount > 0);

    } catch (\Exception $e) {
        DB::rollBack();
        
        \Log::error('===== IMPORT FAILED =====', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return back()
            ->with('message', 'Import failed: ' . $e->getMessage())
            ->with('isSuccess', false);
    }
}
    public function bulkEnable(Request $request)
    {
        try {
            $request->validate([
                'itemids' => 'required|array',
                'itemids.*' => 'required|string'
            ]);

            DB::beginTransaction();

            $updated = rboinventtables::whereIn('itemid', $request->itemids)
                ->update(['activeondelivery' => 1]);

            DB::commit();

            return response()->json([
                'message' => "Successfully enabled {$updated} items for ordering",
                'success' => true
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error enabling items: ' . $e->getMessage(),
                'success' => false
            ], 500);
        }
    }
}