      <footer class="footer-bottom">
          <div class="container">
              <div class="row">
                  <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                     <p class="theme-description">Copyright © 2021 Yize Games</p>
                  </div>
                  <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                     <p class="theme-description text-right"><a href="{{url('privacy-policy')}}">Privacy Policy</a></p>
                  </div>
              </div>
          </div>
      </footer>
      <script src="{{url('assets/site/js/jquery.min.js')}}"></script>
      <script src="{{url('assets/site/js/bootstrap.min.js')}}"></script>
      <script type="text/javascript">
        $(document).ready(function () {
            var loc = window.location.href;      
            $('#navbarSupportedContent').find('a').each(function() {
                $(this).toggleClass('active', $(this).attr('href') == loc);
            });  
        });
      </script>
      @yield('footer')
   </body>
</html>