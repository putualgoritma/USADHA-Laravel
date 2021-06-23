@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">
        {{ trans('global.account.balance') }}
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
                            Saldo Debit (D)
                        </th>
                        <th>
                            Saldo Credit (C)
                        </th>
                        <th>
                            Saldo
                        </th>
                        <th>
                            
                        </th>
                    </tr>
                    @foreach ($accounts as $id => $account)
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
                            {{ number_format($account->amount_debit, 2) }}
                        </td>
                        <td>
                            {{ number_format($account->amount_credit, 2) }}
                        </td>
                        <td>
                        @php
                            $saldo = $account->amount_debit-$account->amount_credit;
                        @endphp
                            {{ number_format($saldo, 2) }}
                        </td>
                        <td>
                        @can('account_show')
                                    <a class="btn btn-xs btn-primary" href="{{ route('admin.accmutation', $account->id) }}">
                                    {{ trans('global.account.mutation') }}
                                    </a>
                                @endcan
                        </td>
                    </tr>
                    @endforeach
                </thead>                
            </table>
        </div>
    </div>
</div>

@endsection