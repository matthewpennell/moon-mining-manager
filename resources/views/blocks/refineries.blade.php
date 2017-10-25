<div class="block">

    <h2>Income Per Refinery</h2>

    <table>
        <thead>
            <tr>
                <th>Refinery</th>
                <th>System</th>
                <th class="numeric">Income</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="2">Total Income:</td>
                <td class="numeric">{{ number_format($total_income) }} ISK</td>
            </tr>
        </tfoot>
        <tbody>
            @foreach ($refineries as $refinery)
                <tr>
                    <td><a href="/refinery/{{ $refinery->observer_id }}">{{ $refinery->name }}</a></td>
                    <td>{{ $refinery->system->solarSystemName }}</td>
                    <td class="numeric">{{ number_format($refinery->income) }} ISK</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
