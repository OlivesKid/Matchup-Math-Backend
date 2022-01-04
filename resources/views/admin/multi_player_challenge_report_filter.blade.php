<table class="table" style="border-top: 1px solid #ddd;">
      <thead>
        <tr>
          <th>#</th>
          <th>Type</th>
          <th>Challenger User Name</th>
          <th>Total Player</th>
          <th>Won User Id</th>
          <th>End Time</th>
          <th>Status</th>
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
              <?php
                  $clr = $r['type'] == 0 ? 'blue' :'orange'  ;
                  $title = $r['type'] == 0 ? 'Classic' : 'Time Master';
             ?>
              <td><span style="color:{{$clr}}" >{{$title}}</span></td>
              <td><a href="{{URL::to('admin/user-detail/').'/'.$r['user']['id']}}">{{$r['user']['username']}}</a></td>
              
              <td>{{$r['total_player']}}</td>
              <td><a href="{{URL::to('admin/user-detail/').'/'.$r['wonuser']['id']}}">{{$r['wonuser']['username']}}</a></td>
              
              <td>{{date('d-m-Y H:i:s', strtotime($r['end_time']))}}</td>
             
              
                <?php
                  $clr = $r['status'] == 0 ? 'red' :'green' ;
                  $title = $r['status'] == 0 ? 'Expire':'Active';
             ?>
               <td><span style="color:{{$clr}}" >{{$title}}</span></td>
              <td>{{date('d-m-Y', strtotime($r['created_at']))}}</td>
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