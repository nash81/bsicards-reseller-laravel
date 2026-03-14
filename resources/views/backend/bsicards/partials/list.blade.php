<div class="main-content">
    <div class="page-title">
        <div class="container-fluid">
            <div class="row">
                <div class="col">
                    <div class="title-content">
                        <h2 class="title">{{ $tableTitle }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="site-table table-responsive">
                    <form action="{{ request()->url() }}" method="get" class="mb-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6 col-lg-5">
                                <label for="search" class="form-label">{{ __('Search') }}</label>
                                <input type="text" id="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ __('Search in results...') }}">
                            </div>
                            <div class="col-md-3 col-lg-2">
                                <label for="perPage" class="form-label">{{ __('Per Page') }}</label>
                                <select name="perPage" id="perPage" class="form-select">
                                    @foreach([15, 30, 45, 60] as $perPage)
                                        <option value="{{ $perPage }}" @selected((int) request('perPage', 15) === $perPage)>{{ $perPage }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-lg-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i data-lucide="search"></i>
                                    {{ __('Filter') }}
                                </button>
                                @if(request()->query())
                                    <a href="{{ request()->url() }}" class="btn btn-outline-secondary w-100">{{ __('Reset') }}</a>
                                @endif
                            </div>
                        </div>
                    </form>

                    @if($apiError)
                        <div class="alert alert-danger">{{ $apiError }}</div>
                    @endif

                    <table class="table align-middle">
                        <thead>
                            <tr>
                                @foreach($columns as $column)
                                    @include('backend.filter.th', ['label' => $column['label'], 'field' => $column['field']])
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    @foreach($columns as $column)
                                        @php
                                            $field = $column['field'];
                                            $value = data_get($row, $field);
                                            if (is_bool($value)) {
                                                $display = $value ? 'Yes' : 'No';
                                            } elseif (is_array($value)) {
                                                $display = collect($value)->flatten()->filter(fn ($item) => !is_array($item))->implode(', ');
                                            } elseif (is_object($value)) {
                                                $display = collect((array) $value)->flatten()->implode(', ');
                                            } else {
                                                $display = (string) $value;
                                            }

                                            $lowerField = \Illuminate\Support\Str::lower($field);
                                            $isStatusField = \Illuminate\Support\Str::contains($lowerField, 'status') || in_array($lowerField, ['is_active', 'active'], true);
                                            $isTypeField = $lowerField === 'type';
                                            $isDetailsField = $lowerField === 'details';

                                            $formattedDisplay = $display;
                                            if (\Illuminate\Support\Str::endsWith($lowerField, '_at') && $display !== '') {
                                                $timestamp = strtotime($display);
                                                if ($timestamp !== false) {
                                                    $formattedDisplay = date('Y-m-d H:i', $timestamp);
                                                }
                                            }

                                            $statusClass = 'bg-secondary';
                                            if ($isStatusField) {
                                                $normalizedStatus = \Illuminate\Support\Str::lower(trim($display));
                                                if (in_array($normalizedStatus, ['success', 'active', 'yes', 'approved', 'completed'], true)) {
                                                    $statusClass = 'bg-success';
                                                } elseif (in_array($normalizedStatus, ['pending', 'processing'], true)) {
                                                    $statusClass = 'bg-warning text-dark';
                                                } elseif (in_array($normalizedStatus, ['failed', 'inactive', 'disabled', 'rejected', 'blocked', 'cancelled', 'canceled', 'no'], true)) {
                                                    $statusClass = 'bg-danger';
                                                }
                                            }

                                            $typeClass = 'bg-secondary';
                                            if ($isTypeField) {
                                                $normalizedType = trim($display);
                                                if ($normalizedType === '+') {
                                                    $typeClass = 'bg-success';
                                                } elseif ($normalizedType === '-') {
                                                    $typeClass = 'bg-danger';
                                                }
                                            }

                                            if ($lowerField === 'amount' && $formattedDisplay !== '') {
                                                $rowType = trim((string) data_get($row, 'type', ''));
                                                if (in_array($rowType, ['+', '-'], true) && !\Illuminate\Support\Str::startsWith(trim($formattedDisplay), ['+', '-'])) {
                                                    $formattedDisplay = $rowType . $formattedDisplay;
                                                }
                                            }
                                        @endphp
                                        <td>
                                            @if($isStatusField && $formattedDisplay !== '')
                                                <span class="badge {{ $statusClass }}">{{ $formattedDisplay }}</span>
                                            @elseif($isTypeField && $formattedDisplay !== '')
                                                <span class="badge {{ $typeClass }}">{{ $formattedDisplay }}</span>
                                            @elseif($isDetailsField)
                                                <div class="text-wrap" style="max-width:420px; white-space:normal;" title="{{ $formattedDisplay }}">
                                                    {{ $formattedDisplay !== '' ? $formattedDisplay : '--' }}
                                                </div>
                                            @else
                                                {{ $formattedDisplay !== '' ? $formattedDisplay : '--' }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ max(count($columns), 1) }}" class="text-center">{{ __('No Data Found!') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{ $rows->links('backend.include.__pagination') }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('single-script')
    <script>
        (function ($) {
            'use strict';

            $('#perPage').on('change', function () {
                $(this).closest('form').trigger('submit');
            });
        })(jQuery);
    </script>
@endpush
