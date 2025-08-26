<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = Account::where('username', $request->username)->first();

        $role = DB::connection('mysql')
            ->table('account_role')
            ->leftJoin('roles', 'account_role.role_id', '=', 'roles.id')
            ->where('account_role.username', $request->username)
            ->value('name');




        if ($user && strtoupper(md5($request->password)) === $user->password) {

            if (empty($role)) {
                return back()->withErrors([
                    'username' => 'ไม่มีสิทธิ์เข้าใช้งานระบบ',
                ])->withInput($request->only('username', 'password'));
            }

            Auth::login($user);

            // เก็บ session เพิ่มเติม
            Session::put('user', [
                'logged_in' => true,
                'user_id' => $user->userid,
                'username' => $user->username,
                'fullname' => $user->fname . ' ' . $user->lname,
                'role' => $role,
                'last_activity' => now(),
            ]);

            return redirect()->intended(route('index'))->with('success', 'เข้าสู่ระบบสำเร็จ');;
        }

        // ส่งกลับพร้อมข้อมูลเดิม รวมถึง password
        return back()->withErrors([
            'username' => 'Username หรือ Password ไม่ถูกต้อง',
        ])->withInput($request->only('username', 'password', 'remember'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        Session::forget('user');
        // ล้าง session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'ออกจากระบบสำเร็จ');;
    }
}
