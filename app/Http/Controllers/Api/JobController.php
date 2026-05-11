<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Job;

class JobController extends Controller
{
    public function index(){
        $allJobs = Job::all();
        return response()->json($allJobs);
    }

    public function store(Request $request){
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin only.'], 403);
        }
        
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'vacancy' => 'required',
            'total_vacancy' => 'required',
        ]);

        $jobs = Job::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'vacancy' => $request->vacancy,
            'total_vacancy' => $request->total_vacancy,
            'status' => 'open'
        ]);

        return response()->json(['message'=>'job created successfully','job'=>$jobs],201);
    }

    public function update(Request $request, Job $job){
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin only.'], 403);
        }

        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'vacancy' => 'required',
            'total_vacancy' => 'required',
            'status' => 'in:open,closed'
        ]);

        $job->update($request->only(['title','description','vacancy','total_vacancy','status']));
        return response()->json(['message'=>'job updated successfully','job'=>$job]);

    }

    public function delete(Request $request, Job $job){
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Admin only.'], 403);
        }

        $job->delete();
        return response()->json(['message'=>'job deleted successfully']);
    }
}
