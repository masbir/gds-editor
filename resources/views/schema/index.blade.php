@extends('layouts.simple')
@section('content')
	@if(count($processing) > 0)
		<div class="alert alert-info">
			@foreach($processing as $processing_item)
				<div>
					{{ ($processing_item->pct) }}% - 
					Importing <strong>{{ $processing_item->file }}</strong> to <strong>{{ $processing_item->kind }}</strong>
				</div>
			@endforeach
		</div>
	@endif

	<div class="panel panel-default">
		<div class="panel-heading clearfix">
			<div class="pull-right">
				<a class="btn btn-default" href="/kinds/insert">Insert</a>
				<a class="btn btn-default" href="/kinds/import">Import CSV</a>
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