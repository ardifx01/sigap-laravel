<div class="authentication-wrapper authentication-cover">
  <div class="authentication-inner row m-0">
    <!-- /Left Text -->
    <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center p-5">
      <div class="w-100 d-flex justify-content-center">
        <div>
          <!-- Logo -->
          <a href="{{url('/')}}" class="app-brand auth-cover-brand gap-2"><x-app-logo /></a>
          <!-- /Logo -->
          <img src="{{asset('assets/img/illustrations/boy-with-rocket-light.png')}}" class="img-fluid" alt="Login image" width="700"/>
        </div>
      </div>
    </div>
    <!-- /Left Text -->

    <!-- Right Text -->
    <div class="card col-12 col-lg-5 col-xl-4">
      <div class="d-flex align-items-center authentication-bg p-sm-12 p-6 h-100">
        <div class="w-px-400 mx-auto mt-sm-12 mt-8">
          {{ $slot }}
          {{-- Social login buttons removed --}}
        </div>
      </div>
    </div>
    <!-- /Right Text -->
  </div>
</div>
