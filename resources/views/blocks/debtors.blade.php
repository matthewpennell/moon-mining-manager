<div class="block">

    <h2>Outstanding Debts</h2>

    <p><a href="/payment/new">Log a payment</a> received via alternative means</p>

    <table>
        <thead>
            <tr>
                <th>Miner</th>
                <th class="numeric">Amount Owed</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td>Total Outstanding:</td>
                <td class="numeric">{{ number_format($total_amount_owed) }} ISK</td>
            </tr>
        </tfoot>
        <tbody>
            @foreach ($miners as $miner)
                <tr>
                    <td><a href="/miners/{{ $miner->eve_id }}">{{ $miner->name }}</a> ({{ $miner->corporation->name }})</td>
                    <td class="numeric">{{ number_format($miner->amount_owed) }} ISK</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
