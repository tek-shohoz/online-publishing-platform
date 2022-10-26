<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\User\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Hash;
use App\Models\User\Membership;
class UserController extends Controller
{
    public function getUserData(){
        $user = auth()->user();
        $user['membership'] = Membership::where('user_id', auth()->user()->id)->orderBy('id', 'DESC')->first();
        return response()->json($user, 200);
    }

    public function updateUserData(Request $request){
        try{
            $validatedUserdata = Validator::make($request->all(),[
                'name' => 'required',
                'email' => 'required',
            ]);
    
            if($validatedUserdata->fails()){
                return response()->json([ 
                    "success"=> false,
                    "msg" => "Validation failed!",
                    "error" => $validatedUserdata->errors()
                ]);
            }

            $user = User::find(auth()->user()->id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->save();
            return response()->json([ "success"=> true,"msg" => "Successfully updated"],200);
        }catch(Exception $e){
            return response()->json([ "success"=> false,"msg" => "Server Error"],500);
        }
    }

    public function updateUserPassword(Request $request){
        try{
            $validatedUserdata = Validator::make($request->all(),[
                'old_password' => 'required',
                'new_password' => 'required',
            ]);
    
            if($validatedUserdata->fails()){
                return response()->json([ 
                    "success"=> false,
                    "msg" => "Validation failed!",
                    "error" => $validatedUserdata->errors()
                ]);
            }

            $user = User::find(auth()->user()->id);

            if(Hash::check($request->old_password, $user->password)){
                $user->password = Hash::make($request->new_password);
                $user->save();
                return response()->json([ "success"=> true,"msg" => "Successfully updated"],200);
            }
            return response()->json([ "success"=> false,"msg" => "Incorrect Password!"],401);
        }catch(Exception $e){
            return response()->json([ "success"=> false,"msg" => "Server Error"],500);
        }
    }

    public function switchUserMembership(Request $request){
        try{
            $validatedUserdata = Validator::make($request->all(),[
                'type' => 'required',
                'expire_at' => 'required',
                'cost' => 'required',
            ]);
    
            if($validatedUserdata->fails()){
                return response()->json([ 
                    "success"=> false,
                    "msg" => "Validation failed!",
                    "error" => $validatedUserdata->errors()
                ]);
            }


            $inputs = ['user_id'=> auth()->user()->id,'type' => $request->type,'cost' => $request->cost, 'expire_at' => $request->expire_at];
            $membership = Membership::create($inputs);
            return response()->json([ 
                "success"=> true,
                "msg" => "Updated membership successfully",
            ],201);   

        }catch(Exception $e){
            return response()->json([ "success"=> false,"msg" => "Server Error"],500);
        }
    }

    public function userPostsDraft(){
        try{
            $posts = Post::where('user_id', auth()->user()->id)
            ->where('status', 'draft')
            ->get();
            return response()->json([ "success"=> true,
                                    "msg" => "User's drafts",
                                    "total_records"=> $posts->count(),
                                    "data" => $posts
                                ],200);
        }catch(Exception $e){
            return response()->json([ "success"=> false,"msg" => "Server Error"],500);
        }
    }    

    public function userPostsScheduled(){
        try{
            $posts = Post::where('user_id', auth()->user()->id)
            ->where('status', 'sheduled')
            ->get();
            return response()->json([ "success"=> true,
                                    "msg" => "User's drafts",
                                    "total_records"=> $posts->count(),
                                    "data" => $posts
                                ],200);
        }catch(Exception $e){
            return response()->json([ "success"=> false,"msg" => "Server Error"],500);
        }
    }   
    
    public function userPostsPublished(){
        try{
            $posts = Post::where('user_id', auth()->user()->id)
            ->where('status', 'published')
            ->get();
            return response()->json([ "success"=> true,
                                    "msg" => "User's Published",
                                    "total_records"=> $posts->count(),
                                    "data" => $posts
                                ],200);
        }catch(Exception $e){
            return response()->json([ "success"=> false,"msg" => "Server Error"],500);
        }
    }   

    public function userFeedLoad(){
        try{
            $posts = Post::where('user_id','!=',auth()->user()->id)
            ->where('status', 'published')
            ->orderBy('created_at', "DESC")
            ->get();
            return response()->json([ "success"=> true,
                                    "msg" => "User's drafts",
                                    "total_records"=> $posts->count(),
                                    "data" => $posts
                                ],200);
        }catch(Exception $e){
            return response()->json([ "success"=> false,"msg" => "Server Error"],500);
        }
    }   
}
