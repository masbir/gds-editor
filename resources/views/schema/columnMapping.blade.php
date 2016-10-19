@extends('layouts.simple') 
@section('scripts')
    <script>
    $(function(){
        $("#btn-add-new-column").click(function(ev){
            ev.preventDefault();
            var cloned = $("#property-0").clone()
            	.insertBefore("#tr-add-new-column");
            cloned
            	.removeAttr("id")
            	.find(".prop-input").attr("type", "text").val("").focus().end()
            	.find(".col-select").val(-1).end()
            	.find(".prop-label").remove();
        });
        $("#table-props").on("click", ".btn-delete-row", function(ev){
            ev.preventDefault();
            $(this).closest("tr").remove();
        }).on("click", ".dropdown-menu a", function(ev){
            ev.preventDefault();
            $(this).closest('.dropdown')
                .find('.prop-input')
                .val($(this).attr('data-value'));
        }); 
    });
    </script>
@stop
@section('content')
    <h2>Map CSV</h1>
    <!--<div class="alert alert-info" role="alert">
        You're importing <strong>{{ $importSession->originalFileName }}</strong> to <strong>{{ $importSession->kind }}</strong>

        <a href="/kinds/import/cancel" class="btn btn-danger">Cancel Import</a>
    </div>-->
    <form method="POST" enctype="multipart/form-data">
        {{ csrf_field() }} 

        <table class="table" id="table-props">
        	<thead>
        		<tr>
        			<th>
        				Column
        			</th>
        			<th>
        				Map to..
        			</th>
                    <th style="width:50px;"> 
                    </th>
        		</tr>
        	</thead>
        	<tbody>
        		<tbody>
        			@foreach($properties as $propKey => $property)
        			<tr id="property-{{ $propKey }}">
        				<td>
                            <div class="input-group dropdown">
                                <input class="prop-input form-control" type="text" name="properties[]" value="{{ $property }}" />

                                <ul class="dropdown-menu">
                                    @foreach($properties as $properySelector) 
                                        <li><a href="#" data-value="{{ $properySelector }}">{{ $properySelector }}</a></li>
                                    @endforeach
                                </ul> 
                                <span role="button" class="input-group-addon dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></span>
        					</div> 
        				</td>
        				<td>
        					<select class="form-control col-select" name="mappedColumns[]"> 
								@foreach($importSession->firstRow as $colKey => $column)
                                    @if($importSession->hasHeader)
                                        <option value="{{ $colKey }}" {{ $importSession->header[$colKey] == $property ? 'selected' : '' }}>{{ $importSession->header[$colKey] }} (e.g. : "{{ $column }}")</option>
                                    @else
                                        <option value="{{ $colKey }}" {{ $colKey == $propKey ? 'selected' : '' }}>"{{ $column }}"</option>
                                    @endif
								@endforeach
							</select>
        				</td>
                        <td>
                            <button class="btn btn-danger btn-delete-row" type="button">&times;</button>
                        </td>
        			</tr>
        			@endforeach
        			<tr id="tr-add-new-column">
        				<td colspan="2">
        					<button class="btn btn-default" id="btn-add-new-column">Add new column</button>
        				</td>
        			</tr>
        		</tbody>
        	</tbody>
        </table> 

        <div style="margin-top:20px;">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
@stop