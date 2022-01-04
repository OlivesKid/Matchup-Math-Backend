<table class="table" style="border-top: 1px solid #ddd;">
      <thead>
        <tr>
          <th>#</th>
          <th>User Name</th>
          <th>Challenge Id</th>
          <th>Game Played</th>
          <th>Score</th>
          <th>Result</th>
          <th>Play Date</th>
           <th>Type</th>
        </tr>
      </thead>
      <tbody>
          <?php
          
          if($total_record > 0 ){
              
              foreach($data as $r){
                  ?>
            <tr>
              <td>{{$r['id']}}</td>
              <td><a href="{{URL::to('admin/user-detail/').'/'.$r['user']['id']}}">{{$r['user']['username']}}</a></td>
              <td>{{$r['challenge_id']}}</td>
          
              <td>{{$r['game_played']}}</td>
              <td>{{$r['score']}}</td>
                  
               <?php

               $statusList=['0'=>'red|Lost','1'=>'green|Won','2'=>'blue|Tie','else'=>'darkgoldenrod|Pending'];
              $finalStatus=\General::status_class_msg($r['won_status'],$statusList);
             ?>
             
               <td><span style="color:{{$finalStatus['cls']}}" >{{$finalStatus['txt']}}</span></td>
              <td>{{date('d-m-Y', strtotime($r['created_at']))}}</td>
               <?php
                $statusList=['0'=>'fa fa-clock-o|Time Master','1'=>'fa fa-list|Classic'];
                $finalStatus=\General::status_class_msg($r['challenge']['type'],$statusList);
             ?>

               <td><i style="color:" title="{{$finalStatus['txt']}}" class="{{$finalStatus['cls']}}"></i></td>
            </tr>
          <?php 
              }
          }else{
             ?>
            <tr>
                <td colspan="7"  style="text-align: center;">No Challenges Played </td>
            </tr>
            <?php
          }
          ?>

      </tbody>
    </table>