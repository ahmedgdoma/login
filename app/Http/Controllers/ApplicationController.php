<?php

namespace App\Http\Controllers;


use App\Application;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ApplicationController extends Controller
{

    /**
     * Display a listing of the applications related to job
     *
     * @param $job_id
     * @return \Illuminate\Http\Response
     */
    public function jobApplications($job_id)
    {
        $Applications = Application::where('job_id', $job_id)->get();
        return response()->json(['data' => $Applications], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model = Application::find($id);
        if(!$model){
            return response()->json(['message' => 'Application not found'], 400);
        }
        return response()->json(['data' => $model], 200);
    }

    /**
     * create Product for admin
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request){
        $this->validate($request, [
            'job_id' => 'required|string|max:255|exists:jobs,id',
            'name' => 'required|string|max:255',
            'university' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'dob' => 'required|date|before:today',
            'cv' => 'required|file',
            'notes' => 'required|string',
        ]);
        $application = $request->all();
        $extension = explode('/', mime_content_type($application['cv']))[1];
        $cv_date = base64_decode($application['cv']);
        Storage::disk('local')->put("cv/".$application['name'].".". $extension , $cv_date);


        $url = Storage::url($application['name'].".". $extension);
        $application['cv'] = $url;
        $application['dob'] = Carbon::createFromDate($application['dob']);
        try{
            Application::create($application);
        }catch (\Exception $e){
            return response()->json(['message' => 'error creating Application'], 400);
        }
        return response()->json(['message' => "Application sent Successfully"], 200);

    }

}
