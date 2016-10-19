@extends('layouts.simple') 
@section('scripts')
	<script>
		$(function(){
			$(".dropdown-menu a").click(function(ev){
				ev.preventDefault();
	            $(this).closest('.dropdown')
	                .find('.target-input')
	                .val($(this).attr('data-value'));
	        }); 
		})
	</script>
@stop
@section('content')

    <h2>Import CSV</h1>
    <form method="POST" enctype="multipart/form-data">
        {{ csrf_field() }} 

		<div class="form-group {{ $errors->has('csvFile') ? 'has-error' : '' }}">
			<label for="csvFile">CSV file</label>
			<input type="file" id="csvFile" name="csvFile" value="{{ old('csvFile') }}">
			@if ($errors->has('csvFile')) 
				<p class="help-block">{{ $errors->first('csvFile') }}</p> 
			@endif
		</div>

		<div class="form-group {{ $errors->has('kind') ? 'has-error' : '' }}">
			<label for="kind">Kind</label>

			<div class="input-group dropdown">
                <input class="target-input form-control" type="text" name="kind" value="{{ old("kind") }}" />

                <ul class="dropdown-menu">
                    @foreach($kinds as $kind)
                        <li><a href="#" data-value="{{ $kind->getKeyName() }}">{{ $kind->getKeyName() }}</a></li>
                    @endforeach
                </ul> 
                <span role="button" class="input-group-addon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></span>
			</div> 

			@if ($errors->has('kind')) 
				<p class="help-block">{{ $errors->first('kind') }}</p> 
			@endif
		</div>

		<div class="{{ $errors->has('hasHeader') ? 'has-error' : '' }}">
			<div class="checkbox">
				<label>
					<input type="checkbox" name="hasHeader" value="1" {{ (old("hasHeader") == "1" ? 'checked' : '') }}> This file contains header on line 1
				</label>
				@if ($errors->has('hasHeader')) 
					<p class="help-block">{{ $errors->first('hasHeader') }}</p> 
				@endif
			</div> 
		</div>

        <div style="margin-top:20px;">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
@stop