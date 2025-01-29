@extends('layouts.master')

@section('content')
<div class="container">
    <h2>Add Opening Balances</h2>
    <form action="{{ route('opening_balance.store') }}" method="POST">
        @csrf
        <div class="row">
            @foreach($accounts as $account)
            <div class="col-md-6">
                <div class="form-group">
                    <label for="balance_{{ $account->id }}">{{ $account->name }} ({{ $account->type }})</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="balances[{{ $account->id }}][amount]" id="balance_{{ $account->id }}" class="form-control" placeholder="Enter opening balance">
                        <select name="balances[{{ $account->id }}][type]" class="form-control">
                            <option value="Dr">Debit</option>
                            <option value="Cr">Credit</option>
                        </select>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <button type="submit" class="btn btn-primary">Save Opening Balances</button>
    </form>
</div>
@endsection
