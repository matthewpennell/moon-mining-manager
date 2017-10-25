<div class="block">

    <h2>Ninja Miners</h2>

    <table>
        <thead>
            <tr>
                <th>Miner</th>
                <th>Corporation/Alliance</th>
                <th class="numeric">Amount Owed</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ninjas as $ninja)
                <tr>
                    <td>{{ $ninja->name }}</a></td>
                    <td>{{ $ninja->corporation->name }} ({{ $ninja->alliance->name }})</td>
                    <td class="numeric">{{ number_format($ninja->amount_owed) }} ISK</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
