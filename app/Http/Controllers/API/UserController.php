<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',


        ]);

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ]);
        } else {


            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'success' => true,
                'msg' => 'User Register Successfully!',
                'user_details' => $user,
            ]);
        }
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [


            'email' => 'required|email',
            'password' => 'required',



        ]);

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ]);
        } else {



            $user  = User::where('email', $request->email)->first();
            //dd($user);


            if (!empty($user)) {

                //dd(Hash::check($request->password,$user->password));
                if (Hash::check($request->password, $user->password,)) {


                    return response()->json([

                        'success' => true,

                        'message' => 'User login successfully.',

                        'user_data'    => $user,
                        'token' => $user->createToken('token')->plainTextToken,

                    ]);
                } else {

                    return response()->json([
                        'success' => false,
                        'message' => 'Email id or password is incorrect!',

                    ]);
                }
            } else {


                return response()->json([
                    'success' => false,
                    'message' => 'Email id or password is incorrect!',

                ]);
            }
        }
    }

    public function addWalletAmount(Request $request)
    {


        $validator = Validator::make($request->all(), [


            'wallet_amt' => 'required|numeric|min:3|max:100',
            'user_id' => 'required',




        ]);

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ]);
        }

        $user  = User::find($request->user_id);

        if ($user) {

            // add wallet amount

           $userwal =  UserWallet::where('user_id',$request->user_id)->first();
           $previousAmt = '0.00';
            if($userwal){
                $previousAmt =$userwal->wallet_amt;
            }
            $addamt = UserWallet::updateOrCreate([
                'user_id' => $request->user_id,

            ], [
                'wallet_amt' => $previousAmt + $request->wallet_amt,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Wallet amount added successfully!',
                'walletDetails' => $addamt,

            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User is not registered with Us!',

            ]);
        }
    }

    public function logout(Request $request)
    {
        $user  = User::find($request->user_id);


        if ($user) {
            Auth::user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully!',

            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User is not registered with Us!',

            ]);
        }
    }

    public function buyCookie(Request $request)
    {

        $validator = Validator::make($request->all(), [


            'amt' => 'required|numeric|min:1|max:1',
            'user_id' => 'required',




        ]);

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ]);
        }
        $user  = User::find($request->user_id);

        if ($user) {


            $updateWallet = UserWallet::where('user_id', $user->id)->first();
            $totalAmt = $updateWallet->wallet_amt;
            $finalAmt = $totalAmt - $request->amt;
            if ($updateWallet) {


                $updateWallet->wallet_amt = $finalAmt;
                $updateWallet->save();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Please add wallet amount first',
                ]);
            }
        } else {

            return response()->json([
                'success' => false,
                'message' => 'User is not registered with Us!',

            ]);
        }






        return response()->json([
            'success' => true,
            'message' => 'Cookie Buy successfully',
            'walletDetails' => $updateWallet,

        ]);
    }
}
