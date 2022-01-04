<aside class="app-aside modal fade" id="aside" role="menu" style="display: none;"style="display: none;">
    <div class="left">
    <div class="box bg-white">
        <div class="navbar md-whiteframe-z1 no-radius md-closed">
            <a class="navbar-brand text-center">
                <img src="{{url(config('constant.LOGO_URL'))}}" alt="logo" style="width: 199px;height: 50px;margin-top: 6px;">
            </a>
        </div>
        <div class="box-row">
            <div class="box-cell scrollable hover">
                <div class="box-inner">
                    
                    <div  id="nav" >
                        <nav ui-nav>
                            <ul class="nav">
                                <li id="dashboard">
                                    <a md-ink-ripple  href="{{URL::to("admin/dashboard")}}">
                                        <i class="icon mdi mdi-view-dashboard i-20"></i>
                                        <span class="font-normal">Dashboard</span>
                                    </a>
                                </li>

                                <li id="users_list">
                                    <a md-ink-ripple  href="{{URL::to("admin/user-list")}}">
                                        <i class="icon fa fa-users"></i>
                                        <span class="font-normal">Users</span>
                                    </a>
                                </li>
                                <li id="multi_player_challenge">
                                    <a md-ink-ripple  href="{{URL::to("admin/multi-player-challenge-report")}}">
                                        <i class="icon mdi mdi-file-document-box i-20"></i>
                                        <span class="font-normal">Multi Player Challenges</span>
                                    </a>
                                </li>
                                
                                <li id="multi_player_report">
                                    <a md-ink-ripple  href="{{URL::to("admin/multi-player-report")}}">
                                        <i class="icon mdi mdi-file-document-box i-20"></i>
                                        <span class="font-normal">Multi Player  Report</span>
                                    </a>
                                </li>
                                
                                  <li id="single_player_report">
                                    <a md-ink-ripple  href="{{URL::to("admin/single-player-report")}}">
                                        <i class="icon mdi mdi-file-document-box i-20"></i>
                                        <span class="font-normal">Single Player  Report</span>
                                    </a>
                                </li>
                                
                            
                                  <li id="multi_player_statics">
                                    <a md-ink-ripple  href="{{URL::to("admin/multi-player-statics")}}">
                                        <i class="icon mdi mdi-file-document-box i-20"></i>
                                        <span class="font-normal">Multi Player Statics  </span>
                                    </a>
                                </li>
                                 
                                  <li id="single_player_statics">
                                    <a md-ink-ripple  href="{{URL::to("admin/single-player-statics")}}">
                                        <i class="icon mdi mdi-file-document-box i-20"></i>
                                        <span class="font-normal">Single Player  Statics</span>
                                    </a>
                                </li>
                               
                               
                             
                           
                         
                                
                         
                                <li class="b-b b m-v-sm"></li>
                                <li id="settings">
                                    <a md-ink-ripple href="{{URL::to('admin/settings')}}" >  <!--It should open popup-->
                                        <i class="icon fa fa-gear i-20"></i>
                                        <span>Settings</span>
                                    </a>
                                </li>
                                
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</aside>
