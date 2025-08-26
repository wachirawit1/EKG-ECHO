<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Account;

class AdminController extends Controller
{
    public function adminPage()
    {
        // users
        $users = DB::connection('sqlsrv2')
            ->table('vwUserInfo')
            ->orderBy('cid', 'asc')
            ->limit(10)
            ->get();

        $account_roles = DB::connection('mysql')
            ->table('account_role')
            ->leftJoin('roles', 'account_role.role_id', '=', 'roles.id')
            ->get()
            ->keyBy('username');

        $users = $users->map(function ($user) use ($account_roles) {
            $role = $account_roles->get($user->username); //match username

            $user->role_id = $role->role_id ?? null;
            $user->role_name = $role->name ?? '';

            return $user;
        });

        // roles
        $roles = DB::connection('mysql')
            ->table('roles')
            ->get();

        return view('admin.admin', compact('users', 'roles'));
    }

    public function findUser(Request $request)
    {
        $search = $request->input('search');

        // ดึง roles จาก MySQL
        $roles = DB::connection('mysql')
            ->table('roles')
            ->get();

        // ดึง account_role มาจับคู่กับ users
        $account_roles = DB::connection('mysql')
            ->table('account_role')
            ->leftJoin('roles', 'account_role.role_id', '=', 'roles.id')
            ->get()
            ->keyBy('username');

        // ค้นหาผู้ใช้จาก SQL Server
        $users = DB::connection('sqlsrv2')
            ->table('vwUserInfo')
            ->where('username', 'LIKE', '%' . $search . '%')
            ->orWhere('fname', 'LIKE', '%' . $search . '%')
            ->orWhere('lname', 'LIKE', '%' . $search . '%')
            ->orderBy('cid', 'asc')
            ->limit(10)
            ->get();

        // แมพ role ให้ users
        $users = $users->map(function ($user) use ($account_roles) {
            $role = $account_roles->get($user->username); // match username
            $user->role_id = $role->role_id ?? null;
            $user->role_name = $role->name ?? '';
            return $user;
        });

        return view('admin.admin', compact('users', 'roles'));
    }


    // กำหนดสิทธิ์
    public function setRole(Request $request, $username)
    {
        $request->validate([
            'role' => 'required|exists:roles,id',
        ]);

        // เช็คว่าผู้ใช้มีสิทธิ์อยู่แล้วหรือไม่
        $existing =  DB::connection('mysql')
            ->table('account_role')
            ->where('username', $username)
            ->get();

        if ($existing->isNotEmpty()) {
            DB::connection('mysql')
                ->table('account_role')
                ->where('username', $username)
                ->update([
                    'role_id' => $request->role,
                ]);
            return response()->json(['success' => 'กำหนดสิทธิ์สำเร็จ']);
        }

        // เพิ่มสิทธิ์ใหม่
        DB::connection('mysql')
            ->table('account_role')
            ->insert([
                'username' => $username,
                'role_id' => $request->role,
            ]);

        return response()->json(['success' => 'กำหนดสิทธิ์สำเร็จ']);
    }
    // ลบผู้ใช้
    public function destroyUser($username)
    {
        try {
            //ตรวจสอบว่าผู้ใช้มีบัญชีในตาราง account หรือไม่
            $hasRole = DB::connection('mysql')
                ->table('account_role')
                ->where('username', $username)
                ->exists();

            if (!$hasRole) {
                return response()->json(['error' => 'ไม่พบผู้ใช้ในระบบ'], 404);
            }

            // ลบจากตาราง account_role ก่อน
            DB::connection('mysql')
                ->table('account_role')
                ->where('username', $username)
                ->delete();

            return redirect()->route('admin')->with('success', 'ลบผู้ใช้สำเร็จ');
        } catch (\Exception $e) {
            return response()->json(['error' => 'เกิดข้อผิดพลาดในการลบผู้ใช้: ' . $e->getMessage()], 500);
        }
    }

    // จัดการศสิทธิ์
    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
        ]);

        $insert = DB::table('roles')->insert([
            'name' => $request->name,
        ]);

        if ($insert) {
            return redirect()->route('admin')->with('success', 'เพิ่มสิทธิ์สำเร็จ');
        } else {
            return redirect()->route('admin')->with('error', 'เพิ่มสิทธิ์ไม่สำเร็จ');
        }
    }

    public function destroyRole($id)
    {
        try {
            // ตรวจสอบว่ามีการใช้งานสิทธิ์นี้ในตาราง account_role หรือไม่
            $isUsed = DB::connection('mysql')
                ->table('account_role')
                ->where('role_id', $id)
                ->exists();

            if ($isUsed) {
                return redirect()->route('admin')->with('error', 'ไม่สามารถลบสิทธิ์นี้ได้ เนื่องจากมีการใช้งานอยู่');
            }

            // ลบสิทธิ์
            DB::connection('mysql')
                ->table('roles')
                ->where('id', $id)
                ->delete();

            return redirect()->route('admin')->with('success', 'ลบสิทธิ์สำเร็จ');
        } catch (\Exception $e) {
            return redirect()->route('admin')->with('error', 'เกิดข้อผิดพลาดในการลบสิทธิ์: ' . $e->getMessage());
        }
    }
}
