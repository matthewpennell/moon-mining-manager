@extends('layouts.master')

@section('title', 'Taxes')

@section('content')

    <h2>Tax Rates</h2>

    <p>
        Update the tax rate for all ores to:
        <form method="post" action="/taxes/update_master_rate">
            {{ csrf_field() }}
            <input type="text" size="3" name="new_tax_rate">
            <button type="submit">Update</button>
        </form>
    </p>

    <div class="block">

        <table>
            <thead>
                <tr>
                    <th>Moon Ore</th>
                    <th class="numeric">Value</th>
                    <th class="numeric">Tax Rate</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tax_rates as $rate)
                    <tr>
                        <td>{{ $rate->type->typeName }}</td>
                        <td class="numeric">
                            {{ number_format($rate->value, 2) }} ISK
                            <!--
                                <form method="post" action="/taxes/update_value/{{ $rate->type_id }}">
                                    {{ csrf_field() }}
                                    <input type="text" size="10" name="new_value" value="{{ round($rate->value) }}"> ISK
                                    <button type="submit">Save</button>
                                </form>
                            -->
                        </td>
                        <td class="numeric">{{ round($rate->tax_rate) }}%</td>
                        <td>
                            <form method="post" action="/taxes/update_rate/{{ $rate->type_id }}">
                                {{ csrf_field() }}
                                <input type="text" size="3" name="new_tax_rate">
                                <button type="submit">Update</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
       
@endsection
