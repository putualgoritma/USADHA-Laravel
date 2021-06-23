@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Approved {{ trans('global.withdraw.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.withdraw.approvedprocess") }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')  

            <div class="form-group {{ $errors->has('acc_pay') ? 'has-error' : '' }}">
                <label for="acc_pay">{{ trans('global.withdraw.fields.acc_pay') }}*</label>
                <select name="acc_pay" class="form-control">
                    <option value="">-- choose account --</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}"{{ old('code') == $account->id ? ' selected' : '' }}>
                        {{ $account->code }}-{{ $account->name }} {{ $account->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('acc_pay'))
                    <em class="invalid-feedback">
                        {{ $errors->first('acc_pay') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.withdraw.fields.acc_pay_helper') }}
                </p>
            </div> 
                     
            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
            <div class="checkbox">
            <label>Approved {{ trans('global.withdraw.fields.status') }}*</label>
            <input type="checkbox" data-toggle="toggle" name="status" id="status">    
            </div>
                @if($errors->has('status'))
                    <em class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.withdraw.fields.status_helper') }}
                </p>
                <input type="hidden" id="id" name="id" value="{{ $withdraw->id }}">
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="Approve">
            </div>
        </form>


    </div>
</div>
@endsection
