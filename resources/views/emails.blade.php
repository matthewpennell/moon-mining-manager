@extends('layouts.master')

@section('title', 'Email Templates')

@section('content')
        
    <h2>Email Templates</h2>

    <p>Within the body of the emails, you can use the following placeholder text. It will be replaced by the appropriate values when sent.</p>

    <ul>
        <li><strong>{date}</strong> - the current date</li>
        <li><strong>{name}</strong> - the name of the recipient</li>
        <li><strong>{amount_owed}</strong> - the total amount currently owed by the recipient</li>
    </ul>

    <form action="/emails/update" method="post">
        {{ csrf_field() }}
        <table>
            <thead>
                <tr>
                    <th>Template</th>
                    <th>Subject</th>
                    <th>Body</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($templates as $template)
                    <tr>
                        <td>{{ $template->name }}</td>
                        <td><input type="text" size="50" name="{{ $template->name }}__subject" value="{{ $template->subject }}"></td>
                        <td><textarea rows="20" cols="100" name="{{ $template->name }}__body">{{ $template->body }}</textarea></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <button type="submit">Save Changes</button>
    </form>
        
@endsection
