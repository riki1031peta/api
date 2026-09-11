<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // ユーザー一覧
    public function index()
    {
        $users = User::latest()->get();
        return response()->json($users);
    }

    // ユーザー詳細
    public function show(User $user)
    {
        return response()->json($user);
    }

    // ユーザー作成
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'icon' => ['nullable', 'string'],
        ]);

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        return response()->json([
            'message' => '会員登録が完了しました。',
            'user' => $user,
        ], 201);
    }

    // ユーザー更新
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'icon' => ['nullable', 'string'],
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    // ユーザー削除
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'ユーザーを削除しました。',
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'メールアドレスまたはパスワードが正しくありません。',
            ], 401);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'ログインしました。',
            'user' => Auth::user(),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
    
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    
        return response()->json([
            'message' => 'ログアウトしました。',
        ]);
    }
    
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}