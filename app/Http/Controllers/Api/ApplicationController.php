<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;
use App\Models\Application;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    public function apply(Request $request, Job $job){
       
       
       try{
        DB::beginTransaction();
        $job = Job::where('id',$job->id)->lockForUpdate()->first();
        if($job->vacancy <= 0){
            throw new \Exception('no vacancies left');
        }
        if(Application::where('user_id',$request->user()->id)->where('job_id',$job->id)->exists()){
            throw new \Exception('you already applied');
        }
        $application = Application::create([
            'user_id' => $request->user()->id,
            'job_id' => $job->id,
            'status' => 'applied',
            'applied_at' => now()
        ]);
        $job->decrement('vacancy');
        DB::commit();
        return response()->json(['message' => 'job applied successfully'],201);
       }catch(\Exception $e){
        DB::rollback();
        return response()->json(['message'=>$e->getMessage()]);
       }
    }

    public function withdraw(Request $request, Job $job){
        try{
            DB::beginTransaction();
            $application = Application::where('user_id',$request->user()->id)->where('job_id',$job->id)->first();
            if(!$application){
                throw new \Exception('not applied for this job');
            }
            if($application->status === 'withdrawn'){
                throw new \Exception('application already withdrawn');
            }
            $application->update(['status'=>'withdrawn','withdrawn_at'=>now()]);
            $job->increment('vacancy');
            DB::commit();
            return response()->json(['message' => 'job withdrawn successfully']);

        }catch(\Exception $e){
            DB::rollback();
            return response()->json(['message'=> $e->getMessage()]);
        }
    }

    public function myApplications(Request $request){
        $application = Application::where('user_id',$request->user()->id)->latest()->get();
        if(!$application){
            return response()->json(['message' => 'no applications found']);
        }

        return response()->json(['applications' => $application]);
    }

    public function applicants(){
        $applicants = Application::all();
        return response()->json($applicants);
    }
}
