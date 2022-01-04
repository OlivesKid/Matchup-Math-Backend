<table class="table" style="border-top: 1px solid #ddd;">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Date Of Year</th>
          <th>Action</th>
          
        </tr>
      </thead>
      <tbody>

          <?php
          if($total_record > 0 ){
              
              foreach($data as $r){
                  $clr = $r['status'] == 1 ? 'green' : ($r['status'] == 2 ? 'orange' : ($r['status'] == 3 ? 'red' : 'blue'));
                  $title = $r['status'] == 1 ? 'Active' : ($r['status'] == 2 ? 'Pending' : ($r['status'] == 3 ? 'Suspended' : 'In Active'));
                  ?>
            <tr>
              <td>{{$r['id']}}</td>
              <td><a href="{{URL::to('admin/user-detail/').'/'.$r['id']}}" style="color:{{$clr}}" title="{{$title}}">{{$r['username']}}</a></td>
              <td>{{$r['email']}}</td>
              <td>{{$r['dob']}}</td>
              <th>
                <span class="dico" tooltip="Delete" onclick="deleteRowData(`{{$r['id']}}`);" style="cursor: pointer;"><i class="mdi-action-delete m-t-sm" style="font-size: 18px;"></i>
            </span>
              </th>
            </tr>
          <?php 
              }
          }else{
             ?>
            <tr>
                <td colspan="4"  style="text-align: center;font-weight: bold;">No users Found</td>
            </tr>
            <?php
          }
          ?>
        
        
      </tbody>
    </table>