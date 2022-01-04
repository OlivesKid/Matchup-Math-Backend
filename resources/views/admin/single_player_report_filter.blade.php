<table class="table" style="border-top: 1px solid #ddd;">
      <thead>
        <tr>
          <th>#</th>
          <th>User Name</th>
          <th>Type</th>
          <th>Score</th>
          <th>Time</th>
          <th>skip</th>
          <th>Result</th>
          <th>Play Date</th>
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
              <?php
                   $clr = $r['type'] == 0 ? 'blue' :'orange'  ;
                  $title = $r['type'] == 0 ? 'Classic' : 'Time Master';
             ?>
              <td><span style="color:{{$clr}}" >{{$title}}</span></td>
                <td>{{$r['score']}}</td>

              <?php
                $statusList=['0'=>'|-','1'=>'|'.$r['time']];
                $finalStatus=\General::status_class_msg($r['type'],$statusList);
             ?> 

              <td>{{$finalStatus['txt']}}</td>
              <td>{{$r['skip']}}</td>
                 <?php
                  $clr = $r['won_status'] == 0 ? 'red' :'green'  ;
                  $title = $r['won_status'] == 0 ? 'Lost' : 'Won';
                ?>
               <td><span style="color:{{$clr}}" >{{$title}}</span></td>
              
              <td>{{date('d-m-Y', strtotime($r['created_at']))}}</td>
            </tr>
          <?php 
              }
          }else{
             ?>
            <tr>
                <td colspan="8" style="text-align: center;font-weight: bold;">No Challenges Played </td>
            </tr>
            <?php
          }
           
           
          ?>
        
        
      </tbody>
    </table>