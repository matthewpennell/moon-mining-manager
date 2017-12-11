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
                    if (typeof data == 'object') {
                        $('.search-response').text('');
                        $('#character_id').val(data.id);
                        $('#character').val(data.name);
                        $('.character-card img').attr('src', data.portrait);
                        $('.character-name').text(data.name);
                        $('.character-corporation').text(data.corporation);
                        $('.character-card').fadeIn();
                    } else {
                        $('.character-card').fadeOut();
                        $('.search-response').text(data);
                    }
                });
            });
        });
    
    </script>

@endsection
