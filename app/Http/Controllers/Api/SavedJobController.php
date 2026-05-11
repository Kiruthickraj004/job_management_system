<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;
use App\Models\SavedJob;
class SavedJobController extends Controller
{
    public function index(Request $request){
        $savedJobs = SavedJob::where('user_id', $request->user()->id)->with('job')->get();
        return response()->json(['saved_jobs' => $savedJobs]);
    }

    public function save(Request $request, Job $job){
        $savedJob = SavedJob::where('user_id',$request->user()->id)->where('job_id',$job->id)->exists();
        if($savedJob){
            return response()->json(['message' => 'job already saved'],400);
        }
        $save = SavedJob::create([
            'user_id' => $request->user()->id,
            'job_id' => $job->id 
        ]);
        return response()->json([
            'message'=>'job saved successfully'
        ],201);
    }

    public function unsave(Request $request, Job $job){
        $jobExist = SavedJob::where('user_id',$request->user()->id)->where('job_id',$job->id)->first();
        if(!$jobExist){
            return response()->json(['message'=>'job not found'],404);
        }
        $jobExist->delete();
        return response()->json(['message' => 'job unsaved successfully']);
    }
}
