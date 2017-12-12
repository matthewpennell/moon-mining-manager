@extends('layouts.master')

@section('title', 'Add New Renter')

@section('content')

    @if ($errors->any())
        <div class="row">
            <div class="col-6">
                <div class="card-heading">Errors!</div>
                <div class="card errors">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <form method="post" action="/renters/new">

        {{ csrf_field() }}

        <div class="row">

            <div class="col-6">

                <div class="card-heading">New renter details</div>

                <div class="card information">
                    <div>
                        <label for="type">Type of rental</label>
                        <select id="type" name="type">
                            <option value="individual">Individual</option>
                            <option value="corporation">Corporation</option>
                        </select>
                    </div>
                    <div>
                        <label for="character">Character</label>
                        <input type="text" id="character" placeholder="Start typing to search by character name...">
                        <input type="hidden" id="character_id" name="character_id">
                        <div class="search-response"></div>
                        <select class="search-options"></select>
                        <div class="character-card">
                            <img src="" alt="">
                            <div class="character-name"></div>
                            <div class="character-corporation"></div>
                        </div>
                    </div>
                    <div>
                        <label for="refinery_id">Refinery to be rented</label>
                        <select id="refinery_id" name="refinery_id">
                            <option value="">Select refinery:</option>
                            @foreach ($refineries as $refinery)
                                <option value="{{ $refinery->observer_id }}">{{ $refinery->name }}</option>
                            @endforeach
                        </select>
                        <span>(if not yet dropped, enter details below)</span>
                    </div>
                    <div>
                        <label for="monthly_rental_fee">Monthly rental fee</label>
                        <input type="text" id="monthly_rental_fee" name="monthly_rental_fee">
                    </div>
                    <div>
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes"></textarea>
                    </div>
                    <div>
                        <label for="start_date">Contract start date (yyyy-mm-dd)</label>
                        <input type="text" id="start_date" name="start_date" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-actions">
                        <button type="submit">Create rental contract</button>
                    </div>
                </div>

            </div>

        </div>

    </form>

    <script>
    
        window.addEventListener('load', function () {
            $('#character').on('keyup', function () {
                if (this.value.length < 4) {
                    $('.character-card').fadeOut();
                    return;
                }
                $.get('/search', {
                    'q': this.value
                }, function (data) {
                    console.log(typeof data);
                    if (typeof data == 'string') {
                        $('.character-card, .search-options').fadeOut();
                        $('.search-response').fadeIn().text(data);
                    } else {
                        if (data.length > 1) {
                            $('.search-response').text('Multiple options found, select one:');
                            $('.search-options').empty().append('<option>Select character:</option>').fadeIn();
                            for (var i = 0; i < data.length; i++) {
                                var option = $('<option data-id="' + data[i].id + '" data-name="' + data[i].name + '" data-portrait="' + data[i].portrait + '" data-corporation="' + data[i].corporation + '">' + data[i].name + ' (' + data[i].corporation + ')</option>');
                                $('.search-options').append(option);
                            }
                        } else {
                            $('.search-response, .search-options').fadeOut();
                            $('#character_id').val(data[0].id);
                            $('#character').val(data[0].name);
                            $('.character-card img').attr('src', data[0].portrait);
                            $('.character-name').text(data[0].name);
                            $('.character-corporation').text(data[0].corporation);
                            $('.character-card').fadeIn();
                        }
                    }
                });
            });
            $('.search-options').on('change', function () {
                var option = $(this).find('option:selected');
                $('#character_id').val(option.data('id'));
                $('#character').val(option.data('name'));
                $('.character-card img').attr('src', option.data('portrait'));
                $('.character-name').text(option.data('name'));
                $('.character-corporation').text(option.data('corporation'));
                $('.character-card').fadeIn();
            });
        });
    
    </script>

@endsection
