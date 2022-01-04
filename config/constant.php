<?php

return [
    'PLATFORM_NAME'                     => "MATcHUP",
    'TITLE_SEPARATOR'                   => " | ",
    'SITE_URL'                          => "matchup.com",
    'LOGO_URL'                          => "assets/images/logo.png",
    'ADMIN_LOGO_URL'                    => "assets/images/logo.png",
    'UPLOAD_DIR_PATH'                   => public_path()."/assets/uploads/",
    'USER_AVATAR_PATH'                  => public_path().'/assets/uploads/avatar/',
    'USER_AVATAR_PATH_LINK'             => url('/assets/uploads/avatar/'), 
    // 'USER_AVATAR_PATH_LINK'             => \URL::to('/assets/uploads/avatar/'),
    'GAME_AVATAR_PATH'                  => public_path('/assets/images/game_level/'),
    'GAME_AVATAR_PATH_LINK'             => url('/assets/images/game_level/'), 
   
    'AUTH_TOKEN_STATUS'                 => 0,
    'ACCOUNT_ACTIVATION_TOKEN_STATUS'   => 1,
    'FORGETPASS_TOKEN_STATUS'           => 2,
    'OTP_TOKEN_STATUS'                  => 3,
    
    'USER_INACTIVE_STATUS'              => 0,
    'USER_ACTIVE_STATUS'                => 1,
    'USER_PENDING_STATUS'               => 2,
    'USER_SUSPEND_STATUS'               => 3,
    
    'WEB_DEVICE'                        => 0,
    'ANDROID_APP_DEVICE'                => 1,
    'IPHONE_APP_DEVICE'                 => 2,

    'FACEBOOK_PAGE_URL'                 => "https://www.facebook.com/MatchUp-Math-107960188181567",
    'TWITTER_PAGE_URL'                  => "https://twitter.com/MathMatchup",
    'SKYPE_PAGE_URL'                    => "https://www.skype.com/#",
    'INSTAGRAM_PAGE_URL'                => "https://instagram.com/matchup_math",
    'GOOGLE_PLUS_PAGE_URL'              => "https://plus.google.com/#",
    'TELEGRAM_PAGE_URL'                 => "https://t.me/joinchat/#",
    'LINKEDIN_PAGE_URL'                 => "https://linkedin.com/#",
    'GITHUB_PAGE_URL'                   => "https://github.com/#",
    'MEDIUM_PAGE_URL'                   => "https://medium.com/#",
    'YOUTUBE_PAGE_URL'                  => "https://youtube.com/#",
    'CONTACT_US_MAIL'                   => "contact@matchup.com",
    'SUPPORT_MAIL'                      => "support@matchup.com",

    'DEFAULT_ADMIN_ID'                  => 1,

    'NODE_SERVER'                       => env('NODE_SERVER','192.168.0.104'),
    'NODE_PORT'                         => env('NODE_PORT','3333'),
    
    'PLAY_STORE'                       => 'play.google.com',
    'APP_STORE'                        => 'play.iphone.com',
    'DUMMY_AVATAR_IMG'                 =>  'my_avatar.jpg',
    'SYSTEM_EMAIL'                      => env('SYSTEM_EMAIL',"support@matchup.com"),
    'SYSTEM_EMAIL_NAME'                 => env('SYSTEM_EMAIL_NAME','Yize Games') ,

];