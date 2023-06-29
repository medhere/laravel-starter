<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Mail\Message;
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
        if ($request->isMethod('get')) {
            return view('auth.signup');
        }

        if ($request->isMethod('post')) {
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
    
            Mail::send([], [], function (Message $message) use ($html, $request){
                $message
                    ->to($request->input('email'))
                    ->subject("{$this->app_name} - Account Signup")
                    ->html($html);
            });
    
            return back()->with('success', 'success');
        }
    }


    public function getsignin(Request $request)
    {
        if ($request->isMethod('get')) {
            if (auth()->check()) {
                if (auth()->user()->role === 'user') {
                    return redirect()->intended('user');
                } else {
                    return redirect()->intended('admin');
                }
            } else {
                return view('auth.signin');
            }
        }

        if ($request->isMethod('post')) {
            $request->validate([
                'info' => 'required',
                'password' => 'required'
            ]);

            $user = User::where('email', $request->info)
                ->orWhere('phone', $request->info)
                ->orWhere('username', $request->info)
                ->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return back()->with('failed', 'Incorrect Email/Password');
            } else {
   
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
        
                Mail::send([], [], function (Message $message) use ($html, $user){
                    $message
                        ->to($user->email)
                        ->subject("{$this->app_name} - Account Signin")
                        ->html($html);
                });
    
                Auth::login($user, $request->remember_me ? true : false);
                $request->session()->regenerate();
                return redirect()->intended(auth()->user()->role);
            }
        }
    }

    public function signout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('signin'));
    }

    public function forgotpassword(Request $request)
    {

        if ($request->isMethod('get')) {
            return view('auth.forgotpassword');
        }

        if ($request->isMethod('post')) {

            $request->validate([
                'info' => 'required',
                'password' => 'required'
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
    
            Mail::send([], [], function (Message $message) use ($html, $user){
                $message
                    ->to($user->email)
                    ->subject("{$this->app_name} - Reset Password")
                    ->html($html);
            });
        
            return view('auth.forgotpassword')->with(['success' => 'Reset Password Pin Sent']);
        }
    }

    public function resetforgotpassword(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('auth.resetforgotpassword');
        }

        if ($request->isMethod('post')) {

            $request->validate([
                'forgot_password' => 'string|required',
                'info' => 'required',
                'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->symbols()->numbers()],
            ]);
            
            $user = User::where('forgot_password', $request->forgot_password)
                ->where(function ($query) use ($request) {
                    $query->where('email', $request->info)
                        ->orWhere('phone', $request->info)
                        ->orWhere('username', $request->info);
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
    
            Mail::send([], [], function (Message $message) use ($html, $user){
                $message
                    ->to($user->email)
                    ->subject("{$this->app_name} - Reset Password")
                    ->html($html);
            });
    
            //send mail or sms with $forgot_password pin
            return view('auth.resetforgotpassword')->with(['success' => 'Password Successfully Reset']);
        }
    }
}
