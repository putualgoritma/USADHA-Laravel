<?php

namespace App\Http\Controllers\Admin;

use App\Chart;
use App\Http\Controllers\Controller;
use App\Order;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
    public function index(Request $request)
    {
     
        if($request->month == 'all' || $request->month ==null || $request->month==''){
            $month = null;
        }else{
            $month = $request->month;
        }

        if($request->year != ''){
            $year = $request->year;
        }else{
            $year = date('Y');
        }

        $data = [];
        $dataCount=[];
        $label = [];

        if($month!=null){
            $statistic_sale_nominal = Order::selectRaw('DATE(created_at) as created_at_tanggal, sum(total) as total')
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
                ->where('type', 'sale')
                ->where('status', 'approved')
                ->groupBy('created_at_tanggal')
                ->get();

            $statistic_sale_count = Order::selectRaw('DATE(created_at) as created_at_tanggal, count(id) as total')
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
                ->where('type', 'sale')
                ->where('status', 'approved')
                ->groupBy('created_at_tanggal')
                ->get();



            for ($i=0; $i<=count($statistic_sale_nominal); $i++) {
                $colours[] = '#' . substr(str_shuffle('ABCDEF0123456789'), 0, 6);
            }

            for ($i=1; $i <=31 ; $i++) { 
                array_push($label, $i);
                array_push($data, 0);
                array_push($dataCount, 0);
            }

            foreach ($label as $key => $value ) {
                foreach ($statistic_sale_nominal as $key => $item) {
                    $index = explode('-',$item->created_at_tanggal)[2];
                    $data[$index-1] = $item->total;
                }

                foreach ($statistic_sale_count as $key => $item) {
                    $index = explode('-',$item->created_at_tanggal)[2];
                    $dataCount[$index-1] = $item->total;
                }
            }

        }else{
            $statistic_sale_nominal = Order::selectRaw('created_at, sum(total) as total, YEAR(created_at) year, MONTH(created_at) month')
                ->whereYear('created_at', '=', $year)
                ->where('type', 'sale')
                ->where('status', 'approved')
                ->groupBy('year','month')
                ->get();

            $statistic_sale_count = Order::selectRaw('created_at, count(id) as total, YEAR(created_at) year, MONTH(created_at) month')
                ->whereYear('created_at', '=', $year)
                ->where('type', 'sale')
                ->where('status', 'approved')
                ->groupBy('year','month')
                ->get();


            for ($i=0; $i<=count($statistic_sale_nominal); $i++) {
                $colours[] = '#' . substr(str_shuffle('ABCDEF0123456789'), 0, 6);
            }

            for($m=1; $m<=12; ++$m){
                array_push($label, date('F', mktime(0, 0, 0, $m, 1)));
                array_push($data, 0);
                array_push($dataCount, 0);
            }

            foreach ($label as $key => $value ) {
                foreach ($statistic_sale_nominal as $key => $item) {
                    $isiBulan = date_format($item->created_at, 'F');
                    $index =  date_format($item->created_at, 'm');
                    if($value == $isiBulan){
                        $data[$index -1] = $item->total;
                       break;
                    }
                }

                foreach ($statistic_sale_count as $key => $item) {
                    $isiBulan = date_format($item->created_at, 'F');
                    $index =  date_format($item->created_at, 'm');
                    if($value == $isiBulan){
                        $dataCount[$index -1] = $item->total;
                        break;
                    }
                }
            }
        }

        $chart = new Chart();
        $chart->labels = $label;
        $chart->dataset = $data;
        $chart->colours = $colours;

        $chartCount = new Chart();
        $chartCount->labels = $label;
        $chartCount->dataset = $dataCount;
        $chartCount->colours = $colours;
        
        return view('admin.statistik.index', compact('chart', 'chartCount'));
    }

    public function product(Request $request)
    {
      
        if($request->month == 'all' || $request->month ==null || $request->month==''){
            $month = null;
        }else{
            $month = $request->month;
        }

        if($request->year != ''){
            $year = $request->year;
        }else{
            $year = date('Y');
        }

        $data=[];
        $label=[];
        for ($i=0; $i<10; $i++) {
            $colours[] = '#' . substr(str_shuffle('ABCDEF0123456789'), 0, 6);
        }
        if($month!=''){
            $statistic_sale_product = Order::selectRaw('DATE(orders.created_at) as created_at,products.name as product_name, sum(product_order_details.quantity) as total')
            ->join('product_order_details', 'orders.id', '=', 'product_order_details.orders_id')
            ->join('products', 'products.id', '=', 'product_order_details.products_id')
            ->whereYear('orders.created_at', '=', $year)
            ->whereMonth('orders.created_at', '=', $month)
            ->where('orders.type', 'sale')
            ->where('orders.status', 'approved')
            ->groupBy('product_order_details.products_id')
            ->orderBy('total', 'desc')
            // ->take(10)
            ->get();

            foreach ($statistic_sale_product as $key => $item) {
                array_push($label, $item->product_name);
                array_push($data, $item->total);
            }
        }else{
            $statistic_sale_product = Order::selectRaw('Month(orders.created_at) as month,Year(orders.created_at) as year,products.name as product_name, sum(product_order_details.quantity) as total')
                ->join('product_order_details', 'orders.id', '=', 'product_order_details.orders_id')
                ->join('products', 'products.id', '=', 'product_order_details.products_id')
                ->whereYear('orders.created_at', '=', $year)
                ->where('orders.type', 'sale')
                ->where('orders.status', 'approved')
                ->groupBy('product_order_details.products_id')
                // ->take(10)
                ->get();
            foreach ($statistic_sale_product as $key => $item) {
                array_push($label, $item->product_name);
                array_push($data, $item->total);
            }
        }

        $chart = new Chart();
        $chart->labels = $label;
        $chart->dataset = $data;
        $chart->colours = $colours;

        // dd($statistic_sale_product);
        return view('admin.statistik.statistikproduct', compact('chart'));
    }
}
