<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuAccessController extends Controller
{
    function getByUserGroup(Request $request)
    {
        $data = DB::connection('sqlsrv_wms')->table('MENU_TBL')
            ->leftJoin('EMPACCESS_TBL', 'MENU_ID', '=', 'EMPACCESS_MENUID')
            ->where('EMPACCESS_GRPID', $request->group_id)
            ->whereNotNull('MENU_DESKTOP')
            ->orderBy('MENU_ID')
            ->get([
                DB::raw("concat(MENU_ID, '#', MENU_DESKTOP) as MENU_ID"),
                'MENU_DSCRPTN',
                'MENU_NAME',
                DB::raw("concat(MENU_PRNT, '#', MENU_DESKTOP) as MENU_PRNT"),
                'MENU_URL',
                'MENU_ICON',
                'MENU_STT',
            ]);

        return ['message' => 'found', 'data' => $data];
    }
}
