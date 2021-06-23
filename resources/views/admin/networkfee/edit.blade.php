@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        {{ trans('global.create') }} {{ trans('global.networkfee.title_singular') }}
    </div>

    <div class="card-body">
        <form action="{{ route("admin.fees.update", [$networkfee->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group {{ $errors->has('code') ? 'has-error' : '' }}">
                <label for="code">{{ trans('global.networkfee.fields.code') }}*</label>
                <input type="text" id="code" name="code" class="form-control" value="{{ old('code', isset($networkfee) ? $networkfee->code : '') }}">
                @if($errors->has('code'))
                    <em class="invalid-feedback">
                        {{ $errors->first('code') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.code_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                <label for="name">{{ trans('global.networkfee.fields.name') }}*</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', isset($networkfee) ? $networkfee->name : '') }}">
                @if($errors->has('name'))
                    <em class="invalid-feedback">
                        {{ $errors->first('name') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.name_helper') }}
                </p>
            </div>
            
            <div class="form-group {{ $errors->has('amount') ? 'has-error' : '' }}">
                <label for="amount">{{ trans('global.networkfee.fields.amount') }}</label>
                <input type="text" id="amount" name="amount" class="form-control" value="{{ old('amount', isset($networkfee) ? $networkfee->amount : '0') }}" step="1.00">
                @if($errors->has('amount'))
                    <em class="invalid-feedback">
                        {{ $errors->first('amount') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.amount_helper') }}
                </p>
            </div>

            <div class="form-group {{ $errors->has('type') ? 'has-error' : '' }}">
                <label for="type">{{ trans('global.networkfee.fields.type') }}*</label>
                <select name="type" class="form-control">
                    <option value="none"{{ $networkfee->type == 'none' ? 'selected="selected"' : '' }}>-- choose type --</option>
                    <option value="activation"{{ $networkfee->type == 'activation' ? 'selected="selected"' : '' }}>Aktivasi</option>  
                    <option value="ro"{{ $networkfee->type == 'ro' ? 'selected="selected"' : '' }}>RO</option>
                    <option value="conventional"{{ $networkfee->type == 'conventional' ? 'selected="selected"' : '' }}>Konvensional</option>
                    <option value="pairing"{{ $networkfee->type == 'pairing' ? 'selected="selected"' : '' }}>Pairing</option>
                    <option value="matching"{{ $networkfee->type == 'matching' ? 'selected="selected"' : '' }}>Matching</option>                  
                </select>
                @if($errors->has('type'))
                    <em class="invalid-feedback">
                        {{ $errors->first('type') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.type_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('deep_level') ? 'has-error' : '' }}">
                <label for="deep_level">{{ trans('global.networkfee.fields.deep_level') }}</label>
                <input type="number" id="deep_level" name="deep_level" class="form-control" value="{{ old('deep_level', isset($networkfee) ? $networkfee->deep_level : '0') }}" step="1">
                @if($errors->has('deep_level'))
                    <em class="invalid-feedback">
                        {{ $errors->first('deep_level') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.deep_level_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('fee_day_max') ? 'has-error' : '' }}">
                <label for="fee_day_max">{{ trans('global.networkfee.fields.fee_day_max') }}</label>
                <input type="number" id="fee_day_max" name="fee_day_max" class="form-control" value="{{ old('fee_day_max', isset($networkfee) ? $networkfee->fee_day_max : '0') }}" step="1">
                @if($errors->has('fee_day_max'))
                    <em class="invalid-feedback">
                        {{ $errors->first('fee_day_max') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.fee_day_max_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('activation_type_id') ? 'has-error' : '' }}">
                <label for="activation_type_id">{{ trans('global.networkfee.fields.activation_type_id') }}*</label>
                <select name="activation_type_id" class="form-control">
                    <option value="0">-- choose activation --</option>
                    @foreach ($activations as $activation)
                        <option value="{{ $activation->id }}"{{ $networkfee->activation_type_id == $activation->id ? ' selected' : '' }}>
                        {{ $activation->code }}-{{ $activation->name }} {{ $activation->last_name }}
                        </option>
                    @endforeach
                </select>
                @if($errors->has('activation_type_id'))
                    <em class="invalid-feedback">
                        {{ $errors->first('activation_type_id') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.activation_type_id_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('sbv') ? 'has-error' : '' }}">
                <label for="sbv">{{ trans('global.networkfee.fields.sbv') }}</label>
                <input type="number" id="sbv" name="sbv" class="form-control" value="{{ old('sbv', isset($networkfee) ? $networkfee->sbv : '0') }}" step="1.00">
                @if($errors->has('sbv'))
                    <em class="invalid-feedback">
                        {{ $errors->first('sbv') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.sbv_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('saving') ? 'has-error' : '' }}">
                <label for="saving">{{ trans('global.networkfee.fields.saving') }}*</label>
                <select name="saving" class="form-control">
                    <option value="no"{{ $networkfee->saving == 'no' ? 'selected="selected"' : '' }}>No</option>
                    <option value="yes"{{ $networkfee->saving == 'yes' ? 'selected="selected"' : '' }}>Yes</option>
                    
                </select>
                @if($errors->has('saving'))
                    <em class="invalid-feedback">
                        {{ $errors->first('saving') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.saving_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('rsbv_g1') ? 'has-error' : '' }}">
                <label for="rsbv_g1">{{ trans('global.networkfee.fields.rsbv_g1') }}</label>
                <input type="number" id="rsbv_g1" name="rsbv_g1" class="form-control" value="{{ old('rsbv_g1', isset($networkfee) ? $networkfee->rsbv_g1 : '0') }}" step="1.00">
                @if($errors->has('rsbv_g1'))
                    <em class="invalid-feedback">
                        {{ $errors->first('rsbv_g1') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.rsbv_g1_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('rsbv_g2') ? 'has-error' : '' }}">
                <label for="rsbv_g2">{{ trans('global.networkfee.fields.rsbv_g2') }}</label>
                <input type="number" id="rsbv_g2" name="rsbv_g2" class="form-control" value="{{ old('rsbv_g2', isset($networkfee) ? $networkfee->rsbv_g2 : '0') }}" step="1.00">
                @if($errors->has('rsbv_g2'))
                    <em class="invalid-feedback">
                        {{ $errors->first('rsbv_g2') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.rsbv_g2_helper') }}
                </p>
            </div>
            <div class="form-group {{ $errors->has('cba2') ? 'has-error' : '' }}">
                <label for="cba2">{{ trans('global.networkfee.fields.cba2') }}*</label>
                <select name="cba2" class="form-control">                    
                    <option value="yes"{{ $networkfee->cba2 == 'yes' ? 'selected="selected"' : '' }}>Yes</option>
                    <option value="no"{{ $networkfee->cba2 == 'no' ? 'selected="selected"' : '' }}>No</option>
                    
                </select>
                @if($errors->has('cba2'))
                    <em class="invalid-feedback">
                        {{ $errors->first('cba2') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.networkfee.fields.cba2_helper') }}
                </p>
            </div>

            <div>
                <input class="btn btn-danger" type="submit" value="{{ trans('global.save') }}">
            </div>
        </form>
    </div>
</div>

@endsection