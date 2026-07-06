<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class AjaxController extends Controller
{
    protected function resolveStorePanelUser(Request $request)
    {
        $email = trim((string) $request->email);
        $password = (string) $request->password;
        $uuid = trim((string) $request->id);

        if ($email === '') {
            return null;
        }

        $defaultPassword = $password !== '' ? $password : ($uuid !== '' ? $uuid : Str::random(40));

        $createPayload = [
            'name' => $email,
            'password' => Hash::make($defaultPassword),
        ];

        if (Schema::hasColumn('users', 'isSubscribed')) {
            $createPayload['isSubscribed'] = $request->isSubscribed;
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            $createPayload
        );

        $updatePayload = [];

        if (empty($user->name)) {
            $updatePayload['name'] = $email;
        }

        if ($password !== '' && Hash::needsRehash((string) $user->password)) {
            $updatePayload['password'] = Hash::make($password);
        }

        if (Schema::hasColumn('users', 'isSubscribed')) {
            $updatePayload['isSubscribed'] = $request->isSubscribed == null ? '' : $request->isSubscribed;
        }

        if (!empty($updatePayload)) {
            $user->update($updatePayload);
            $user->refresh();
        }

        return $user;
    }

    public function setToken(Request $request)
    {
        $email = trim((string) $request->email);
        $uuid = trim((string) $request->id);
        $data = ['access' => false];

        $user = $this->resolveStorePanelUser($request);

        if (!$user) {
            return response()->json($data, 422);
        }

        $vendorUser = DB::table('vendor_users')
            ->where('email', $email)
            ->when($uuid !== '', function ($query) use ($uuid) {
                $query->orWhere('uuid', $uuid);
            })
            ->first();

        $vendorPayload = [
            'user_id' => $user->id,
            'uuid' => $uuid,
            'email' => $email,
        ];

        if ($vendorUser) {
            DB::table('vendor_users')
                ->where('id', $vendorUser->id)
                ->update($vendorPayload);
        } else {
            DB::table('vendor_users')->insert($vendorPayload);
        }

        Auth::loginUsingId($user->id, true);

        if (Auth::check()) {
            $data['access'] = true;
        }

        return $data;
    }

    public function setSubcriptionFlag(Request $request)
    {
        if (Schema::hasColumn('users', 'isSubscribed')) {
            User::where('email', $request->email)->update([
                'isSubscribed' => $request->isSubscribed,
            ]);
        }

        $data = [];
        if (Auth::check()) {
            $data['access'] = true;
        }

        return $data;
    }

    public function logout(Request $request)
    {
        $authUser = Auth::user();
        $userId = $authUser ? $authUser->id : null;
        $user = $userId ? VendorUsers::where('user_id', $userId)->first() : null;

        try {
            Auth::logout();
        } catch (\Exception $e) {
            $this->sendError($e->getMessage(), 401);
        }

        $data1 = [];
        if (!Auth::check()) {
            $data1['logoutuser'] = true;
        }

        return $data1;
    }
}
