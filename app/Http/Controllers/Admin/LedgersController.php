<?php

namespace App\Http\Controllers\Admin;

use App\Ledger;
use App\Account;
use App\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Gate;
use App\Http\Requests\MassDestroyLedgerRequest;
use App\Http\Requests\StoreLedgerRequest;
use App\Http\Requests\UpdateLedgerRequest;
use Symfony\Component\HttpFoundation\Response;

class LedgersController extends Controller
{
    public function index()
    {
        abort_if(Gate::denies('ledger_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ledgers = Ledger::with('accounts')
        ->where('status', '=', 'approved')
        ->orderBy("id", "DESC")
        ->get();
        //return $ledgers;

        return view('admin.ledgers.index', compact('ledgers'));
    }

    public function create()
    {
        abort_if(Gate::denies('ledger_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $accounts = Account::all();

        return view('admin.ledgers.create', compact('accounts'));
    }

    public function store(StoreLedgerRequest $request)
    {
        //check balance D C
        $debit_total=0;
        $credit_total=0;
        $accounts = $request->input('accounts', []);
        $amounts = $request->input('amounts', []);
        $types = $request->input('types', []);
        for ($account=0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                if($types[$account]=="D"){
                    $debit_total +=$amounts[$account];
                }else{
                    $credit_total +=$amounts[$account];
                }
            }
        }

        if($debit_total==$credit_total && $debit_total>0){
            $ledger = Ledger::create($request->all());
            //store to ledger_entries
            for ($account=0; $account < count($accounts); $account++) {
                if ($accounts[$account] != '') {
                    $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account],'amount' => $amounts[$account]]);
                }
            }
            return redirect()->route('admin.ledgers.index');
        }else{
            return back()->withError('Neraca Tidak Balance!')->withInput();
        }
        
    }

    public function edit(Ledger $ledger)
    {
        abort_if(Gate::denies('ledger_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $accounts = Account::all();

        $ledger->load('accounts');

        return view('admin.ledgers.edit', compact('accounts', 'ledger'));
    }

    public function update(UpdateLedgerRequest $request, Ledger $ledger)
    {
        $ledger->update($request->all());

        $ledger->accounts()->detach();
        $accounts = $request->input('accounts', []);
        $amounts = $request->input('amounts', []);
        for ($account=0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                $ledger->accounts()->attach($accounts[$account], ['amount' => $amounts[$account]]);
            }
        }

        return redirect()->route('admin.ledgers.index');
    }

    public function show(Ledger $ledger)
    {
        abort_if(Gate::denies('ledger_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ledger->load('accounts');

        return view('admin.ledgers.show', compact('ledger'));
    }

    public function destroy(Ledger $ledger)
    {
        abort_if(Gate::denies('ledger_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ledger->accounts()->detach();
        $ledger->delete();

        return back();
    }

    public function massDestroy(MassDestroyLedgerRequest $request)
    {
        Ledger::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
