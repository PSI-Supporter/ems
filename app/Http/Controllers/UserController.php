<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Jakarta');
    }

    public function login(Request $request)
    {
        $dataReq = $request->json()->all();
        $data = [
            'nick_name' => $dataReq['username'],
            'password' => $dataReq['password'],
            'active' => '1',
        ];

        if (Auth::attempt($data)) {
            $user = User::where('nick_name', $dataReq['username'])->first();
            $user->token = $user->createToken($dataReq['password'] . 'bebas')->plainTextToken;
            return $user;
        } else {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => [
                        'username or password wrong'
                    ],
                    'data' => $data
                ]
            ], 401));
        }
    }

    public function logout(Request $request)
    {
        $data = $request->user('sanctum')->currentAccessToken()->delete();
        return ['message' => 'Log out successfully', $data];
    }

    public function loginCIDesktop(Request $request)
    {
        $currrtime = date('Y-m-d H:i:s');
        $respon = [];


        $validator = Validator::make($request->all(), [
            'inputUserid' => 'required',
            'inputPassword' => 'required',
        ], [
            'inputUserid.required' => ':attribute is required',
            'inputPassword.required' => ':attribute is required',
        ]);

        $finalMessage = '';
        $fname = '';
        $fullname = '';

        if ($validator->fails()) {
            $finalMessage = 'sorry please try again';
        } else {
            $username = $request->inputUserid;

            $password = $request->inputPassword;
            $where = [
                'MSTEMP_ID' => $username,
                'MSTEMP_PW' => hash('sha256', $password),
                'MSTEMP_ACTSTS' => true,
                'MSTEMP_STS' => true,
            ];

            $dlogses = DB::connection('sqlsrv_wms')->table('MSTEMP_TBL')->where($where)->get([
                'MSTEMP_FNM',
                'MSTEMP_GRP',
                DB::raw("DATEDIFF(DAY,MSTEMP_LCHGPWDT,GETDATE()) DAY_AFTER_CHANGE_PW"),
                DB::raw("concat(MSTEMP_FNM, ' ', MSTEMP_LNM) FULLNAME"),
            ]);
            $dloghis = DB::connection('sqlsrv_wms')->table('v_lastidlgusrplus')->get();

            $idlog = '';
            $m_grupid = '';
            $day_after_change_pw = 0;
            foreach ($dlogses as $r) {
                $fname = $r->MSTEMP_FNM;
                $day_after_change_pw = $r->DAY_AFTER_CHANGE_PW;
                $m_grupid = $r->MSTEMP_GRP;
                $fullname = $r->FULLNAME;
            }
            foreach ($dloghis as $dloghis) {
                $idlog = $dloghis->idnew;
            }
            $data_log = [
                'USRLOG_ID' => $idlog,
                'USRLOG_USR' => $username,
                'USRLOG_GRP' => $m_grupid,
                'USRLOG_TYP' => $dlogses ? 'LGIN' : 'LGFLR',
                'USRLOG_TM' => $currrtime,
                'USRLOG_IP' => $request->ip(),
            ];

            if (strlen($fname) > 0) {
                $rsPWPolicy = DB::connection('sqlsrv_wms')->table('PWPOL_TBL')->get(['PWPOL_MAXAGE']);
                $shouldChangePassword = false;
                foreach ($rsPWPolicy as $r) {
                    if ($day_after_change_pw > $r->PWPOL_MAXAGE) {
                        $shouldChangePassword = true;
                    }
                }

                if (!$shouldChangePassword) {
                    $finalMessage = 'OK';
                } else {
                    $finalMessage = 'Need to change password';
                }
            } else {
                $finalMessage = 'sorry please try again, check user id and password';
            }
            DB::connection('sqlsrv_wms')->table('USRLOG_TBL')->insert($data_log);
        }
        return [
            'message' => $finalMessage,
            'data' => [
                'user_group' => $m_grupid,
                'first_name' => $fname,
                'full_name' => $fullname
            ]
        ];
    }
}
