@extends('backend.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Card Balance</div>
                <div class="card-body">
                    <h5 class="card-title">{{ number_format($card_balance, 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Visafund Balance</div>
                <div class="card-body">
                    <h5 class="card-title">{{ number_format($visafund_balance, 2) }}</h5>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

