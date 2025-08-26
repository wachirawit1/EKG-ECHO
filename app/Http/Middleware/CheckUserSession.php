<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class CheckUserSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    protected $timeout = 60;

    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('user') || Session::get('user.logged_in') !== true) {
            return redirect('/login')->with('error', 'กรุณาเข้าสู่ระบบก่อน');
        }

        $lastActivity = Session::get('user.last_activity');
        $now = now();

        // ตรวจสอบว่า session หมดอายุหรือไม่
        if($now->diffInMinutes($lastActivity)>$this->timeout){
            Session::forget('user');
            Auth::logout();
            return redirect('/login')->with('error', 'Session หมดอายุ กรุณาล็อคอินใหม่');
        }

        // อัปเดตเวลาการใช้งานล่าสุด
        Session::put('user.last_activity', $now);
        
        return $next($request);
    }
}
