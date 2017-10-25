@extends('layouts.master')

@section('title', 'Home')

@section('content')

    @include('blocks.debtors')

    @include('blocks.ninjas')

    @include('blocks.refineries')

@endsection
