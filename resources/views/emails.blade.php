@extends('layouts.master')

@section('title', 'Email Templates')

@section('content')
    
    <div class="row">

        <div class="col-12">
            <div class="card information">
                <p>Within the body of the emails, you can use the following placeholder text. It will be replaced by the appropriate values when sent.</p>
                <ul>
                    <li><strong>{date}</strong> - the current date</li>
                    <li><strong>{name}</strong> - the name of the recipient</li>
                    <li><strong>{refinery}</strong> - the name of a rented refinery</li>
                    <li><strong>{amount_owed}</strong> - the total amount currently owed by the recipient</li>
                    <li><strong>{monthly_rental_fee}</strong> - the monthly rental fee for a rented refinery</li>
                    <li><strong>{outstanding_balance}</strong> - the outstanding balance owed for a rented refinery</li>
                    <li><strong>{activity_log}</strong> - a list of mining activity by the recipient</li>
                </ul>
            </div>
        </div>

    </div>

    <form action="/emails/update" method="post">
        {{ csrf_field() }}
        <div class="row">
            @foreach ($templates as $template)
                <div class="col-6">
                    <div class="card-heading">{{ str_replace('_', ' ', $template->name) }}</div>
                    <div class="card information">
                        <p>Email subject:</p>
                        <input type="text" size="50" name="{{ $template->name }}__subject" value="{{ $template->subject }}">
                        <p>Email body:</p>
                        <textarea name="{{ $template->name }}__body">{{ $template->body }}</textarea>
                        <div class="form-actions">
                            <button type="submit">Save Changes</button>
                        </div>
                    </div>
                </div>
                @if ($loop->index % 2 == 1)
                    </div>
                    <div class="row">
                @endif
            @endforeach
        </div>
    </form>
        
@endsection
