<div class="navigation">

    <ul>
        <li><a href="/">Home</a></li>
        <li><a href="/miners">Miners</a></li>
        <li><a href="/taxes">Manage Tax Rates</a></li>
        <li><a href="/emails">Manage Emails</a></li>
        <li><a href="/access">Manage Access</a></li>
    </ul>

    <div class="user">
        {{ $user->name }}
        <img src="{{ $user->avatar }}" width="40" height="40" alt="{{ $user->name }}" style="border-radius: 20px;">
        <a href="/logout">Logout</a>
    </div>

</div>

        
