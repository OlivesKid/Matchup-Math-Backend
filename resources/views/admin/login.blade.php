
<!DOCTYPE html>
<!--[if lt IE 7]>  <html class="lt-ie7"> <![endif]-->
<!--[if IE 7]>     <html class="lt-ie8"> <![endif]-->
<!--[if IE 8]>     <html class="lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>{{$header['title']}} {{config("constant.TITLE_SEPARATOR")}}{{config("constant.PLATFORM_NAME")}}</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="{{url('assets/css/app.min.css')}}" type="text/css">
        <script src="{{url('assets/js/app.min.js')}}" ></script>
    </head>

    <body class="bg-dark">
        <div class="center-block w-xxl w-auto-xs p-v-md">
            <div class="navbar">
                <div class="navbar-brand m-t-lg text-center">
                    <div class="navbar-brand m-t-lg text-center">
                        <img src="{{url(config('constant.LOGO_URL'))}}" alt="logo" style="width: 199px;height: 50px">
                    </div> 
                </div>
            </div>

            <div class="p-lg panel md-whiteframe-z1 text-color m">
                <div class="m-b text-sm">
                    Sign in with your {{config("constant.PLATFORM_NAME")}} Account
                </div>
                <?php 
//                    dd($errors);
                    if(isset($errors) && $errors->first()!=''){ ?>
                            <div class="alert alert-danger" style="text-align: center;">
                                <strong><?php echo $errors->first(); ?></strong>
                            </div>
                    <?php }
                    $session = \Session::get("msg");
//                    dd($session);
                    if($session != "" && \General::is_json($session)){ 
                        $session = json_decode($session,true);
                        \Session::forget("msg");
                        ?>
                            <div class="alert alert-danger" style="text-align: center;">
                                <strong><?php echo $session['msg']; ?></strong>
                            </div>
                <?php } ?>
                <form name="form" method='Post' action='{{URL::to('admin/login')}}'>
                    <div class="md-form-group float-label">
                        <input type="email" name='uname' class="md-input" required>
                        <label>Email</label>
                    </div>
                    <div class="md-form-group float-label">
                        <input type="password" name='password' class="md-input" required>
                        <input type="hidden" name='_token' value='{{csrf_token()}}'>
                        <label>Password</label>
                    </div>      
                    <div class="m-b-md">        
                        <label class="md-check">
                            <input type="checkbox" name='remember'><i class="indigo"></i> Keep me signed in
                        </label>
                    </div>
                    <button md-ink-ripple type="submit" class="md-btn md-raised indigo btn-block p-h-md">Sign in</button>
                </form>
            </div>

        </div>
    </body>
</html>