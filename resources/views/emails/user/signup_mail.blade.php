<html>
   <head>
      <title>{{$mail_subject}}</title>
   </head>
   <body>
      <p></p>
      <div style="margin: 0;">
         <div style="background: #f2f2f2; margin: 0 auto; max-width: 640px; padding: 0 20px;">
            <table border="0" cellspacing="0" cellpadding="0" align="center">
               <tbody>
                  <tr>
                     <td>&nbsp;</td>
                  </tr>
                  <tr>
                     <td>
                        <div style="width: 96%; margin: auto; padding: 5px 0 0px 0;">
                           <img style="text-align: center; margin: 20px 0 0 0;" src="{{\URL::to("assets/images/logo.png")}}" alt="{{config('constant.COIN_NAME')}}" />
                        </div>
                        <div style="background: #fff; color: #5b5b5b; border-radius: 4px; font-family: arial; font-size: 15px; padding: 10px 20px; width: 90%; margin: 20px auto; line-height: 17px; border: 1px #ddd solid; border-top: 0; clear: both;">
                           <p>Hi {{$to_name}}, </p>
                           <p>
                              Help us secure your {{strtoupper(config('constant.PLATFORM_NAME'))}} account by verifying you <br>
                              email address (<span style="color:green">{{$email}}</span>).
                           </p>
                           <p style="text-align: center;margin-top: 22px;">
                              <a href="{{URL::to('user/email-verify')}}/{{$activation_token}}" style="padding:7px;background-color: green;color:white;text-align: center;border-radius: 5px;text-decoration: none">Verify email address</a>
                           </p>
                        </div>
                        <div style=" color: #5b5b5b;font-family: arial; font-size: 13px; padding: 0px 20px; width: 90%; margin: 0px auto; line-height: 17px; clear: both;">
                           <p>
                              If the button does not work, just copy and paste the link below into your Internet browser.
                           </p>
                           <p style="color:green">{{URL::to('user/email-verify')}}/{{$activation_token}}</p>
                        </div>
                        <div style=" color: #5b5b5b;font-family: arial; font-size: 13px; padding: 0px 20px; width: 90%; margin: 0px auto; line-height: 17px; clear: both;">
                           <p>

                              You are receiving this email because you recently created a new MATcHup account. If this was not you, please ignore this email.

                           </p>
                        </div>
                        <table border="0" width="100%" cellspacing="0" cellpadding="0">
                           <tbody>
                              <tr>
                                 <td>
                                    <table border="0" width="100%" cellspacing="0" cellpadding="0">
                                       <tbody>
                                          <tr>
                                             <td style="font-family: 'Open Sans',Arial,sans-serif; font-size: 15px;" width="82%">&nbsp;</td>
                                             <td rowspan="2" valign="top" width="18%">
                                                <table border="0" width="102" cellspacing="0" cellpadding="0" align="right">
                                                   <tbody>
                                                      <tr>
                                                         <td align="center"><a href="{{config("constant.FACEBOOK_PAGE_URL")}}" target="_blank" rel="noopener"> <img src="{{\URL::to("assets/images/email/fb.png")}}" alt="{{config('constant.COIN_NAME')}}" width="31" height="31" border="0" /> </a></td>
                                                         <td align="center"><a href="{{config("constant.INSTAGRAM_PAGE_URL")}}" target="_blank" rel="noopener"> <img src="{{\URL::to("assets/images/email/insta.png")}}" alt="{{config('constant.PLATFORM_NAME')}}" width="31" height="31" border="0" /> </a></td>
                                                         <td align="right"><a href="{{config("constant.TWITTER_PAGE_URL")}}" target="_blank" rel="noopener"> <img src="{{\URL::to("assets/images/email/twitter.png")}}" alt="{{config('constant.COIN_NAME')}}" width="31" height="31" border="0" /> </a></td>
                                                      </tr>
                                                   </tbody>
                                                </table>
                                             </td>
                                          </tr>
                                          <tr>
                                             <td style="font-family: 'Open Sans',Arial,sans-serif; font-size: 8.5pt;">We would love to hear from you. Just write to us at <a href="mailto:{{config('constant.SUPPORT_MAIL')}}">{{config('constant.SUPPORT_MAIL')}}</a></td>
                                          </tr>
                                          <tr>
                                             <td style="font-family: 'Open Sans',Arial,sans-serif; font-size: 8.5pt;">&nbsp;</td>
                                          </tr>
                                       </tbody>
                                    </table>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </td>
                  </tr>
               </tbody>
            </table>
         </div>
      </div>
   </body>
</html>