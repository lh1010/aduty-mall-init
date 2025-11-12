@extends('default.shared._layout')
@section('title', 'AdutyCmf')
@section('keywords', 'AdutyCmf')
@section('description', 'AdutyCmf')
@section('pagecss')
<link rel="stylesheet" type="text/css" href="/static/default/style/index.css?v={{Config('common.version')}}" />
<style>
.mainbox {
  font-size: 22px;
  text-align: center;
  font-weight: bold;
  margin-top: 200px;
}
</style>
@endsection
@section('content')
<div class="mainbox">AdutyCmf</div>
@endsection
@section('pagejs')
<script>
console.log('AdutyCmf');
</script>
@endsection
