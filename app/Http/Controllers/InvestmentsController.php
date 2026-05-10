<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\InvestmentVote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InvestmentsController extends Controller
{
    public function index(): View
    {
        $investments = Investment::with(['creator', 'votes'])->orderByDesc('created_at')->get();
        $totalInvested = $investments->sum('amount_invested');
        $totalProfit = $investments->sum('profit');
        $activeInvestments = $investments->where('status', 'active')->count();

        return view('pages.investments.index', compact('investments', 'totalInvested', 'totalProfit', 'activeInvestments'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (Auth::user()->role?->role_name !== 'Admin') {
            abort(Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount_invested' => ['required', 'numeric', 'min:0.01'],
            'expected_return' => ['nullable', 'numeric', 'min:0'],
            'investment_type' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
        ]);

        Investment::create([
            'title' => $request->title,
            'description' => $request->description,
            'amount_invested' => $request->amount_invested,
            'expected_return' => $request->expected_return,
            'investment_type' => $request->investment_type,
            'status' => 'pending',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('investments')->with('success', 'Investment created successfully');
    }

    public function update(Request $request, Investment $investment): RedirectResponse
    {
        if (Auth::user()->role?->role_name !== 'Admin') {
            abort(Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount_invested' => ['required', 'numeric', 'min:0.01'],
            'expected_return' => ['nullable', 'numeric', 'min:0'],
            'profit' => ['nullable', 'numeric'],
            'status' => ['required', 'in:pending,approved,active,completed,rejected'],
            'investment_type' => ['required', 'string', 'max:255'],
        ]);

        $investment->update($request->only('title', 'description', 'amount_invested', 'expected_return', 'profit', 'status', 'investment_type'));

        return redirect()->route('investments')->with('success', 'Investment updated successfully');
    }

    public function destroy(Investment $investment): RedirectResponse
    {
        if (Auth::user()->role?->role_name !== 'Admin') {
            abort(Response::HTTP_FORBIDDEN);
        }

        $investment->delete();

        return redirect()->route('investments')->with('success', 'Investment deleted successfully');
    }

    public function vote(Request $request, Investment $investment): RedirectResponse
    {
        $request->validate([
            'vote' => ['required', 'in:approve,reject,abstain'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        InvestmentVote::updateOrCreate(
            ['investment_id' => $investment->id, 'user_id' => Auth::id()],
            ['vote' => $request->vote, 'comment' => $request->comment]
        );

        return back()->with('success', 'Your vote has been recorded');
    }
}
