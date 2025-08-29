<?php

namespace App\Http\Controllers;
use App\Models\wastedeclarations; 
use App\Models\wastedeclarationtrans; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Carbon\Carbon;

class WasteController extends Controller
{
        public function index()
    {
        $storeId = Auth::user()->storeid;
        $role = Auth::user()->role;

                        $wastedeclarations = DB::table('wastedeclarations AS a')
                        ->select('a.journalid', 'a.storeid', 'a.description',
                                DB::raw('SUM(CAST(b.COUNTED AS UNSIGNED)) AS qty'),
                                DB::raw('SUM(CAST(c.priceincltax AS DECIMAL(10,2)) * CAST(b.COUNTED AS UNSIGNED)) AS amount'),
                                'a.posted', 'a.updated_at', 'a.createddatetime')
                        ->leftJoin('wastedeclarationtrans AS b', 'b.journalid', '=', 'a.journalid')
                        ->leftJoin('inventtablemodules AS c', 'c.itemid', '=', 'b.ITEMID')
                        ->where('storeid', '=', $storeId)
                        ->groupBy('a.journalid', 'a.storeid', 'a.description',
                                'a.posted', 'a.updated_at', 'a.createddatetime')
                        ->get();
        
                    $utcDateTime = Carbon::now('UTC');
                    $currentDateTime = $utcDateTime->setTimezone('Asia/Manila')->toDateString();

                    $waste = [];
                    if ($role === "STORE") {
                        $orders = DB::table('wastedeclarations as b')
                            ->select(
                                'b.journalid',
                                'b.POSTEDDATETIME as POSTEDDATETIME',
                                'e.STOREID as STOREID', 
                                'b.STOREID as STORENAME',
                                'd.itemid as ITEMID',
                                'd.itemname as ITEMNAME',
                                'c.COUNTED as COUNTED'
                            )
                            ->leftJoin('wastedeclarationtrans as c', 'b.journalid', '=', 'c.journalid')
                            ->leftJoin('inventtables as d', 'c.ITEMID', '=', 'd.itemid')
                            ->leftJoin('rbostoretables as e', 'b.STOREID', '=', 'e.NAME')
                            ->whereRaw("DATE(b.createddatetime) = ?", [$currentDateTime])
                            ->where('b.POSTED', '=', '0')
                            ->where('b.STOREID', '=', $storeId) 
                            ->get();
                    }

                    return Inertia::render('Waste/Index', [
                        'wastedeclarations' => $wastedeclarations,
                        'waste' => $waste
                    ]);
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $utcDateTime = Carbon::now('UTC');
            $beijingDateTime = $utcDateTime->setTimezone('Asia/Manila');
            $storeId = Auth::user()->storeid;
            $userId = Auth::user()->id;

            $existingWaste = DB::table('wastedeclarations')
                ->whereDate('CREATEDDATETIME', $beijingDateTime)
                ->where('STOREID', $storeId)
                ->exists();

            if ($existingWaste) {
                throw new \Exception('You have already waste declaration this time.');
            }

            $nextRec = DB::table('nubersequencevalues')
                ->where('storeid', $storeId)
                ->lockForUpdate()
                ->value('wasterec');

            $nextRec = $nextRec !== null ? (int)$nextRec + 1 : 1;

            DB::table('nubersequencevalues')
                ->where('STOREID', $storeId)
                ->update(['WASTEREC' => $nextRec]);

            $journalid = $userId . str_pad($nextRec, 8, '0', STR_PAD_LEFT);
            $description = "BO" . $journalid;

            $newJournal = wastedeclarations::create([
                'journalid' => $journalid,
                'STOREID' => $storeId,
                'DESCRIPTION' => $description,
                'POSTED' => "0",
                'POSTEDDATETIME' => $beijingDateTime,
                'DELETEPOSTEDLINES' => "0",
                'CREATEDDATETIME' => $beijingDateTime,
            ]);

            DB::commit();

            return redirect()->route('waste.index', ['role' => Auth::user()->role])
                ->with('message', 'Waste Created Successfully')
                ->with('isSuccess', true);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('waste.index')
                ->with('message', $e->getMessage())
                ->with('isError', true);
        }
    }

    public function show(string $id)
    {
    }

    public function edit(string $id)
    {
    }

    public function update(Request $request, string $id)
    {
    }

    public function destroy(string $id)
    {
    }
}
