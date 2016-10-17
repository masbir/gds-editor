@extends('layouts.simple')
@section('content')
	<div class="panel panel-default">
		<div class="panel-heading clearfix">
			<div class="pull-right">
				<a class="btn btn-default" href="/kinds/{{ $name }}/insert">Insert</a>
			</div>
			{{ $name }}
		</div>
		<table class="table">
			<thead>
				<tr>
					<th style="width:100px;">
						ID
					</th>
					@foreach($merged_columns as $column)
						<th>{{ $column }}</th>
					@endforeach
				</tr>
			</thead>
			<tbody>
				@foreach($result as $item)
					<tr>
						<td title="$item->getKeyId()">
							{{ str_limit($item->getKeyId(), 3) }}
						</td>
						@foreach($merged_columns as $column)
							@if(array_key_exists($column, $item->getData()))
								<td>{{ $item->getData()[$column] }}</td>
							@else
								<td></td>
							@endif
						@endforeach
					</tr>
				@endforeach
			</tbody>
		</table>
		<div class="panel-body">
			<div class="text-center">
				<div class="btn-group" role="group" aria-label="...">
					@if($page > 1)
						<a class="btn btn-default" href="?page={{ ($page - 1) }}">Previous</a>
					@endif
					<span class="btn btn-default">Page {{ $page }}</span>
					<a class="btn btn-default" href="?page={{ ($page + 1) }}">Next</a>
				</div>
			</div>
		</div>
	</div>
@stop