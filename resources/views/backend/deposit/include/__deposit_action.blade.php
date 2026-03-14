@php
    $manualFields = (array) json_decode($data->manual_field_data, true);
    $imageExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
@endphp

<h3 class="title mb-4">
    {{ __('Deposit Approval Action') }}
</h3>

<ul class="list-group mb-4 deposit-meta-list">
    <li class="list-group-item">
        <span>{{ __('Total amount') }}</span>
        <strong>{{ $data->final_amount. ' '.$currency }}</strong>
    </li>
    @if($data->pay_currency != $currency)
        <li class="list-group-item">
            <span>{{ __('Conversion amount') }}</span>
            <strong>{{ $data->pay_amount. ' '.$data->pay_currency }}</strong>
        </li>
    @endif

</ul>

<ul class="list-group mb-4 manual-data-list">

    @foreach($manualFields as $key => $value)
        @php
            $valueString = is_scalar($value) ? (string) $value : '';
            $isFile = $valueString !== '' && preg_match('/\.(jpg|jpeg|png|gif|webp|bmp|svg|pdf)$/i', $valueString);
            $ext = strtolower(pathinfo($valueString, PATHINFO_EXTENSION));
            $isImage = in_array($ext, $imageExt, true);
        @endphp
        <li class="list-group-item">
            <span class="manual-key">{{ $key }}</span>

            @if($valueString !== '')
                @if($isFile && $isImage)
                    <a href="{{ asset($valueString) }}" target="_blank" class="proof-preview-wrap" title="{{ __('Open image') }}">
                        <img src="{{ asset($valueString) }}" alt="{{ $key }}" class="proof-preview-img"/>
                    </a>
                @elseif($isFile)
                    <a href="{{ asset($valueString) }}" target="_blank" class="proof-file-link">
                        <i data-lucide="paperclip"></i>{{ __('View attachment') }}
                    </a>
                @else
                    <strong>{{ $valueString }}</strong>
                @endif
            @endif
        </li>
    @endforeach
</ul>

<form action="{{ route('admin.deposit.action.now') }}" method="post">
    @csrf
    <input type="hidden" name="id" value="{{ $id }}">

    <div class="site-input-groups">
        <label for="" class="box-input-label">{{ __('Details Message(Optional)') }}</label>
        <textarea name="message" class="form-textarea mb-0" placeholder="{{ __('Details Message') }}"></textarea>
    </div>

    <div class="action-btns">
        <button type="submit" name="approve" value="yes" class="site-btn-sm primary-btn me-2">
            <i data-lucide="check"></i>
            {{ __('Approve') }}
        </button>
        <button type="submit" name="reject" value="yes" class="site-btn-sm red-btn">
            <i data-lucide="x"></i>
            {{ __('Reject') }}
        </button>
    </div>

</form>
<script>
    'use strict';
    lucide.createIcons();
</script>



