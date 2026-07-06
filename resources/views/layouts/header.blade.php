<div class="navbar-header">
    <a class="navbar-brand" href="<?php echo URL::to('/'); ?>">
        <img src="{{ asset('images/ghalbit-maritronix-icon.svg') }}" alt="GHALBIT MARITRONIX" class="ghalbit-header-logo-mark">
        <span class="ghalbit-header-brand">
            <strong>GHALBIT MARITRONIX</strong>
            <small>Integrated Maritime &amp; Land Intelligence Platform</small>
        </span>
    </a>
</div>
<div class="navbar-collapse">
    <!-- ============================================================== -->
    <!-- toggle and nav items -->
    <!-- ============================================================== -->
    <ul class="navbar-nav mr-auto mt-md-0">
        <!-- This is  -->
        <li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="mdi mdi-menu"></i></a> </li>
        <li class="nav-item m-l-10"> <a class="nav-link sidebartoggler hidden-sm-down text-muted waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </li>
        <!-- ============================================================== -->
        <!-- Comment -->
    </ul>
    <!-- ============================================================== -->
    <!-- User profile and search -->
    <!-- ============================================================== -->
    <div style="visibility: hidden;" class="language-list icon d-flex align-items-center text-light ml-2" id="language_dropdown_box">
        <div class="language-select">
            <i class="fa fa-globe"></i>
        </div>
        <div class="language-options">
            <select class="form-control changeLang text-dark" id="language_dropdown"></select>
        </div>
    </div>
    <ul class="navbar-nav my-lg-0">
       

        <!-- Profil -->
        <li class="nav-item dropdown">

		 <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="{{ asset('images/ghalbit-maritronix-icon.svg') }}" onerror="this.onerror=null; this.src='{{ asset('images/ghalbit-maritronix-icon.svg') }}';" alt="user" class="profile-pic userimage" id="user_avatar"></a>
        
            <div class="dropdown-menu dropdown-menu-right scale-up">
                <ul class="dropdown-user">
                    <li>
                        <div class="dw-user-box">
                            <div class="u-img"><img src="{{ asset('images/ghalbit-maritronix-icon.svg') }}" onerror="this.onerror=null; this.src='{{ asset('images/ghalbit-maritronix-icon.svg') }}';" id="user_image" class="userimage" alt="user" style="max-width: 45px;"></div>
                            
                            <div class="u-text">
                            <h4 id="username"></h4>
                            <small class="ghalbit-profile-role">Store Panel Operator</small>
                            </div>
                        </div>
                    </li>
                    <li role="separator" class="divider"></li>
                    <li><a href="{{ route('user.profile') }}"><i class="ti-user"></i>  {!! trans('lang.user_profile') !!}</a></li>
                    <li role="separator" class="divider"></li>
                    <li><a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-power-off"></i> {{ __('Logout') }}</a></li>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                    </form>
                </ul>
            </div>
        </li>
    </ul>
</div>

<style>
    .ghalbit-header-logo-mark {
        width: 52px;
        height: 52px;
        flex: 0 0 52px;
        border-radius: 16px;
        box-shadow: 0 12px 28px rgba(0, 217, 255, .18);
    }

    .ghalbit-header-brand {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;
        line-height: 1.1;
    }

    .ghalbit-header-brand strong {
        color: #f5fbff;
        font-weight: 800;
        letter-spacing: .08em;
        font-size: .98rem;
        text-transform: uppercase;
    }

    .ghalbit-header-brand small {
        color: #8cccf5;
        font-size: .74rem;
        letter-spacing: .02em;
        white-space: nowrap;
    }

    .ghalbit-profile-role {
        display: block;
        color: #9ec8ea;
        font-size: .74rem;
        margin-top: 2px;
        font-weight: 600;
    }

    @media (max-width: 991.98px) {
        .ghalbit-header-brand small {
            display: none;
        }
    }

    @media (max-width: 767.98px) {
        .ghalbit-header-brand {
            display: none;
        }
    }
</style>

<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<script src="https://www.gstatic.com/firebasejs/7.2.0/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/7.2.0/firebase-firestore.js"></script>
<script src="https://www.gstatic.com/firebasejs/7.2.0/firebase-storage.js"></script>
<script src="https://www.gstatic.com/firebasejs/7.2.0/firebase-auth.js"></script>
<script src="https://www.gstatic.com/firebasejs/7.2.0/firebase-database.js"></script>
<script src="{{ asset('js/geofirestore.js') }}"></script>
<script src="https://cdn.firebase.com/libs/geofire/5.0.1/geofire.min.js"></script>
<script src="{{ asset('js/crypto-js.js') }}"></script>
<script src="{{ asset('js/jquery.cookie.js') }}"></script>
<script src="{{ asset('js/jquery.validate.js') }}"></script>
<script>
    var doNotDeleteAlert = "{{trans('lang.this_is_for_demo_we_can_not_allow_to_delete')}}";

    // Wait for DOM + jQuery to be fully loaded
    $(document).ready(function () {
        var database = firebase.firestore();
        let placeholderImage = '';

        // 1. Fetch placeholder from Firestore
        database.collection('settings').doc('placeHolderImage')
            .get()
            .then(function (snapshot) {
                if (snapshot.exists && snapshot.data().image) {
                    placeholderImage = snapshot.data().image;
                }
                applyGlobalPlaceholder();
                applyUserImagePlaceholder();   // Now safe to run
            })
            .catch(function (err) {
                console.error('Firestore placeholder error:', err);
                applyGlobalPlaceholder();
                applyUserImagePlaceholder();
            });

        // 2. Apply onerror fallback to ALL images
        function applyGlobalPlaceholder() {
            if (!placeholderImage) return;

            $('img').each(function () {
                var $img = $(this);
                if (!$img.data('placeholder')) {
                    $img.data('placeholder', placeholderImage);
                }

                // Replace or set onerror
                $img.off('error').on('error', function () {
                    if ($(this).data('placeholder')) {
                        this.src = $(this).data('placeholder');
                    }
                });
            });
        }

        // 3. FORCE placeholder into #user_image and #user_avatar
        function applyUserImagePlaceholder() {
            if (!placeholderImage) return;

            var $userImg   = $('#user_image');
            var $avatarImg = $('#user_avatar');

            // Update #user_image (dropdown)
            if ($userImg.length) {
                if (!$userImg.data('original-src')) {
                    $userImg.data('original-src', $userImg.attr('src'));
                }
                $userImg.attr('src', placeholderImage);   // This is what you want
            }

            // Update #user_avatar (navbar trigger) – optional but consistent
            if ($avatarImg.length) {
                if (!$avatarImg.data('original-src')) {
                    $avatarImg.data('original-src', $avatarImg.attr('src'));
                }
                $avatarImg.attr('src', placeholderImage);
            }
        }
    });
   
</script>
