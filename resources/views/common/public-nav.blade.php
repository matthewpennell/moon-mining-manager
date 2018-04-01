<div class="public-menu">
    <ul>
        <li>
            <a href="/logout">Log out</a>
        </li>
        <li>
            <a href="/moons"
                @if ($page == 'moons')
                    class="current"
                @endif
            >Moons available to rent</a>
        </li>
        <li>
            <a href="/timers"
                @if ($page == 'timers')
                    class="current"
                @endif
            >Upcoming moon timers</a>
        </li>
    </ul>
</div>
