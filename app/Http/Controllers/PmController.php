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
            ->leftJoin('UserInfo', 'vwUserInfo.username', '=', 'UserInfo.username')
            ->limit(20)
            ->get();
        return view('pm.pm', compact('allPm'));
    }
    public function pm_search(Request $request)
    {
        $search = $request->input('search');

        $allPm = DB::connection('sqlsrv2')
            ->table('vwUserInfo as v')
            ->leftJoin('UserInfo as u', 'v.username', '=', 'u.username')
            ->where('v.username', 'LIKE', '%' . $search . '%')
            ->orWhere(DB::raw("v.fname + ' ' + v.lname"), 'LIKE', '%' . $search . '%')
            ->orWhere('v.cid', 'LIKE', '%' . $search . '%')
            ->orWhere('v.position', 'LIKE', '%' . $search . '%')
            ->orWhere('v.department', 'LIKE', '%' . $search . '%')
            ->select(
                'v.*',
                'u.birthday', // เปลี่ยนชื่อถ้าซ้ำ

            )
            ->limit(50)
            ->get();
        return view('pm.pm', compact('allPm'));
    }
}
