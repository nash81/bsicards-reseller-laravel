@extends('backend.setting.index')
@section('setting-title')
{{ __('Card Settings') }}
@endsection
@section('title')
{{ __('Card Settings') }}
@endsection
@section('setting-content')

<div class="col-xl-12 col-lg-12 col-md-12 col-12">
    <div class="site-card">
        <div class="site-card-header">
            <h3 class="title">{{ __('Card Settings') }}</h3>

        </div>
        <div class="site-card-body">
            <form action="{{ route('admin.settings.cardupdate') }}" method="post">
                @csrf

                <div class="site-input-groups row mb-0">
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-label">
                        {{ __('API Keys') }}
                    </div>
                    <div class="col-xl-9 col-lg-9 col-md-9 col-sm-12">
                        <div class="form-row row">
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                <div class="site-input-groups">
                                    <label for=""
                                           class="box-input-label col-label">{{ __('Reseller Public Key') }}</label>
                                    <input
                                        type="text"
                                        class="box-input"
                                        name="bsi_publickey"
                                        value="{{ $general->bsi_publickey }}"
                                        required
                                        />
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                <div class="site-input-groups">
                                    <label for=""
                                           class="box-input-label col-label">{{ __('Reseller Secret Key') }}</label>
                                    <input
                                        type="text"
                                        class="box-input"
                                        name="bsi_secretkey"
                                        value="{{ $general->bsi_secretkey }}"
                                        required
                                        />
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                <div class="site-input-groups">
                                    <label for=""
                                           class="box-input-label col-label">{{ __('BSI Reseller Key') }}</label>
                                    <input
                                        type="text"
                                        class="box-input"
                                        name="bsi_resellerkey"
                                        value="NA"
                                        required readonly
                                        />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="site-input-groups row mb-0">
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-label">
                        {{ __('Card Fees') }}
                    </div>
                    <div class="col-xl-9 col-lg-9 col-md-9 col-sm-12">
                        <div class="form-row row">
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                <div class="site-input-groups">
                                    <label for=""
                                           class="box-input-label col-label">{{ __('BSI Issue Fees $') }}</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        class="box-input"
                                        name="bsiissue_fee"
                                        value="{{ $general->bsiissue_fee }}"
                                        required
                                        />
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                <div class="site-input-groups">
                                    <label for=""
                                           class="box-input-label col-label">{{ __('BSI Load Fees %') }}</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        class="box-input"
                                        name="bsiload_fee"
                                        value="{{ $general->bsiload_fee }}"
                                        required
                                        />
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12">
                                <div class="site-input-groups">
                                    <label for=""
                                           class="box-input-label col-label">{{ __('Digital Mastercard Fee $') }}</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        class="box-input"
                                        name="digifee"
                                        value="{{ $general->digifee }}"
                                        required
                                        />
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="offset-sm-3 col-sm-9 col-12">
                        <button type="submit" class="site-btn-sm primary-btn w-100">
                            {{ __(' Save Changes') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>





@endsection
