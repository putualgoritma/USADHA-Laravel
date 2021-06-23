<?php

namespace App\Http\Controllers\Admin;

use App\Ledger;
use App\Account;
use App\Http\Controllers\Controller;
use DB;

class AccbalanceController extends Controller
{
    public function mutation($id)
    {
        abort_unless(\Gate::allows('accbalance_access'), 403);

        $ledger_entries = Ledger::selectRaw("ledgers.register,ledgers.memo,ledger_entries.entry_type,ledger_entries.amount,accounts.name")
            ->rightjoin('ledger_entries', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->join('accounts', 'accounts.id', '=', 'ledger_entries.accounts_id')
            ->where('ledgers.status', '=', 'approved')
            ->where('ledger_entries.accounts_id', '=', $id)
            ->orderBy("ledgers.register", "asc")
            ->get();
        //return $ledger_entries;

        return view('admin.accounts.mutation', compact('ledger_entries'));
    }

    public function index()
    {
        abort_unless(\Gate::allows('accbalance_access'), 403);

        $accounts = Account::selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->leftJoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->where('ledgers.status', '=', 'approved')
            ->groupBy('accounts.id')
            ->get();
        //return $accounts;

        return view('admin.accounts.balance', compact('accounts'));
    }

    public function trial()
    {
        abort_unless(\Gate::allows('accbalance_access'), 403);

        $accounts_assets = DB::table('accounts')
            ->leftjoin('accounts_group', 'accounts.accounts_group_id', '=', 'accounts_group.id')
            ->leftjoin('accounts_type', 'accounts_group.accounts_type_id', '=', 'accounts_type.id')
            ->leftjoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->where('ledgers.status', '=', 'approved')
            ->where('accounts_type.id', '=', 1)
            ->groupBy('accounts.id')
            ->get();

        $accounts_liabilities = DB::table('accounts')
            ->leftjoin('accounts_group', 'accounts.accounts_group_id', '=', 'accounts_group.id')
            ->leftjoin('accounts_type', 'accounts_group.accounts_type_id', '=', 'accounts_type.id')
            ->leftjoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->where('ledgers.status', '=', 'approved')
            ->where('accounts_type.id', '=', 2)
            ->groupBy('accounts.id')
            ->get();

        $accounts_equity = DB::table('accounts')
            ->leftjoin('accounts_group', 'accounts.accounts_group_id', '=', 'accounts_group.id')
            ->leftjoin('accounts_type', 'accounts_group.accounts_type_id', '=', 'accounts_type.id')
            ->leftjoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->where('ledgers.status', '=', 'approved')
            ->where('accounts_type.id', '=', 3)
            ->groupBy('accounts.id')
            ->get();

        $accounts_revenues = DB::table('accounts')
            ->leftjoin('accounts_group', 'accounts.accounts_group_id', '=', 'accounts_group.id')
            ->leftjoin('accounts_type', 'accounts_group.accounts_type_id', '=', 'accounts_type.id')
            ->leftjoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->where('ledgers.status', '=', 'approved')
            ->where('accounts_type.id', '=', 4)
            ->groupBy('accounts.id')
            ->get();

        $accounts_expenses = DB::table('accounts')
            ->leftjoin('accounts_group', 'accounts.accounts_group_id', '=', 'accounts_group.id')
            ->leftjoin('accounts_type', 'accounts_group.accounts_type_id', '=', 'accounts_type.id')
            ->leftjoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->where('ledgers.status', '=', 'approved')
            ->where('accounts_type.id', '=', 5)
            ->groupBy('accounts.id')
            ->get();

        return view('admin.accounts.balancetrial', compact('accounts_assets', 'accounts_liabilities', 'accounts_equity','accounts_revenues','accounts_expenses'));
    }

    public function profitLoss()
    {
        abort_unless(\Gate::allows('accbalance_access'), 403);

        $accounts_revenues = DB::table('accounts')
            ->leftjoin('accounts_group', 'accounts.accounts_group_id', '=', 'accounts_group.id')
            ->leftjoin('accounts_type', 'accounts_group.accounts_type_id', '=', 'accounts_type.id')
            ->leftjoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->where('ledgers.status', '=', 'approved')
            ->where('accounts_type.id', '=', 4)
            ->groupBy('accounts.id')
            ->get();

        $accounts_expenses = DB::table('accounts')
            ->leftjoin('accounts_group', 'accounts.accounts_group_id', '=', 'accounts_group.id')
            ->leftjoin('accounts_type', 'accounts_group.accounts_type_id', '=', 'accounts_type.id')
            ->leftjoin('ledger_entries', 'ledger_entries.accounts_id', '=', 'accounts.id')
            ->leftjoin('ledgers', 'ledgers.id', '=', 'ledger_entries.ledgers_id')
            ->selectRaw("accounts.*,SUM(CASE WHEN ledger_entries.entry_type = 'D' THEN ledger_entries.amount ELSE 0 END) AS amount_debit,SUM(CASE WHEN ledger_entries.entry_type = 'C' THEN ledger_entries.amount ELSE 0 END) AS amount_credit")
            ->where('ledgers.status', '=', 'approved')
            ->where('accounts_type.id', '=', 5)
            ->groupBy('accounts.id')
            ->get();

        return view('admin.accounts.profitloss', compact('accounts_revenues','accounts_expenses'));
    }
}
