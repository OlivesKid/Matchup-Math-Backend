<table class="table" style="border-top: 1px solid #ddd;">
      <thead>
        <tr>
          <th>#</th>
          <th>User Name</th>
          <th>Total Played</th>
          <th>Classic Played</th>
          <th>TimeMaster Played</th>
          <th>Average Score</th>
          <th>Average Score Classic</th>
          <th>Average Score TimeMaster</th>
          <th>High Score</th>
          <th>High Score Classic</th>
          <th>High Score TimeMaster</th>
          
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
              <td>{{$r['total_played']}}</td>
              <td>{{$r['classic_played']}}</td>
              <td>{{$r['timed_played']}}</td>
              
              <td>{{$r['avg_score']}}</td>
              <td>{{$r['avg_score_classic']}}</td>
              <td>{{$r['avg_score_timed']}}</td>
              
              <td>{{$r['high_score']}}</td>
              <td>{{$r['high_score_classic']}}</td>
              <td>{{$r['high_score_timed']}}</td>
              <td>{{date('d-m-Y', strtotime($r['updated_at']))}}</td>
            </tr>
          <?php 
              }
          }else{
             ?>
            <tr>
                <td colspan="12" style="text-align: center;font-weight: bold;"> Statics Not Found </td>
            </tr>
            <?php
          }
          ?>

      </tbody>
    </table>