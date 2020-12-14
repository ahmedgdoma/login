<?php

namespace App\Http\Controllers;


use App\Job;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JobController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $jobs = Job::all();
        return response()->json(['data' => $jobs], 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function availableJobs()
    {
        $today = Carbon::today();
        $jobs = Job::where('start_date', '<', $today)->get();
        return response()->json(['data' => $jobs], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model = Job::find($id);
        if(!$model){
            return response()->json(['message' => 'job not found'], 400);
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
            'title' => 'required|string|max:255|unique:jobs,title',
            'required_experience_level' => 'required|string|max:255',
            'brief' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);
        $job = $request->all();
        $job['start_date'] = Carbon::createFromDate($job['start_date']);
        $job['end_date'] = Carbon::createFromDate($job['end_date']);
        try{
            Job::create($job);
        }catch (\Exception $e){
            return response()->json(['message' => $e->getMessage()], 400);
        }
        return response()->json(['message' => "Product {$request->get('title')} created Successfully"], 200);

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => "string|max:255|unique:jobs,title,{$id}",
            'required_experience_level' => 'string|max:255',
            'brief' => 'string',
            'start_date' => 'date',
            'end_date' => 'date|after:start_date',
        ]);
        $job = $request->all();
        if($job['start_date'])
            $job['start_date'] = Carbon::createFromDate($job['start_date']);
        if($job['end_date'])
            $job['end_date'] = Carbon::createFromDate($job['end_date']);
        $model = Job::find($id);
        if(!$model){
            return response()->json(['message' => 'job not found'], 400);
        }
        $model->update($request->all());
        return response()->json(['message' => "Job {$model->title} updated Successfully"], 200);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $model = Job::find($id);
        if(!$model){
            return response()->json(['message' => 'job not found'], 400);
        }
        $model->delete();
        return response()->json(['message' => "Job {$model->title} deleted Successfully"], 200);
    }
}
