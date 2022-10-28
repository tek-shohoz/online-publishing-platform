<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmailJob;
use App\Mail\PostNotificationMail;
use App\Models\User\Post;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{

    public function view_own($id){
        try{
            $post = Post::where('user_id', auth()->user()->id)->where("id", $id)->first();
            return response()->json([ "success"=> true,
                                    "msg" => "View post",
                                    "data" => $post
                                ],200);
        }catch(Exception $e){
            return response()->json([ "success"=> false,"msg" => "Server Error"],500);
        }
        
    }

    public function view_any($id){
        try{
            $post = Post::where("id", $id)->first();
            return response()->json([ "success"=> true,
                                    "msg" => "View post",
                                    "data" => $post
                                ],200);
        }catch(Exception $e){
            return response()->json([ "success"=> false,"msg" => "Server Error"],500);
        }
        
    }
  
    public function save_draft(Request $request){
        try{
            $validatedUserdata = Validator::make($request->all(),[
                'title' => 'required',
                'description' => 'required',
            ]);
    
            if($validatedUserdata->fails()){
                return response()->json([ 
                    "success"=> false,
                    "msg" => "Validation failed!",
                    "error" => $validatedUserdata->errors()
                ]);
            }


            $inputs = ['user_id'=> auth()->user()->id,'title' => $request->title,'description' => $request->description, 'status' => "draft"];
            $post = Post::create($inputs);
            return response()->json([ 
                "success"=> true,
                "msg" => "Draft saved successfully",
                "post" => $post
            ],201);   

        }catch(Exception $e){
            return response()->json([ "success"=> false,"msg" => "Server Error"],500);
        }
    }
    public function publish_uncreated(Request $request){
        $post = null;
        try{
            $validatedUserdata = Validator::make($request->all(),[
                'title' => 'required',
                'description' => 'required',
            ]);
    
            if($validatedUserdata->fails()){
                return response()->json([ 
                    "success"=> false,
                    "msg" => "Validation failed!",
                    "error" => $validatedUserdata->errors()
                ]);
            }


            $inputs = ['published_at'=>date('Y-m-d H:i:s'),'user_id'=> auth()->user()->id,'title' => $request->title,'description' => $request->description, 'status' => "published"];
            $post = Post::create($inputs); 

        }catch(Exception $e){
            return response()->json([ "success"=> false,"msg" => "Server Error"],500);
        }
        try{
            dispatch(new SendEmailJob($post));
        }catch(Exception $e){}


        return response()->json([ 
            "success"=> true,
            "post"=> $post,
            "msg" => "Published successfully",
        ],201);  

    }

    public function publish_created($id){
        $post = null;
        try{
            $post = Post::where('user_id', auth()->user()->id)->where("id",$id)->first();
            $post->status = "published";
            $post->published_at=date('Y-m-d H:i:s');
            $post->save();  

        }catch(Exception $e){
            return response()->json([ "success"=> false,"msg" => "Server Error"],500);
        }
        try{
            if($post){
                dispatch(new SendEmailJob($post));
            }
        }catch(Exception $e){}


        return response()->json([ 
            "success"=> true,
            "post"=> $post,
            "msg" => "Published successfully",
        ],201); 

    }

    public function schedule(Request $request){
        try{
            $validatedUserdata = Validator::make($request->all(),[
                'title' => 'required',
                'description' => 'required',
                'datetime' => 'required'
            ]);
    
            if($validatedUserdata->fails()){
                return response()->json([ 
                    "success"=> false,
                    "msg" => "Validation failed!",
                    "error" => $validatedUserdata->errors()
                ]);
            }

            $inputs = ['user_id'=> auth()->user()->id,
                        'title' => $request->title,
                        'description' => $request->description, 
                        'status' => "scheduled", 
                        'scheduled_at'=> $request->datetime];

            $post = Post::create($inputs);
            return response()->json([ 
                "success"=> true,
                "post"=> $post,
                "msg" => "Scheduled successfully",
            ],201);   

        }catch(Exception $e){
            return response()->json([ "success"=> false,"msg" => "Server Error"],500);
        }
    }
    public function edit(Request $request, $id){
        try{
            $validatedUserdata = Validator::make($request->all(),[
                'title' => 'required',
                'description' => 'required',
            ]);
    
            if($validatedUserdata->fails()){
                return response()->json([ 
                    "success"=> false,
                    "msg" => "Validation failed!",
                    "error" => $validatedUserdata->errors()
                ]);
            }


            $post = Post::where("id", $id)->where("user_id", auth()->user()->id)->first();
            $post->title = $request->title;
            $post->description = $request->description;
            $post->save();
            return response()->json([ 
                "success"=> true,
                "post"=> $post,
                "msg" => "Successfully edited",
            ],201);   

        }catch(Exception $e){
            return response()->json([ "success"=> false,"msg" => "Server Error"],500);
        }
    }
    public function delete($id){
        try{
            $res=Post::where('id', $id)->where('user_id', auth()->user()->id)->delete();
            if($res){
                return response()->json([ 
                    "success"=> true,
                    "msg" => "Successfully deleted",
                ],200); 
            }
            return response()->json([ 
                "success"=> false,
                "msg" => "Something went wrong",
            ],401); 
        }catch(Exception $e){
            return response()->json([ "success"=> false,"msg" => "Server Error"],500);
        }
    }
}
