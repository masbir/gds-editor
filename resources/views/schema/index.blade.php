@extends('layouts.simple')
@section('content')
	<div class="panel panel-default">
		<div class="panel-heading clearfix">
			<div class="pull-right">
				<a class="btn btn-default" href="/kinds/insert">Insert</a>
			</div>
			Kinds
		</div> 
		<table class="table">
			<thead>
				<tr>
					<th>
						Name
					</th> 
				</tr>
			</thead>
			<tbody>
				@foreach($kinds as $kind)
					<tr>
						<td>
							<a href="/kinds/{{ $kind->getKeyName() }}">{{ $kind->getKeyName() }}</a>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table> 
	</div>
@stop