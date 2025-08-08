<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;


class TestController extends Controller
{
    public function renderView()
    {
        $targetDoc = [' 21116', ' 22947', ' 26587', ' 33166', ' 34559', ' 37288', ' 36155', ' 34916'];
        $today = Carbon::now()->addYears(543)->format('Ymd');

        $appoint = DB::connection('sqlsrv')
            ->table('Appoint')
            ->leftJoin('DOCC', 'Appoint.doctor', '=', 'DOCC.docCode')
            ->whereIn('Appoint.doctor', $targetDoc) 
            // ->where('Appoint.appoint_date', '=', $today)
            ->orderBy('Appoint.appoint_date')
            ->paginate(10);
        return view('querytest', compact('appoint'));
    }
}
