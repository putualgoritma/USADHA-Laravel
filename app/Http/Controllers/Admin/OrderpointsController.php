<?php

namespace App\Http\Controllers\Admin;

use App\OrderPoint;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Customer;

class OrderpointsController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(\Gate::allows('orderpoint_access'), 403);

        if ($request->ajax()) {
            $query = OrderPoint::with('orders')
                ->with('customers')
                ->where('status', 'onhand')
                ->orderBy('id','DESC')
                ->FilterInput()
                ->get();
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');

            $table->editColumn('register', function ($row) {
                return $row->orders->register ? $row->orders->register : "";
            });

            $table->editColumn('memo', function ($row) {
                return $row->memo ? $row->memo : "";
            });

            $table->editColumn('name', function ($row) {
                return $row->customers->name ? $row->customers->code." - ".$row->customers->name : "";
            });            

            $table->editColumn('debit', function ($row) {
                if($row->type === 'D'){
                return $row->amount ? number_format($row->amount, 2) : "";
                }
            });

            $table->editColumn('credit', function ($row) {
                if($row->type === 'C'){
                    return $row->amount ? number_format($row->amount, 2) : "";
                    }
            });

            $table->editColumn('balance', function ($row) {
                return ;
            });

            $table->rawColumns(['placeholder']);

            $table->addIndexColumn();
            return $table->make(true);
        }
        //def view
        $orderpoints = OrderPoint::with('orders')
            ->with('customers')
            ->where('status', 'onhand')
            ->orderBy('id','DESC')
            ->get();

        $customers = Customer::select('*')
            ->where(function ($query) {
                $query->where('type', 'member')
                    ->orWhere('type', 'agent')
                    ->orWhere('def', '1');
            })
            ->get();

        return view('admin.orderpoints.index', compact('orderpoints', 'customers'));        
    }    
}
