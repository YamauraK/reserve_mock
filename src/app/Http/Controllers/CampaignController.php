<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rows = Campaign::orderBy('start_date','desc')->paginate(20);
        return view('masters.campaigns.index', compact('rows'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('masters.campaigns.form', ['campaign' => new Campaign]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>['required','string','max:100'],
            'description'=>['nullable','string'],
            'start_date'=>['nullable','date'],
            'end_date'=>['nullable','date','after_or_equal:start_date'],
            'is_active'=>['required','boolean'],
        ]);
        Campaign::create($data);
        return redirect()->route('campaigns.index')->with('status','企画を作成しました');
    }

    /**
     * Display the specified resource.
     */
    public function show(Campaign $campaign)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Campaign $campaign)
    {
        return view('masters.campaigns.form', compact('campaign'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Campaign $campaign)
    {
        $data = $request->validate([
            'name'=>['required','string','max:100'],
            'description'=>['nullable','string'],
            'start_date'=>['nullable','date'],
            'end_date'=>['nullable','date','after_or_equal:start_date'],
            'is_active'=>['required','boolean'],
        ]);
        $campaign->update($data);
        return redirect()->route('campaigns.index')->with('status','企画を更新しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return back()->with('status','企画を削除しました');
    }
}
