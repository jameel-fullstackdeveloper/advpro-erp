@extends('layouts.master')

@section('content')
<div class="container">
    <h2>Ledger for {{ $account->name }}</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ledger as $entry)
            <tr>
                <td>{{ $entry['date'] }}</td>
                <td>{{ $entry['description'] }}</td>
                <td>{{ $entry['debit'] }}</td>
                <td>{{ $entry['credit'] }}</td>
                <td>{{ $entry['balance'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
