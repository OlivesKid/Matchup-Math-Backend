<table class="table" style="border-top: 1px solid #ddd;">
      <thead>
        <tr>
          <th>#</th>
          <th>User Name</th>
          <th>Game Played</th>
          <th>Won</th>
          <th>Tie</th>
          <th>Lost</th>
          <th>Total Score</th>
          <th>Last Played Date</th>
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
              <td>{{$r['played']}}</td>
              <td>{{$r['won']}}</td>
              <td>{{$r['tie']}}</td>
              <td>{{$r['lost']}}</td>
              <td>{{$r['total_score']}}</td>
              <td>{{date('d-m-Y', strtotime($r['updated_at']))}}</td>
            </tr>
          <?php 
              }
          }else{
             ?>
            <tr>
                <td colspan="8" style="text-align: center;font-weight: bold;"> Statics Not Found </td>
            </tr>
            <?php
          }
          ?>

      </tbody>
    </table>