<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

// use Intervention\Image\Facades\Image;


class AuthController extends Controller
{
    private $app_name = env('APP_NAME');

    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|numeric|max:11|unique:users',
            'gender' => 'required|in:Male,Female',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->symbols()->numbers()],
        ]);

        $user_id = Str::upper(Str::random(7));

        $image = $request->file('image');
        $imageName = $user_id . '.' . $image->getClientOriginalExtension();
        $imagePath = $image->storeAs('', $imageName, 'images');

        // Image::make($imagePath)->resize(500, 500)->save($imagePath);

        $request->merge([
            'user_id' => $user_id,
            'role' => 'user',
            'created_by' => null,
            // 'password' => Hash::make('123456'), 
        ]);

        $inputs = $request->except(['image']);
        User::create($inputs);

        $password_mask = Str::mask($request->input('password'), '*', 3);

        $html = "
        Hello {$request->input('name')},
        <br>
        Your have successfully signed up to our platform.
        <br>
        Your can signin with your email, username, or phone number and the password: $password_mask
        <br>
        Regards.
        <br>
        Team {$this->app_name}.
        ";

        Mail::send([], [], function (Message $message) use ($html, $request) {
            $message
                ->to($request->input('email'))
                ->subject("{$this->app_name} - Account Signup")
                ->html($html);
        });

        return response()->json(['message' => 'User registered successfully'], 201);
    }

    public function signin(Request $request)
    {

        $request->validate([
            'info' => 'required',
            'password' => 'required',
            'name' => 'required',
            'platform' => 'required',
            'os' => 'required'
        ]);

        $user = User::where('email', $request->info)
            ->orWhere('phone', $request->info)
            ->orWhere('username', $request->info)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        } else {
            $expiry = $request->input('remember_me') ? now()->addDays(45) : now()->addDays(7);

            $sanctum = $user->createToken($request->name, ['*'], $expiry);
            $sanctum_token = $sanctum->plainTextToken;
            $sanctum_name = $sanctum->accessToken->name;
            $sanctum_id = $sanctum->accessToken->id;

            DB::table('personal_access_tokens')
                ->where('id', $sanctum_id)
                ->update($request->only(['platform', 'os']));

            $html = "
            Hello {$user->name},
            <br>
            Your have successfully signed into our platform.
            <br>
            If this was not you, please contact us for account reset.
            <br>
            Regards.
            <br>
            Team {$this->app_name}.
            ";

            Mail::send([], [], function (Message $message) use ($html, $user) {
                $message
                    ->to($user->email)
                    ->subject("{$this->app_name} - Account Signin")
                    ->html($html);
            });

            return response()->json([
                'status' => 'success',
                'token' => $sanctum_token,
                'user' => $user
            ], 200);
        }
    }

    public function signout(Request $request)
    {
        // $request->user()->currentAccessToken()->delete();
        $request->user->tokens()->delete(); // Revoke all tokens...

        return response()->json(['message' => 'User logged out successfully'], 200);
    }

    public function forgotpassword(Request $request)
    {
        $request->validate([
            'info' => 'required',
            'password' => 'required',
            'device_id' => 'required',
        ]);

        $user = User::where('email', $request->info)
            ->orWhere('phone', $request->info)
            ->orWhere('username', $request->info)
            ->first();

        $forgot_password = Str::upper(Str::random(8));
        User::where('id', $user->id)->update(['forgot_password' => $forgot_password]);

        $html = "
        Hello {$user->name},
        <br>
        Your have requested a password reset.
        <br>
        This is your reset pin: $forgot_password.
        <br>
        Regards.
        <br>
        Team {$this->app_name}.
        ";

        Mail::send([], [], function (Message $message) use ($html, $user) {
            $message
                ->to($user->email)
                ->subject("{$this->app_name} - Reset Password")
                ->html($html);
        });

        return response()->json(['message' => 'Reset Password Pin Sent'], 200);
    }

    public function resetforgotpassword(Request $request)
    {
        $request->validate([
            'forgot_password' => 'string|required',
            'email' => 'email',
            'phone' => 'numeric',
            'username' => 'string',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->symbols()->numbers()],
        ]);

        $user = User::where('forgot_password', $request->forgot_password)
            ->where(function ($query) use ($request) {
                $query->where('email', $request->email)
                    ->orWhere('phone', $request->phone)
                    ->orWhere('username', $request->username);
            })
            ->first();

        User::where('id', $user->id)->update(['forgot_password' => null, 'password' => $request->password]);

        $password_mask = Str::mask($request->input('password'), '*', 3);

        $html = "
        Hello {$user->name},
        <br>
        Your password reset was succesful.
        <br>
        Your new password is: $password_mask.
        <br>
        Regards.
        <br>
        Team {$this->app_name}.
        ";

        Mail::send([], [], function (Message $message) use ($html, $user) {
            $message
                ->to($user->email)
                ->subject("{$this->app_name} - Reset Password")
                ->html($html);
        });

        return response()->json(['message' => 'Password Successfully Reset'], 200);
    }
}
