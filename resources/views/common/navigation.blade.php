<div class="user">
    <img src="{{ $user->avatar }}" class="avatar" alt="{{ $user->name }}">
    <div class="user-name">{{ $user->name }}</div>
    <a href="/logout">Logout</a>
</div>

<ul>
    <li>
        <a href="/">
            <i class="icon-home"></i>
            Home
        </a>
    </li>
    <li>
        <a href="/miners">
            <i class="icon-users"></i>
            Miners
        </a>
    </li>
    <li>
        <a href="/reports">
            <i class="icon-stats-dots"></i>
            Reports
        </a>
    </li>
    <li>
        <a href="/taxes">
            <i class="icon-coin-dollar"></i>
            Taxes
        </a>
    </li>
    <li>
        <a href="/emails">
            <i class="icon-envelop"></i>
            Manage Emails
        </a>
    </li>
    <li>
        <a href="/access">
            <i class="icon-cog"></i>
            Settings
        </a>
    </li>
</ul>
        
