<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PatientController extends Controller
{
    /**
     * Search endpoint used by the POS LOV/autocomplete.
     */
    public function search(Request $request)
    {
        $q = (string) $request->query('q', '');
        $mode = $request->query('mode', 'all'); // name|phone|all
        if ($q === '') {
            return response()->json([]);
        }

        // Try searching patients table if exists, fallback to users
        if (Schema::hasTable('patients')) {
            $rows = Patient::select('id', 'name', 'phone', 'dob')
                ->where(function($qry) use ($q, $mode) {
                    if ($mode === 'name') {
                        $qry->where('name', 'like', "%{$q}%");
                    } elseif ($mode === 'phone') {
                        $qry->where('phone', 'like', "%{$q}%");
                    } else {
                        $qry->where('name', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%");
                    }
                })
                ->limit(20)
                ->get();
        } else {
            $rows = DB::table('users')
                ->select('id', 'name', 'phone')
                ->where(function($qry) use ($q, $mode) {
                    if ($mode === 'name') {
                        $qry->where('name', 'like', "%{$q}%");
                    } elseif ($mode === 'phone') {
                        $qry->where('phone', 'like', "%{$q}%");
                    } else {
                        $qry->where('name', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%");
                    }
                })
                ->limit(20)
                ->get();
        }

        return response()->json($rows);
    }

    public function index(Request $request)
    {
        $patients = Patient::orderBy('name')->paginate(20);
        $title = 'patients';
        return view('admin.patients.index', compact('patients', 'title'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'phone' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:200',
            'dob' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $patient = Patient::create($data);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'id' => $patient->id, 'patient' => $patient]);
        }

        return back()->with(notify('Patient created'));
    }

    public function update(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'phone' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:200',
            'dob' => 'nullable|date',
            'address' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $patient->update($data);
        return back()->with(notify('Patient updated'));
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        return response()->json(['success' => true]);
    }
}
