@extends('layouts.admin')
@section('content')
<script>
    window.onload=function(){
        $("#<?php echo $body['id']?>").addClass("active");
    }
</script>
    <!-- Stats Panels -->
    <div class="row sortable">
      <div class="col-lg-3 col-md-3 col-sm-12">
        <a href="{{URL::to("admin/user-list")}}" class="card-panel stats-card red lighten-2 red-text text-lighten-5">
          <i class="fa fa-users"></i>
          <span class="count">{{$body['users']}}</span>
          <div class="name">Users</div>
        </a>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12">
        <a href="{{URL::to("admin/multi-player-challenge-report")}}" class="card-panel stats-card blue lighten-2 blue-text text-lighten-5">
          <i class="fa fa-bar-chart"></i>
          <span class="count">{{$body['totalMpChallenge']}}</span>
          <div class="name">Total MultiPlayer Challenges</div>
        </a>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12">
        <a href="{{URL::to("admin/multi-player-report")}}" class="card-panel stats-card amber lighten-2 amber-text text-lighten-5">
          <i class="fa fa-bar-chart"></i>
          <span class="count" style="color: black;">{{$body['totalMpPlayChallenge']}}</span>
          <div class="name" style="color: black;">Total MultiPlayer Played Game </div>
        </a>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12">
        <a href="{{URL::to("admin/single-player-report")}}" class="card-panel stats-card white lighten-2 white-text text-lighten-5">
          <i class="fa fa-bar-chart"></i>
          <span class="count">{{$body['totalSpChallenge']}}</span>
          <div class="name">Total SinglePlayer Challenges</div>
        </a>
      </div>
    </div>

@endsection
