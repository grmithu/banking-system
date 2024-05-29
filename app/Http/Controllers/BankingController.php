<?php

namespace App\Http\Controllers;


use App\Models\Transaction;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class BankingController extends Controller
{
    //

    public function showDepositForm()
    {
        return view('deposit');
    }


    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $amount = $request->input('amount');

        $user->balance += $amount;
        $user->save();

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => $amount,
        ]);

        return redirect()->back()->with('success', 'Deposit successful!');

    }




    public function showWithdrawForm()
    {
        return view('withdraw');
    }

    public function withdraw(Request $request)
    {

        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user();
        $amount = $request->input('amount');
        $fee = $this->calculateFee($amount);
        $totalAmount = $amount + $fee;

        if ($totalAmount > $user->balance) {
            return redirect()->back()->with('error', 'Insufficient balance.');
        }

        if ($this->exceedsDailyLimit($user, $amount)) {
            return redirect()->back()->with('error', 'Daily withdrawal limit exceeded.');
        }

        $monthlyWithdrawals = $this->getMonthlyWithdrawals($user);
        if ($monthlyWithdrawals >= 3) {
            $fee += 5;
            $totalAmount = $amount + $fee;
            if ($totalAmount > $user->balance) {
                return redirect()->back()->with('error', 'Insufficient balance for additional fee.');
            }
        }

        $user->balance -= $totalAmount;
        $user->save();

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'amount' => $amount,
            'fee' => $fee,
        ]);

        return redirect()->back()->with('success', 'Withdrawal successful!');
    }

    private function calculateFee($amount)
    {
        if ($amount > 1000) {
            return $amount * 0.02;
        }
        elseif ($amount >= 500) {
            return $amount * 0.01;
        }
        else {
            return 0;
        }
    }

    private function exceedsDailyLimit($user, $amount)
    {
        $dailyWithdrawals = $user->transactions()
            ->where('type', 'withdrawal')
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount');

        return ($dailyWithdrawals + $amount) > 3000;
    }

    private function getMonthlyWithdrawals($user)
    {
        return $user->transactions()
            ->where('type', 'withdrawal')
            ->whereMonth('created_at', now()->month)
            ->count();
    }






}
