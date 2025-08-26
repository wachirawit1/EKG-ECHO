<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('user') || session('user.logged_in') !== true) {
            return redirect('/login')->with('error', 'กรุณาเข้าสู่ระบบก่อน');
        }
        return $next($request);
    }
}
