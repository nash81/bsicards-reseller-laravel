<div class="side-nav">
    @php
        $height = setting('site_logo_height','global') == 'auto' ? 'auto' : setting('site_logo_height','global').'px';
        $width = setting('site_logo_width','global') == 'auto' ? 'auto' : setting('site_logo_width','global').'px';
    @endphp

    <div class="logo">
        <a href="{{ route('admin.dashboard') }}">
            <img
                class="logo-unfold"
                style="height:{{ $height }};width:{{ $width }}"
                src="{{asset(setting('site_logo','global'))}}"
                alt="{{ setting('site_title','global') }}"
            />
            <img
                class="logo-fold"
                style="max-height: 35px;"
                src="{{asset(setting('site_favicon','global'))}}"
                alt="{{ setting('site_title','global') }}"
            />
        </a>
    </div>

    <div class="side-nav-inside">
        <ul class="side-nav-menu">

            {{-- ************************************************************* Dashboard *********************************************************--}}
            <li class="side-nav-item {{ isActive('admin.dashboard') }}">
                <a href="{{route('admin.dashboard')}}"><i data-lucide="layout-dashboard"></i><span>{{ __('Dashboard')
                        }}</span></a>
            </li>

            {{-- ************************************************************* Customer Management *********************************************************--}}
            @canany(['customer-list','customer-login','customer-mail-send','customer-basic-manage','customer-balance-add-or-subtract','customer-change-password','all-type-status'])
            <li class="side-nav-item category-title">
                <span>{{ __('Customer Management') }}</span>
            </li>
            <li class="side-nav-item side-nav-dropdown {{ isActive(['admin.user*','admin.notification*']) }}">
                <a href="javascript:void(0);" class="dropdown-link">
                    <i data-lucide="users"></i><span>{{ __('Customers') }}</span>
                    <span class="right-arrow"><i data-lucide="chevron-down"></i></span></a>
                <ul class="dropdown-items">
                    @canany(['customer-list','customer-login','customer-mail-send','customer-basic-manage','customer-balance-add-or-subtract','customer-change-password','all-type-status'])
                    <li class="{{ isActive('admin.user.index') }}">
                        <a href="{{route('admin.user.index')}}"><i data-lucide="users"></i>{{ __('All Customers') }}</a>
                    </li>
                    <li class="{{ isActive('admin.user.active') }}">
                        <a href="{{ route('admin.user.active') }}"><i data-lucide="user-check"></i>{{ __('Active
                            Customers') }}</a>
                    </li>
                    <li class="{{ isActive('admin.user.closed') }}">
                        <a href="{{ route('admin.user.closed') }}"><i data-lucide="x-circle"></i>{{ __('Closed
                            Customers') }}</a>
                    </li>
                    <li class="{{ isActive('admin.user.disabled') }}">
                        <a href="{{ route('admin.user.disabled') }}"><i data-lucide="user-x"></i>{{ __('Disabled
                            Customers') }}</a>
                    </li>
                    <li class="{{ isActive('admin.user.new') }}">
                        <a href="{{ route('admin.user.new') }}"><i data-lucide="user-plus"></i>{{
                            __('Add New Customer') }}</a>
                    </li>
                    @endcanany

                    <li class="{{ isActive('admin.notification.all') }}">
                        <a href="{{ route('admin.notification.all') }}"><i data-lucide="megaphone"></i>{{
                            __('Notifications') }}</a>
                    </li>
                    @can('customer-mail-send')
                    <li class="{{ isActive('admin.user.mail-send.all') }}">
                        <a href="{{ route('admin.user.mail-send.all') }}"><i data-lucide="send"></i>{{ __('Send Email to
                            all') }}</a>
                    </li>
                    @endcan

                </ul>
            </li>
            @endcanany

            @canany(['kyc-list','kyc-action','kyc-form-manage'])
            <li class="side-nav-item side-nav-dropdown {{ isActive(['admin.kyc*']) }}">
                <a href="javascript:void(0);" class="dropdown-link"><i data-lucide="check-square"></i><span>{{ __('KYC Management') }}</span><span class="right-arrow"><i data-lucide="chevron-down"></i></span></a>
                <ul class="dropdown-items">
                    @canany(['kyc-list','kyc-action'])
                    <li class="{{ isActive('admin.kyc.pending') }}">
                        <a href="{{ route('admin.kyc.pending') }}"><i data-lucide="airplay"></i>{{ __('Pending KYC')
                            }}</a>
                    </li>
                    <li class="{{ isActive('admin.kyc.rejected') }}">
                        <a href="{{ route('admin.kyc.rejected') }}"><i data-lucide="file-warning"></i>{{ __('Rejected
                            KYC') }}</a>
                    </li>
                    <li class="{{ isActive('admin.kyc.all') }}">
                        <a href="{{ route('admin.kyc.all') }}"><i data-lucide="contact"></i>{{ __('All KYC Logs') }}</a>
                    </li>
                    @endcanany
                    @can('kyc-form-manage')
                    <li class="{{ isActive('admin.kyc-form*') }}">
                        <a href="{{ route('admin.kyc-form.index') }}"><i data-lucide="check-square"></i>{{ __('KYC Options') }}</a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany


            {{-- ************************************************************* Transactions *********************************************************--}}
            @canany(['transaction-list','user-paybacks','bank-profit'])
            <li class="side-nav-item category-title">
                <span>{{ __('Transactions') }}</span>
            </li>
            @can('transaction-list')
            <li class="side-nav-item {{ isActive('admin.transactions') }}">
                <a href="{{route('admin.transactions')}}"><i data-lucide="cast"></i><span>{{ __('Transactions') }}</span></a>
            </li>
            @endcan

            @endcanany

            {{-- ************************************************************* Essentials *********************************************************--}}
            @canany(['automatic-gateway-manage','manual-gateway-manage','deposit-list','deposit-action',
            'withdraw-list','withdraw-method-manage','withdraw-action','referral-create',
            'manage-referral','referral-edit','referral-delete','site-setting'])

            <li class="side-nav-item category-title">
                <span>{{ __('Essentials') }}</span>
            </li>

            @canany(['automatic-gateway-manage','manual-gateway-manage','deposit-list','deposit-action'])
            @can('automatic-gateway-manage')
            <li class="side-nav-item {{ isActive('admin.gateway*') }}">
                <a href="{{ route('admin.gateway.automatic') }}"><i data-lucide="door-open"></i><span>{{ __('Automatic
                        Gateways') }}</span></a>
            </li>
            @endcan

            <li class="side-nav-item side-nav-dropdown {{ isActive(['admin.deposit*']) }}">
                <a href="javascript:void(0);" class="dropdown-link"><i data-lucide="arrow-down-circle"></i><span>{{
                        __('Deposits') }}</span><span class="right-arrow"><i data-lucide="chevron-down"></i></span></a>
                <ul class="dropdown-items">

                    @can('automatic-gateway-manage')
                    <li class="{{ isActive('admin.deposit.method.list','auto') }}"><a
                            href="{{ route('admin.deposit.method.list','auto') }}"><i data-lucide="workflow"></i>{{
                            __('Automatic Methods') }}</a></li>
                    @endcan

                    @can('manual-gateway-manage')
                    <li class="{{ isActive('admin.deposit.method.list','manual') }}"><a
                            href="{{route('admin.deposit.method.list','manual')}}"><i data-lucide="compass"></i>{{
                            __('Manual Methods') }}</a></li>
                    @endcan

                    @canany(['deposit-list','deposit-action'])
                    <li class="{{ isActive('admin.deposit.manual.pending') }}"><a
                            href="{{ route('admin.deposit.manual.pending') }}"><i data-lucide="columns"></i>{{ __('Pending
                            Manual Deposits') }}</a></li>
                    <li class="{{ isActive('admin.deposit.history') }}"><a
                            href="{{ route('admin.deposit.history') }}"><i data-lucide="clipboard-check"></i>{{
                            __('Deposit History') }}</a></li>
                    @endcanany
                </ul>
            </li>
            @endcanany

            @canany(['withdraw-list','withdraw-method-manage','withdraw-action','withdraw-schedule'])
            <li class="side-nav-item side-nav-dropdown  {{ isActive(['admin.withdraw*']) }}">
                <a href="javascript:void(0);" class="dropdown-link"><i data-lucide="landmark"></i><span>{{ __('Withdraw')
                        }}</span><span class="right-arrow"><i data-lucide="chevron-down"></i></span></a>
                <ul class="dropdown-items">
                    @can('withdraw-method-manage')
                    <li class="{{ isActive('admin.withdraw.method.list','auto')  }}">
                        <a href="{{ route('admin.withdraw.method.list','auto') }}"><i data-lucide="workflow"></i>{{
                            __('Automatic Methods') }}</a>
                    </li>
                    <li class="{{ isActive('admin.withdraw.method.list','manual') }}">
                        <a href="{{route('admin.withdraw.method.list','manual')}}"><i data-lucide="compass"></i>{{
                            __('Manual Methods') }}</a>
                    </li>

                    @endcan
                    @canany(['withdraw-list','withdraw-action'])
                    <li class="{{ isActive('admin.withdraw.pending')  }}"><a
                            href="{{ route('admin.withdraw.pending') }}"><i data-lucide="wallet"></i>{{ __('Pending
                            Withdraws') }}</a></li>
                    @endcanany
                    @can('withdraw-schedule')
                    <li class="{{ isActive('admin.withdraw.schedule') }}"><a
                            href="{{ route('admin.withdraw.schedule') }}"><i data-lucide="alarm-clock"></i>{{ __('Withdraw
                            Schedule') }}</a></li>
                    @endcan
                    @can('withdraw-list')
                    <li class="{{ isActive('admin.withdraw.history') }}"><a
                            href="{{ route('admin.withdraw.history') }}"><i data-lucide="piggy-bank"></i>{{ __('Withdraw
                            History') }}</a></li>
                    @endcan
                </ul>
            </li>
            @endcanany

            @canany(['referral-create','manage-referral','referral-edit','referral-delete'])
            <li class="side-nav-item {{ isActive('admin.referral.*') }}">
                <a href="{{ route('admin.referral.index') }}"><i data-lucide="align-end-horizontal"></i><span>{{ __('Referral') }}</span></a>
            </li>
            @endcanany




            @endcanany

            {{-- ************************************************************* Appearance & Pages *********************************************************--}}
            @canany(['landing-page-manage','page-manage','footer-manage','navigation-manage','custom-css'])
            <li class="side-nav-item category-title">
                <span>{{ __('Appearance & Pages') }}</span>
            </li>

            @can('landing-page-manage')
            {{-- site theme Management--}}
            <li class="side-nav-item side-nav-dropdown  {{ isActive(['admin.theme*','admin.custom-css']) }}">
                <a href="javascript:void(0);" class="dropdown-link"><i data-lucide="palette"></i><span>{{ __('Appearance') }}</span><span class="right-arrow"><i data-lucide="chevron-down"></i></span></a>
                <ul class="dropdown-items">
                    {{-- <li class="{{ isActive('admin.theme.site') }}">
                        <a href="{{ route('admin.theme.site') }}"><i data-lucide="roller-coaster"></i>{{ __('Site Theme')
                            }}</a>
                    </li> --}}
                    <li class="{{ isActive('admin.theme.dynamic-landing') }}">
                        <a href="{{ route('admin.theme.dynamic-landing') }}"><i data-lucide="warehouse"></i>{{ __('Dynamic
                            Landing Theme') }}</a>
                    </li>

                    @can('custom-css')
                    <li class="side-nav-item {{ isActive('admin.custom-css') }}">
                        <a href="{{ route('admin.custom-css') }}"><i data-lucide="braces"></i><span>{{ __('Custom CSS') }}</span></a>
                    </li>
                    @endcan
                </ul>
            </li>
            {{-- end site theme Management--}}

            <li class="side-nav-item side-nav-dropdown  {{ isActive(['admin.page.section.section*','admin.footer-content']) }}">
                <a href="javascript:void(0);" class="dropdown-link"><i data-lucide="home"></i><span>{{ __('Landing Page')
                        }}</span><span class="right-arrow"><i data-lucide="chevron-down"></i></span></a>
                <ul class="dropdown-items">
                    @can('page-manage')
                    <li class="side-nav-item {{ isActive('admin.page.section.management*') }}">
                        <a href="{{ route('admin.page.section.management') }}"><i data-lucide="list-end"></i><span>{{ __('Section Management') }}</span></a>
                    </li>
                    @endcan
                    @foreach($landingSections as $section)
                    <li class="@if(request()->is('admin/page/section/'.$section->code)) active @endif">
                        <a href="{{ route('admin.page.section.section',$section->code) }}"><i data-lucide="egg"></i>{{
                            $section->name }}</a>
                    </li>
                    @endforeach
                    @can('footer-manage')
                    <li class="side-nav-item {{ isActive('admin.footer-content') }}">
                        <a href="{{ route('admin.footer-content') }}"><i data-lucide="list-end"></i><span>{{ __('Footer Contents')
                                }}</span></a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcan

            @can('page-manage')
            <li class="side-nav-item side-nav-dropdown {{ isActive(['admin.page.edit*','admin.page.create']) }}">
                <a href="javascript:void(0);" class="dropdown-link"><i data-lucide="layout-grid"></i><span>{{ __('Pages')
                        }}</span><span class="right-arrow"><i data-lucide="chevron-down"></i></span></a>
                <ul class="dropdown-items">
                    @foreach($pages as $page)
                    <li class="@if(request()->is('admin/page/edit/'.$page->code)) active @endif">
                        <a href="{{ route('admin.page.edit',$page->code) }}"><i data-lucide="egg"></i>{{ $page->title
                            }}</a>
                    </li>
                    @endforeach
                    <li class="{{ isActive('admin.page.create') }}">
                        <a href="{{ route('admin.page.create') }}"><i data-lucide="egg"></i>{{ __('Add New Page') }}</a>
                    </li>
                </ul>
            </li>
            @endcan
            @can('navigation-manage')
            <li class="side-nav-item {{ isActive('admin.navigation*') }}">
                <a href="{{ route('admin.navigation.menu') }}"><i data-lucide="menu"></i><span>{{ __('Site Navigations')
                        }}</span></a>
            </li>
            @endcan

            @endcanany

            {{-- ************************************************************* Site Settings *********************************************************--}}
            @canany(['site-setting','email-setting','plugin-setting','page-manage','language-setting','sms-setting','push-notification-setting','notification-tune-setting'])
            <li class="side-nav-item category-title">
                <span>{{ __('Site Settings') }}</span>
            </li>
            <li class="side-nav-item side-nav-dropdown {{ isActive(['admin.settings*','admin.language*','admin.page.setting']) }}">
                <a href="javascript:void(0);" class="dropdown-link"><i data-lucide="settings"></i>
                    <span>{{ __('Settings') }}</span><span class="right-arrow"><i data-lucide="chevron-down"></i></span></a>
                <ul class="dropdown-items">
                    @can('site-setting')
                    <li class="{{ isActive('admin.settings.site') }}">
                        <a href="{{route('admin.settings.site')}}"><i data-lucide="settings-2"></i>{{ __('Site Settings') }}</a>
                    </li>
                    @endcan
                        @can('site-setting')
                            <li class="{{ isActive('admin.settings.cardsetting') }}">
                                <a href="{{ route('admin.settings.cardsetting') }}"><i data-lucide="credit-card"></i>{{ __('Cardsetting') }}</a>
                            </li>
                        @endcan
                    @can('email-setting')
                    <li class="{{ isActive('admin.settings.mail') }}">
                        <a href="{{ route('admin.settings.mail') }}"><i data-lucide="inbox"></i>{{ __('Email Settings') }}</a>
                    </li>
                    @endcan
                    @can('site-setting')
                    <li class="{{ isActive('admin.settings.seo.meta') }}">
                        <a href="{{route('admin.settings.seo.meta')}}"><i data-lucide="search-code"></i>{{ __('SEO Meta Settings') }}</a>
                    </li>
                    @endcan
                    @can('language-setting')
                    <li class="{{ isActive('admin.language*') }}">
                        <a href="{{ route('admin.language.index') }}"><i data-lucide="languages"></i><span>{{ __('Language Settings') }}</span></a>
                    </li>
                    @endcan
                    @can('page-manage')
                    <li class="side-nav-item {{ isActive('admin.page.setting') }}">
                        <a href="{{ route('admin.page.setting') }}"><i data-lucide="layout"></i><span>{{ __('Page Settings')
                                }}</span></a>
                    </li>
                    @endcan

                    @can('plugin-setting')
                    <li class="{{ isActive('admin.settings.plugin','system') }}">
                        <a href="{{ route('admin.settings.plugin','system') }}"><i data-lucide="toy-brick"></i>{{ __('Plugin Settings') }}</a>
                    </li>
                    @can('sms-setting')
                    <li class="{{ isActive('admin.settings.plugin','sms') }}">
                        <a href="{{ route('admin.settings.plugin','sms') }}"><i data-lucide="message-circle"></i>{{ __('SMS Settings') }}</a>
                    </li>
                    @endcan
                    @can('push-notification-setting')
                    <li class="{{ isActive('admin.settings.plugin','notification') }}">
                        <a href="{{ route('admin.settings.plugin','notification') }}"><i data-lucide="bell-ring"></i>{{ __('Push Notification') }}</a>
                    </li>
                    @endcan
                    @can('notification-tune-setting')
                    <li class="{{ isActive('admin.settings.notification.tune') }}">
                        <a href="{{ route('admin.settings.notification.tune') }}"><i data-lucide="volume-2"></i>{{ __('Notification Tune') }}</a>
                    </li>
                    @endcan

                    @endcan

                </ul>
            </li>

            @endcanany

            {{-- ************************************************************* Access Management *********************************************************--}}
            @canany(['role-list','role-create','role-edit','staff-list','staff-create','staff-edit'])
            <li class="side-nav-item category-title">
                <span>{{ __('Access Management') }}</span>
            </li>
            <li class="side-nav-item side-nav-dropdown {{ isActive(['admin.roles*','admin.staff*']) }}">
                <a href="javascript:void(0);" class="dropdown-link"><i data-lucide="users"></i><span>{{ __('System Access') }}</span>
                    <span class="right-arrow"><i data-lucide="chevron-down"></i></span></a>
                <ul class="dropdown-items">
                    @canany(['role-list','role-create','role-edit'])
                    <li class="{{ isActive('admin.roles*') }}">
                        <a href="{{route('admin.roles.index')}}"><i data-lucide="contact"></i><span>{{ __('Manage Roles')
                                }}</span></a>
                    </li>
                    @endcanany
                    @canany(['staff-list','staff-create','staff-edit'])
                    <li class="{{ isActive('admin.staff*') }}">
                        <a href="{{route('admin.staff.index')}}"><i data-lucide="user-cog"></i><span>{{ __('Manage Staffs')
                                }}</span></a>
                    </li>
                    @endcanany
                </ul>
            </li>
            @endcanany

            @if(setting('multi_branch'))
                {{-- ************************************************************* Branch Management *********************************************************--}}
                @canany(['branch-list','branch-create','branch-edit','branch-staff-list','branch-staff-create','branch-staff-edit'])
                <li class="side-nav-item category-title">
                    <span>{{ __('Manage Bank Branches') }}</span>
                </li>
                <li class="side-nav-item side-nav-dropdown {{ isActive(['admin.branch*','admin.branch-staff*']) }}">
                    <a href="javascript:void(0);" class="dropdown-link"><i data-lucide="split"></i><span>{{ __('Manage
                            Branches') }}</span>
                        <span class="right-arrow"><i data-lucide="chevron-down"></i></span></a>
                    <ul class="dropdown-items">
                        @canany(['branch-list','branch-create','branch-edit'])
                        <li class="side-nav-item {{ isActive('admin.branch*') }}">
                            <a href="{{route('admin.branch.index')}}"><i data-lucide="landmark"></i><span>{{ __('All Branch')
                                    }}</span></a>
                        </li>
                        @endcanany
                        @can(['branch-staff-list','branch-staff-create','branch-staff-edit'])
                        <li class="side-nav-item {{ isActive('admin.branch-staff*') }}">
                            <a href="{{route('admin.branch-staff.index')}}"><i data-lucide="user-cog"></i><span>{{ __('Branch
                                    Staff') }}</span></a>
                        </li>
                        @endcan
                    </ul>
                </li>
                @endcanany
            @endif

            @canany((['subscriber-list','subscriber-mail-send','support-ticket-list','support-ticket-action','email-template','sms-template','sms-template','push-notification-template']))
            <li class="side-nav-item category-title">
                <span>{{ __('Support & Newsletter') }}</span>
            </li>

            @canany(['sms-template','email-template','push-notification-template'])
            <li class="side-nav-item side-nav-dropdown {{ isActive(['admin.email-template','admin.template.*']) }}">
                <a href="javascript:void(0);" class="dropdown-link">
                    <i data-lucide="mail"></i><span>{{ __('Templates') }}</span>
                    <span class="right-arrow"><i data-lucide="chevron-down"></i></span>
                </a>

                <ul class="dropdown-items">
                    @can('email-template')
                    <li class="{{ isActive('admin.email-template') }}">
                        <a href="{{ route('admin.email-template') }}"><i data-lucide="mail"></i><span>{{ __('Email Template')
                                }}</span></a>
                    </li>
                    @endcan
                    @can('sms-template')
                    <li class="{{ isActive('admin.template.sms.index') }}">
                        <a href="{{ route('admin.template.sms.index') }}"><i data-lucide="message-square"></i><span>{{ __('SMS
                                Template') }}</span></a>
                    </li>
                    @endcan
                    @can('push-notification-template')
                    <li class="{{ isActive('admin.template.notification.index') }}">
                        <a href="{{ route('admin.template.notification.index') }}"><i data-lucide="bell-ring"></i><span>{{
                                __('Push Notification Template') }}</span></a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany

            @canany(['subscriber-list','subscriber-mail-send','support-ticket-list','support-ticket-action'])
            <li class="side-nav-item side-nav-dropdown {{ isActive(['admin.subscriber','admin.ticket*']) }}">
                <a href="javascript:void(0);" class="dropdown-link">
                    <i data-lucide="wrench"></i><span>{{ __('Subscriber & Support') }}</span>
                    <span class="right-arrow"><i data-lucide="chevron-down"></i></span>
                </a>
                <ul class="dropdown-items">
                    @canany(['subscriber-list','subscriber-mail-send'])
                    <li class="{{ isActive('admin.subscriber') }}">
                        <a href="{{ route('admin.subscriber') }}"><i data-lucide="mail-open"></i><span>{{ __('All Subscriber')
                                }}</span></a>
                    </li>
                    @endcanany
                    @canany(['support-ticket-list','support-ticket-action'])
                    <li class="{{ isActive('admin.ticket*') }}">
                        <a href="{{ route('admin.ticket.index') }}"><i data-lucide="wrench"></i><span>{{ __('Support Tickets')
                                }}</span></a>
                    </li>
                    @endcanany
                </ul>
            </li>
            @endcanany
            @endcanany

            @canany(['manage-cron-job','cron-job-create','cron-job-edit','cron-job-logs','cron-job-run','cron-job-delete','clear-cache','application-details'])
            <li class="side-nav-item category-title">
                <span>{{ __('System') }}</span>
            </li>
            <li class="side-nav-item side-nav-dropdown {{ isActive(['admin.clear-cache','admin.application-info','admin.cron.jobs.*']) }}">
                <a href="javascript:void(0);" class="dropdown-link">
                    <i data-lucide="power"></i><span>{{ __('System') }}</span>
                    <span class="right-arrow"><i data-lucide="chevron-down"></i></span>
                </a>
                <ul class="dropdown-items">
                    @can('manage-cron-job')
                    <li class="{{ isActive('admin.cron.jobs.*') }}">
                        <a href="{{ route('admin.cron.jobs.index') }}"><i data-lucide="alarm-clock"></i><span>{{ __('Cron Jobs') }}</span></a>
                    </li>
                    @endcan
                    @can('clear-cache')
                    <li class="{{ isActive('admin.clear-cache') }}">
                        <a href="{{ route('admin.clear-cache') }}"><i data-lucide="trash-2"></i><span>{{ __('Clear Cache') }}</span></a>
                    </li>
                    @endcan
                    @can('application-details')
                    <li class="{{ isActive('admin.application-info') }}">
                        <a href="{{ route('admin.application-info') }}">
                            <i data-lucide="app-window"></i>
                            <span>{{ __('Application Details') }}</span>
                            <span class="badge yellow-color">{{ config('app.version') }}</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @endcanany
        </ul>
    </div>
</div>
