<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\DB;

class CheckUserSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    // 720 นาที (12 ชั่วโมง)
    protected $timeout = 720;

    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('user') || Session::get('user.logged_in') !== true) {
            return redirect('/login')->with('error', 'กรุณาเข้าสู่ระบบก่อน');
        }

        $lastActivity = Session::get('user.last_activity');
        $now = now();

        // ตรวจสอบว่า session หมดอายุหรือไม่
        if ($now->diffInMinutes($lastActivity) > $this->timeout) {
            Session::forget('user');
            Auth::logout();
            return redirect('/login')->with('error', 'Session หมดอายุ กรุณาล็อคอินใหม่');
        }

        // อัปเดตเวลาการใช้งานล่าสุด
        Session::put('user.last_activity', $now);

        // Update Online Users Table
        try {
            $user = Session::get('user');
            DB::connection('mysql')->table('online_users')->updateOrInsert(
                ['user_id' => $user['user_id']],
                [
                    'fullname' => $user['fullname'],
                    'last_activity' => $now
                ]
            );
        } catch (\Exception $e) {
            // Ignore DB errors to prevent blocking the user
        }

        return $next($request);
    }
}
