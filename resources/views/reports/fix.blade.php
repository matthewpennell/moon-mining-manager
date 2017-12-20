@extends('layouts.master')

@section('title', 'Reports')

@section('content')

    <div class="row">

        <div class="col-6">

            <div class="card-heading">Unsent invoices</div>

            <div class="card information">
                <p>The following miners were not sent an invoice last Monday ({{ $last_monday }}):</p>
                <ul>
                    @foreach ($miners as $miner)
                        <li>{{ $miner->name }}</li>
                    @endforeach
                </ul>
            </div>
        
        </div>

        <div class="col-6">
            
            <div class="card-heading">Resend invoices</div>

            <div class="card information">
                <p>Use this link to regenerate any invoices which failed to send last Monday. Check logs for more details:</p>
                <p><a href="/reports/regenerate">Regenerate Invoices</a></p>
            </div>

        </div>

    </div>

@endsection
