<div class="top">
    <div class="step-progress">
        <div class="single-step finished">
            <h4>Step 1</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/1.png') }}" alt=""></div>
        </div>
        <div class="single-step finished">
            <h4>Step 2</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/2.png') }}" alt=""></div>
        </div>
        <div @class([
            'single-step',
            'finished' => Route::is('install.step.three')
        ])>
            <h4>Step 3</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/3.png') }}" alt=""></div>
        </div>
        <div @class([
            'single-step',
            'finished' => Route::is('install.step.four')
        ])>
            <h4>Step 4</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/4.png') }}" alt=""></div>
        </div>
        <div @class([
            'single-step',
            'finished' => Route::is('install.step.five')
        ])>
            <h4>Step 5</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/5.png') }}" alt=""></div>
        </div>
        <div @class([
            'single-step',
            'finished' => Route::is('install.step.finish')
        ])>
            <h4>Step 6</h4>
            <div class="icon"><img src="{{ asset('global/installer/images/6.png') }}" alt=""></div>
        </div>
    </div>
    <h3>@yield('title')</h3>
    <p>Just make sure to provide all the correct informations to install the application</p>
</div>
