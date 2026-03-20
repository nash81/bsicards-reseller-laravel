@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Reseller Digital Visa Wallet Cards</h2>
    <div id="visaWalletCardsList"></div>
    <button class="btn btn-primary" id="createCardBtn">Create New Card</button>
    <!-- Modal for Create Card -->
    @include('reseller.partials._visa_wallet_card_modal')
</div>
@endsection
@section('scripts')
<script src="/js/reseller/visaWalletCards.js"></script>
@endsection

{{-- This file is deprecated and should be removed. Use the new Digital Visa Wallet Card views instead. --}}
