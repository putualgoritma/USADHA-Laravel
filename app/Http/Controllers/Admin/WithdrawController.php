<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyWithdrawRequest;
use App\Http\Requests\StoreWithdrawRequest;
use App\Http\Requests\UpdateWithdrawRequest;
use App\Ledger;
use App\OrderPoint;
use App\Point;
use App\Withdraw;
use App\Traits\TraitModel;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Berkayk\OneSignal\OneSignalClient;
use OneSignal;
use App\LogNotif;

class WithdrawController extends Controller
{
    use TraitModel;
    private $onesignal_client;

    public function __construct()
    {
        //get env
        $this->onesignal_client = new OneSignalClient(env('ONESIGNAL_APP_ID_MEMBER'), env('ONESIGNAL_REST_API_KEY_MEMBER'), '');
    }

    public function index()
    {
        abort_if(Gate::denies('withdraw_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $withdraw = Withdraw::with('points')
            ->with('customers')
            ->with('accounts')    
            ->where('type', 'withdraw')
            ->orderBy("id", "DESC")
            ->get();

        return view('admin.withdraw.index', compact('withdraw'));
    }

    public function create()
    {
        abort_if(Gate::denies('withdraw_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $points = Point::all();
        $customers = Customer::select('*')
            ->get();
        $accounts = Account::select('*')
            ->where('accounts_group_id', 1)
            ->get();

        $last_code = $this->top_get_last_code();
        $code = acc_code_generate($last_code, 8, 3);

        return view('admin.withdraw.create', compact('points', 'customers', 'accounts', 'code'));
    }

    public function store(StoreWithdrawRequest $request)
    {
        //get total
        $total = 0;
        $points = $request->input('points', []);
        $amounts = $request->input('amounts', []);
        for ($point = 0; $point < count($points); $point++) {
            $total += $amounts[$point];
        }
        //proceed ledger withdraw
        $last_code = $this->get_last_code('withdraw');
        $code = acc_code_generate($last_code, 8, 3);
        $member = Customer::find($request->input('customers_id'));
        $memo="Withdraw Poin ".$member->code."-".$member->name;
        $data = ['register' => $request->input('register'), 'title' => $memo, 'memo' => $memo];
        $ledger = Ledger::create($data);
        $ledger_id = $ledger->id;
        //set ledger entry arr
        $acc_points = '67';
        $accounts = array($acc_points, $request->input('accounts_id'));
        $amounts_acc = array($total, $total);
        $types = array('C', 'D');
        //ledger entries
        for ($account = 0; $account < count($accounts); $account++) {
            if ($accounts[$account] != '') {
                $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts_acc[$account]]);
            }
        }

        //set def
        $customers_id = $request->input('customers_id');
        $warehouses_id = 1;
        //set withdraw
        $data = array_merge($request->all(), ['total' => $total, 'type' => 'withdraw', 'status' => 'approved', 'ledgers_id' => $ledger_id, 'customers_id' => $customers_id, 'payment_type' => 'cash', 'memo' => $memo, 'code' => $code]);
        $withdraw = Withdraw::create($data);
        //set withdraw points
        for ($point = 0; $point < count($points); $point++) {
            if ($points[$point] != '') {
                $withdraw->points()->attach($points[$point], ['amount' => $amounts[$point], 'type' => 'D', 'status' => 'onhand', 'customers_id' => $customers_id, 'memo' => $memo]);
            }
        }

        return redirect()->route('admin.withdraw.index');
    }

    public function edit(Withdraw $withdraw)
    {
        abort_if(Gate::denies('withdraw_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $points = Point::all();

        $withdraw->load('points');

        return view('admin.withdraw.edit', compact('points', 'withdraw'));
    }

    public function update(UpdateWithdrawRequest $request, Withdraw $withdraw)
    {
        $withdraw->update($request->all());

        $withdraw->points()->detach();
        $points = $request->input('points', []);
        $quantities = $request->input('quantities', []);
        for ($point = 0; $point < count($points); $point++) {
            if ($points[$point] != '') {
                $withdraw->points()->attach($points[$point], ['quantity' => $quantities[$point]]);
            }
        }

        return redirect()->route('admin.withdraw.index');
    }

    public function show(Withdraw $withdraw)
    {
        abort_if(Gate::denies('withdraw_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $withdraw->load('points');

        return view('admin.withdraw.show', compact('withdraw'));
    }

    public function destroy(Withdraw $withdraw)
    {
        abort_if(Gate::denies('withdraw_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $withdraw->delete();

        return back();
    }

    public function massDestroy(MassDestroyWithdrawRequest $request)
    {
        Withdraw::whereIn('id', request('ids'))->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function approved($id)
    {
        abort_if(Gate::denies('withdraw_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $withdraw = Withdraw::find($id);
        $accounts = Account::select('*')
            ->where('accounts_group_id', 1)
            ->get();

        return view('admin.withdraw.approved', compact('withdraw','accounts'));
    }

    public function approvedprocess(Request $request)
    {
        abort_unless(\Gate::allows('withdraw_show'), 403);
        if ($request->has('status')) {
            //get
            $withdraw = Withdraw::find($request->input('id'));

            /* proceed ledger */
            $memo='Withdraw Poin'.$withdraw->customers->code."-".$withdraw->customers->name;
            $data = ['register' => $withdraw->register, 'title' => $memo, 'memo' => $memo];
            //return $data;
            $ledger = Ledger::create($data);
            $ledgers_id = $ledger->id;
            //set ledger entry arr
            $acc_pay = $request->input('acc_pay');
            $acc_points = '67';//utang poin
            $total = $withdraw->total;
            $accounts = array($acc_points, $acc_pay);
            $amounts = array($total, $total);
            $types = array('D', 'C');
            //ledger entries
            for ($account = 0; $account < count($accounts); $account++) {
                if ($accounts[$account] != '') {
                    $ledger->accounts()->attach($accounts[$account], ['entry_type' => $types[$account], 'amount' => $amounts[$account]]);
                }
            }

            //update
            $withdraw->acc_pay = $request->input('acc_pay');
            $withdraw->status = 'approved';
            $withdraw->ledgers_id = $ledgers_id;
            $withdraw->save();
            //get list order points
            $orderpoint_arr = OrderPoint::select('*')
                ->where('orders_id', $withdraw->id)
                ->get();
            foreach ($orderpoint_arr as $key => $value) {
                $orderpoint = OrderPoint::find($value->id);
                $orderpoint->status = 'onhand';
                $orderpoint->save();
            }

            //push notif
            $user = Customer::find($withdraw->customers_id);
            $id_onesignal = $user->id_onesignal;
            $memo = 'Hallo ' . $user->name . ', Withdraw sejumlah '.$withdraw->total.' sudah disetujui.';
            $register = date("Y-m-d");
            //store to logs_notif
            $data = ['register' => $register, 'customers_id' => $withdraw->customers_id, 'memo' => $memo];
            $logs = LogNotif::create($data);
            //push notif
            if($user->type=='agent'){
                OneSignal::sendNotificationToUser(
                    $memo,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }else{
                $this->onesignal_client->sendNotificationToUser(
                    $memo,
                    $id_onesignal,
                    $url = null,
                    $data = null,
                    $buttons = null,
                    $schedule = null
                );
            }
        }
        return redirect()->route('admin.withdraw.index');

    }
}
