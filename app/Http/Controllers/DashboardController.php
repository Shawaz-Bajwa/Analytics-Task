<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsData;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $data = AnalyticsData::all();
        return view('dashboard', compact('data'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'profile_views' => 'required|integer',
            'visitors' => 'required|integer',
            'name' => 'required|string|max:255',
        ]);

        AnalyticsData::create($request->all());

        return redirect()->route('dashboard')->with('success', 'Data added successfully!');
    }

    public function edit($id)
    {
        $analytics = AnalyticsData::findOrFail($id);
        return response()->json($analytics);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'profile_views' => 'required|integer',
            'visitors' => 'required|integer',
            'name' => 'required|string|max:255',
        ]);

        $analytics = AnalyticsData::findOrFail($id);
        $analytics->update($request->all());

        return redirect()->route('dashboard')->with('success', 'Data updated successfully!');
    }

    public function destroy($id)
    {
        $analytics = AnalyticsData::findOrFail($id);
        $analytics->delete();

        return redirect()->route('dashboard')->with('success', 'Data deleted successfully!');
    }
}
