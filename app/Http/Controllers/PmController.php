<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PmController extends Controller
{
   public function pm(Request $request)
    {
        $allPm = DB::connection('sqlsrv2')
        ->table('vwUserInfo')
        ->get();

        return view('pm.pm',compact('allPm'));


    }
    public function pm_search(Request $request){
        $search = $request->input('search');

        $allPm = DB::connection('sqlsrv2')
            ->table('vwUserInfo')
            ->where('username', 'LIKE', '%' . $search . '%')
            ->orWhere('fname', 'LIKE', '%' . $search . '%')
            ->orWhere('lname','LIKE', '%' . $search . '%')
            ->orWhere('cid', 'LIKE', '%' . $search . '%')
            ->orWhere('username', 'LIKE', '%' . $search . '%')
            ->get();

        return view('pm.pm', compact('allPm'));

    }
}
