@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.account.balance_trial') }}
    </div>

    <div class="card-body">
        <div class="row">
            </div>
        <div class="table-responsive">
        <table class="table table-bordered table-striped">
                <thead>
                <tr>
                        <th width="10">

                        </th>
                        <th>
                            {{ trans('global.account.fields.code') }}
                        </th>
                        <th>
                            {{ trans('global.account.fields.name') }}
                        </th>
                        <th>
                            Saldo
                        </th>
                    </tr>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            Harta/Assets
                        </th>
                        <th>
                            
                        </th>
                    </tr>
                    @php
                    $total = 0;
                    @endphp
                    @foreach ($accounts_assets as $id => $account)
                    @php
                    $amount = $account->amount_debit - $account->amount_credit;
                    $total = $total + $amount;
                    @endphp
                    <tr>
                        <td>

                        </td>
                        <td>
                            {{ $account->code }}
                        </td>
                        <td>
                            {{ $account->name }}
                        </td>
                        <td>
                            {{ number_format($amount, 2) }}
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            Total Harta
                        </th>
                        <th>
                        {{ number_format($total, 2) }}
                        </th>
                    </tr>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            
                        </th>
                        <th>
                            
                        </th>
                    </tr>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            Utang/Kewajiban
                        </th>
                        <th>
                            
                        </th>
                    </tr>
                    @php
                    $total = 0;
                    @endphp
                    @foreach ($accounts_liabilities as $id => $account)
                    @php
                    $amount = $account->amount_credit - $account->amount_debit;
                    $total = $total + $amount;
                    @endphp
                    <tr>
                        <td>

                        </td>
                        <td>
                            {{ $account->code }}
                        </td>
                        <td>
                            {{ $account->name }}
                        </td>
                        <td>
                            {{ number_format($amount, 2) }}
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            Total Utang
                        </th>
                        <th>
                        {{ number_format($total, 2) }}
                        </th>
                    </tr>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            
                        </th>
                        <th>
                            
                        </th>
                    </tr>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            Modal/Equity
                        </th>
                        <th>
                            
                        </th>
                    </tr>
                    @php
                    $total = 0;
                    @endphp
                    @foreach ($accounts_equity as $id => $account)
                    @php
                    $amount = $account->amount_credit - $account->amount_debit;
                    $total = $total + $amount;
                    @endphp
                    <tr>
                        <td>

                        </td>
                        <td>
                            {{ $account->code }}
                        </td>
                        <td>
                            {{ $account->name }}
                        </td>
                        <td>
                            {{ number_format($amount, 2) }}
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            Total Modal
                        </th>
                        <th>
                        {{ number_format($total, 2) }}
                        </th>
                    </tr>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            
                        </th>
                        <th>
                            
                        </th>
                    </tr>
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            Laba/Profit
                        </th>
                        <th>
                            
                        </th>
                    </tr>
                    @php
                    $total_revenue = 0;
                    $total_expense = 0;
                    @endphp
                    @foreach ($accounts_revenues as $id => $account)
                    @php
                    $amount = $account->amount_credit - $account->amount_debit;
                    $total_revenue = $total_revenue + $amount;
                    @endphp     
                    @endforeach 
                    @foreach ($accounts_expenses as $id => $account)
                    @php
                    $amount = $account->amount_debit - $account->amount_credit;
                    $total_expense = $total_expense + $amount;
                    @endphp     
                    @endforeach 
                    @php
                    $total = $total_revenue-$total_expense;
                    @endphp             
                    <tr>
                        <th width="10">

                        </th>
                        <th>
                            
                        </th>
                        <th>
                            Total Laba
                        </th>
                        <th>
                        {{ number_format($total, 2) }}
                        </th>
                    </tr>
                </thead>                
            </table>
        </div>
    </div>
</div>

@endsection