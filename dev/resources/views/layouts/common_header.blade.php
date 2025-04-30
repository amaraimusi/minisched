<header class="navbar navbar-expand-lg  navbar-light bg-success mb-1">
    <div class="container-fluid d-flex ">
        <h3 class="header-name p-2 mt-2 ">
            <a class="navbar-brand fs-3 text-light" href="{{ url('/home') }}">{{ config('app.name') }}</a>
        </h3>    
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item dropdown">
                
                    @auth
                        <div class="dropdown">
                            <button class="btn btn-success btn-lg text-light dropdown-toggle" type="button" id="dropdown_topmenu1" data-bs-toggle="dropdown" aria-expanded="false">
                            {{$userInfo['nickname']}}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdown_topmenu1">
                                <li><span class="dropdown-item">{{$userInfo['authority_wamei']}}</span></li>
                                <li><span class="dropdown-item">{{$userInfo['name']}}</span></li>
                                <li><a href="logout" class="dropdown-item">ログアウト</a></li>
                            </ul>
                        </div>
                        
                    @else
                        <a href="{{ route('login') }}" class="nav-link text-light">ログイン</a>
                    @endguest
        
                </li>

            </ul>
        </div><!-- navbarSupportedContent -->
    </div><!-- container-fluid -->
</header>

    