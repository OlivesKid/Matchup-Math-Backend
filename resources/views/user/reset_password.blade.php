

<!DOCTYPE html>
<html>
<style>
body {font-family: Arial, Helvetica, sans-serif;
      width: 50%;
      margin-left: 25%;
      margin-top: 10%;
}
* {box-sizing: border-box}

/* Full-width input fields */
input[type=text], input[type=password] {
    width: 100%;
    padding: 15px;
    margin: 5px 0 22px 0;
    display: inline-block;
    border: none;
    background: #f1f1f1;
}

input[type=text]:focus, input[type=password]:focus {
    background-color: #ddd;
    outline: none;
}

hr {
    border: 1px solid #f1f1f1;
    margin-bottom: 25px;
}

/* Set a style for all buttons */
button {
    background-color: #4CAF50;
    color: white;
    padding: 14px 20px;
    margin: 8px 0;
    border: none;
    cursor: pointer;
    width: 100%;
    opacity: 0.9;
}

button:hover {
    opacity:1;
}

/* Extra styles for the cancel button */
.cancelbtn {
    padding: 14px 20px;
    background-color: #f44336;
}

/* Float cancel and signup buttons and add an equal width */
.cancelbtn, .signupbtn {
  float: left;
  width: 50%;
  margin-left: 25%;
}

/* Add padding to container elements */
.container {
    padding: 16px;
}

/* Clear floats */
.clearfix::after {
    content: "";
    clear: both;
    display: table;
}

/* Change styles for cancel button and signup button on extra small screens */
@media screen and (max-width: 300px) {
    .cancelbtn, .signupbtn {
       width: 100%;
    }
}
</style>
<body>

<form action="{{URL::to('user/reset-password')}}" method="post" style="border:1px solid #ccc">
  <div class="container">
    <h1>MATcHup</h1>
    <p>Please fill in this form to reset password.</p>
    <hr>

     <?php if(isset($errors) && $errors->first()!=''){ ?> 
    
                           <div>
                               <strong style="color:red;"><?php echo $errors->first(); ?></strong>
                           </div>
                       
                       <?php }
                            $session = \Session::get("msg");
                           \Log::info($session);
                            if($session != "" && \General::is_json($session)){ 
                                $session = json_decode($session,true);
                                 \Log::info('session='+$session);
                                \Session::forget("msg");
                            ?>
                     
                          
                             <strong style="color:red;"><?php echo $session['msg']; ?></strong>
                             

                         
                        
                    
                    <?php } ?>

    
                         <input type="hidden" name='_token' value='{{csrf_token()}}'>
                          <input type="hidden" id="forgottoken" name="forgottoken" value="{{$body['forgorttoken']}}" class="form-control">
    <label for="psw"><b>Password</b></label>
    <input type="password" placeholder="Enter Password"  name="new_pass" required>

    <label for="psw-repeat"><b>Repeat Password</b></label>
    <input type="password" placeholder="Repeat Password" name="cnew_pass" required>
    

    <div class="clearfix">
<!--      <button type="button" class="cancelbtn">Cancel</button>-->
      <button type="submit" class="signupbtn">Reset</button>
    </div>
  </div>
</form>

</body>
</html>




