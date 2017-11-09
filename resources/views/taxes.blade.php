@extends('layouts.master')

@section('title', 'Taxes')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-heading">Update all tax rates</div>
            <div class="card information inline-form">
                <form method="post" action="/taxes/update_master_rate">
                    {{ csrf_field() }}
                    <p>Update the tax rate for all ores to:
                        <input type="text" size="3" name="new_tax_rate">
                        <button type="submit">Update</button>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-6">

            <div class="card-heading">Tax rate details (<a href="/taxes/history">See history</a>)</div>

            <table>
                <thead>
                    <tr>
                        <th>Moon Ore</th>
                        <th class="numeric">Value</th>
                        <th class="numeric">Tax Rate</th>
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
                            <td class="numeric inline-form">
                                <form method="post" action="/taxes/update_rate/{{ $rate->type_id }}">
                                    {{ csrf_field() }}
                                    {{ round($rate->tax_rate) }}%
                                    <input type="text" size="3" name="new_tax_rate">
                                    <button type="submit">Update</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    </div>
       
@endsection
