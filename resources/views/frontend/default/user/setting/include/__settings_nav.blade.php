<div class="col-xl-12 col-lg-12 col-md-12 col-12">
    <div class="settings-tab-nav">
        <nav class="nav nav-tabs">
            <a href="{{ route('user.setting.show') }}" class="nav-link {{ isActive('user.setting.show') }}">
                <i data-lucide="user"></i>
                <span>{{ __('Profile Settings') }}</span>
            </a>
            <a href="{{ route('user.change.password') }}" class="nav-link {{ isActive('user.change.password') }}">
                <i data-lucide="key"></i>
                <span>{{ __('Change Password') }}</span>
            </a>
            <a href="{{ route('user.setting.security') }}" class="nav-link {{ isActive('user.setting.security') }}">
                <i data-lucide="lock"></i>
                <span>{{ __('Security Settings') }}</span>
            </a>
            <a href="{{ route('user.kyc') }}" class="nav-link {{ isActive('user.kyc*') }}">
                <i data-lucide="file-text"></i>
                <span>{{ __('ID Verification') }}</span>
            </a>
            <a href="{{ route('user.notification.all') }}" class="nav-link {{ isActive('user.notification.all') }}">
                <i data-lucide="bell"></i>
                <span>{{ __('All Notifications') }}</span>
            </a>
            <a href="{{ route('user.setting.action') }}" class="nav-link {{ isActive('user.setting.action') }}">
                <i data-lucide="settings"></i>
                <span>{{ __('Account Closing') }}</span>
            </a>
        </nav>
    </div>
</div>
