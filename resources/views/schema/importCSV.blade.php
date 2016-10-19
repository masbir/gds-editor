@extends('layouts.simple') 
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
			<select class="form-control" id="kind" name="kind">
				@foreach($kinds as $kind)
					<option value="{{ $kind->getKeyName() }}" {{ (old("kind") == $kind->getKeyName() ? 'selected' : '') }}>{{ $kind->getKeyName() }}</option>
				@endforeach
			</select>
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