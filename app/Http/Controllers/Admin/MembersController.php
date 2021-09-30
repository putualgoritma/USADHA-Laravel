<?php

namespace App\Http\Controllers\Admin;

use App\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyMemberRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Member;
use App\Order;
use App\Ledger;
use App\OrderDetails;
use App\OrderPoint;
use App\Package;
use App\Product;
use Hashids;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class MembersController extends Controller
{

    public function unblock($id)
    {
        abort_if(\Gate::denies('member_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $member = Member::find($id);

        return view('admin.members.unblock', compact('member'));
    }

    public function unblockProcess(Request $request)
    {
        abort_unless(\Gate::allows('member_show'), 403);
        if ($request->has('status_block')) {
            //get
            $member = Member::find($request->input('id'));
            //update
            $member->status_block = '0';
            $member->save();
        }
        return redirect()->route('admin.members.index');

    }

    public function index(Request $request)
    {
        abort_unless(\Gate::allows('member_access'), 403);

        //$from = !empty($request->from) ? $request->from : date('Y-m-01'); 
        $from = !empty($request->from) ? $request->from : '';
        $to = !empty($request->to) ? $request->to :date('Y-m-d'); 


        if ($request->ajax()) {
            $query = Member::selectRaw("customers.*,(SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END)) AS amount_balance")
                ->whereBetween('customers.activation_at', [$from, $to])
                ->leftJoin('order_points', 'order_points.customers_id', '=', 'customers.id')
                ->where(function ($qry) {
                    $qry->where('customers.type', '=', 'member')
                        ->orWhere('customers.def', '=', '1');
                })
                ->orderBy("customers.activation_at", "DESC")
                ->groupBy('customers.id')
                ->FilterInput()
                ->get();
            foreach ($query as $key => $value) {
                $query[$key]->ref_link = env('APP_URL') . "/member?ref=" . Hashids::encode($value->id);
            }
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate = 'member_show';
                $editGate = 'member_edit';
                $deleteGate = 'member_delete';
                $crudRoutePart = 'members';

                return view('partials.datatablesMembers', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            // $table->editColumn('no', function ($row) {
            //     return $row->index ? $row->index : "";
            // });

            $table->editColumn('code', function ($row) {
                return $row->code ? $row->code : "";
            });

            $table->editColumn('register', function ($row) {
                return $row->activation_at ? $row->activation_at : "";
            });

            $table->editColumn('name', function ($row) {
                return $row->name ? $row->name : "";
            });

            $table->editColumn('email', function ($row) {
                return $row->email ? $row->email : "";
            });

            $table->editColumn('phone', function ($row) {
                return $row->phone ? $row->phone : "";
            });

            $table->editColumn('status', function ($row) {
                return $row->status ? $row->status : "";
            });

            $table->editColumn('saldo', function ($row) {
                return number_format($row->amount_balance, 2) ? $row->amount_balance : 0;
            });

            $table->editColumn('ref_link', function ($row) {
                return $row->ref_link ? $row->ref_link : "";
            });

            $table->rawColumns(['actions', 'placeholder']);

            $table->addIndexColumn();

            //     $table->filter(function ($instance) use ($request) {
            //        if ($request->get('status') == '0' || $request->get('status') == '1') {
            //            $instance->where('status', $request->get('status'));
            //        }
            //        if (!empty($request->get('search'))) {
            //             $instance->where(function($w) use($request){
            //                $search = $request->get('search');
            //                $w->orWhere('name', 'LIKE', "%$search%")
            //                ->orWhere('email', 'LIKE', "%$search%");
            //            });
            //        }
            //    });

            return $table->make(true);
        }

        // $orders_id=104;
        // $agents_id=42;
        // $order = Order::with('products')
        // ->with('customers')
        // ->get()->find($orders_id);
        // $agent = Customer::find($agents_id);

        // return view('email.order')
        // ->with([
        //     'order' => $order,
        //     'agent' => $agent,
        // ]);

        $members = Member::selectRaw("customers.*,(SUM(CASE WHEN order_points.type = 'D' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END) - SUM(CASE WHEN order_points.type = 'C' AND order_points.status = 'onhand' AND order_points.points_id = '1' THEN order_points.amount ELSE 0 END)) AS amount_balance")
            ->leftJoin('order_points', 'order_points.customers_id', '=', 'customers.id')
            ->where('customers.type', '=', 'member')
            ->orderBy('customers.activation_at')
            ->groupBy('customers.id')
            ->get();

        $out_val = array();
        foreach ($members as $key => $value) {
            $members[$key]->ref_link = env('APP_URL') . "/member?ref=" . Hashids::encode($value->id);
        }

        $ref_def_id = Customer::select('id')
            ->Where('def', '=', '1')
            ->get();

        $ref_def_link = env('APP_URL') . "/member?ref=" . Hashids::encode($ref_def_id[0]->id);

        return view('admin.members.index', compact('members', 'ref_def_link'));
    }

    public function create()
    {
        abort_unless(\Gate::allows('member_create'), 403);

        $products = Product::where('type', '=', 'package')
            ->get();
        $referals = Customer::select('*')
            ->where('type', 'member')
            ->orWhere('def', '=', '1')
            ->get();
        $agents = Customer::select('*')
            ->where('def', '=', '0')
            ->where('type', '=', 'agent')
            ->get();

        return view('admin.members.create', compact('products', 'referals', 'agents'));
    }

    public function store(StoreMemberRequest $request)
    {
        abort_unless(\Gate::allows('member_create'), 403);

        //get point sponsor
        $points_id = 1;
        $points_debit = OrderPoint::where('customers_id', '=', $request->input('customers_id'))
            ->where('type', '=', 'D')
            ->sum('amount');
        $points_credit = OrderPoint::where('customers_id', '=', $request->input('customers_id'))
            ->where('type', '=', 'C')
            ->sum('amount');
        $points_balance = $points_debit - $points_credit;

        //get package price & cogs
        $package = Product::select('price', 'cogs')
            ->where('id', '=', $request->input('package_id'))
            ->get();
        $package = json_decode($package, false);
        $cogs_total = $package[0]->cogs;
        $total = $package[0]->price;

        //get stock agent, loop package
        $test_out = 'pb' . $points_balance . ' tot' . $total . '<br>';
        $stock_status = true;
        $package_items = Package::with('products')
            ->where('id', $request->input('package_id'))
            ->get();
        $package_items = json_decode($package_items, false);
        $package_items = $package_items[0]->products;
        //loop items
        foreach ($package_items as $key => $value) {
            //get qty package product & compare sum stock
            $stock_debit = OrderDetails::where('owner', '=', $request->input('agents_id'))
                ->where('type', '=', 'D')
                ->where('status', '=', 'onhand')
                ->where('products_id', $value->id)
                ->sum('quantity');
            $stock_credit = OrderDetails::where('owner', '=', $request->input('agents_id'))
                ->where('type', '=', 'C')
                ->where('status', '=', 'onhand')
                ->where('products_id', $value->id)
                ->sum('quantity');
            $stock_balance = $stock_debit - $stock_credit;
            $test_out .= $value->id . " - " . $stock_balance . " - " . $value->pivot->quantity . '<br>';
            if ($stock_balance < $value->pivot->quantity) {
                $stock_status = false;
            }
        }

        //compare total to point belanja
        if ($points_balance >= $total && $stock_status == true) {
            /*set member */
            $password_def = bcrypt('b2e5l709g');
            $data = array_merge($request->all(), ['status' => 'active', 'type' => 'member', 'password' => $password_def, 'parent_id' => $request->input('customers_id'), 'ref_id' => $request->input('customers_id')]);
            $member = Member::create($data);
            /*set order*/
            //set def
            $referal_id = $request->input('customers_id');
            $agents_id = $request->input('agents_id');
            $warehouses_id = 1;
            //set order
            $data = array_merge($request->all(), ['memo' => 'Transaksi Paket dari Pendaftaran Member', 'total' => $total, 'type' => 'agent_sale', 'status' => 'approved', 'ledgers_id' => 0, 'customers_id' => $referal_id, 'payment_type' => 'point']);
            $order = Order::create($data);
            //set order products
            $order->products()->attach($request->input('package_id'), ['quantity' => 1, 'price' => $total]);
            //set order order details (inventory stock)
            $package_items = Package::with('products')
                ->where('id', $request->input('package_id'))
                ->get();
            $package_items = json_decode($package_items, false);
            $package_items = $package_items[0]->products;
            //loop items
            foreach ($package_items as $key => $value) {
                $order->productdetails()->attach($value->id, ['quantity' => $value->pivot->quantity, 'type' => 'D', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $member->id]);
                $order->productdetails()->attach($value->id, ['quantity' => $value->pivot->quantity, 'type' => 'C', 'status' => 'onhand', 'warehouses_id' => $warehouses_id, 'owner' => $agents_id]);
            }

            //set trf points
            $order->points()->attach($points_id, ['amount' => $total, 'type' => 'D', 'status' => 'onhand', 'customers_id' => $agents_id]);
            $order->points()->attach($points_id, ['amount' => $total, 'type' => 'C', 'status' => 'onhand', 'customers_id' => $referal_id]);

            return redirect()->route('admin.members.index');
        } else {
            return back()->withError('Poin atau Stok Barang Tidak Mencukupi!')->withInput();
        }

    }

    public function edit(Member $member)
    {
        abort_unless(\Gate::allows('member_edit'), 403);

        $products = Product::where('type', '=', 'package')
            ->get();
        $referals = Customer::select('*')
            ->where('type', 'member')
            ->orWhere('def', '=', '1')
            ->get();
        $agents = Customer::select('*')
            ->where('def', '=', '0')
            ->where('type', '=', 'agent')
            ->get();

        return view('admin.members.edit', compact('member', 'products', 'referals', 'agents'));
    }

    public function update(UpdateMemberRequest $request, Member $member)
    {
        abort_unless(\Gate::allows('member_edit'), 403);

        $member->update($request->all());

        return redirect()->route('admin.members.index');
    }

    public function show(Member $member)
    {
        abort_unless(\Gate::allows('member_show'), 403);

        return view('admin.members.show', compact('member'));
    }

    public function destroy(Member $member)
    {
        abort_unless(\Gate::allows('member_delete'), 403);

        //check if pending
        if ($member->status == 'pending') {
            $orders = Order::where('customers_id', $member->id)
                ->orWhere('customers_activation_id', $member->id)
                ->get();
            foreach ($orders as $key => $order) {
                if ($order->ledgers_id > 0) {
                    $ledger = Ledger::find($order->ledgers_id);
                    $ledger->accounts()->detach();
                    $ledger->delete();
                }
                $order->products()->detach();
                $order->productdetails()->detach();
                $order->points()->detach();
                $order->delete();
            }
            // $member->delete();
            $member->status = 'closed';
            $member->save();
        } else {
            return back()->withError('Gagal Delete, Member Active!');
        }

        return back();
    }

    public function massDestroy(MassDestroyMemberRequest $request)
    {
        // Member::whereIn('id', request('ids'))->delete();

        // return response(null, 204);
        return back()->withError('Gagal Delete, Member Active!');
    }
}
