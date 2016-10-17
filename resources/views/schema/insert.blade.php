@extends('layouts.simple')
@section('scripts')
    <script>
    $(function(){
        $("#btnAddProperty").click(function(ev){
            ev.preventDefault();
            $(".template").clone().appendTo("#properties").removeClass("template").show();
        })
        $("#properties").on("click", ".delete", function(){ 
            $(this).closest(".property-row").remove();
        })
    });
    </script>
@stop
@section('content')
    <h2>Insert {{ $name }}</h1>
    <form method="POST">
        {{ csrf_field() }} 
        <div class="form-group">
            <label for="kind_name">Kind</label>
            <input type="text" class="form-control" id="kind_name" value="{{ $name }}" name="name">
        </div>

        <h3>Properties</h3>
        <div id="properties">
            <div class="form-group template property-row" style="display:none;">
                <div class="row">
                    <div class="col-sm-3">
                        <input type="text" class="form-control" placeholder="Name" name="keys[]">
                    </div>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" placeholder="Value" name="values[]">
                    </div>
                    <div class="col-sm-1">
                        <button class="btn btn-danger delete" type="button">&times;</button>
                    </div>
                </div>
            </div> 
        </div>
        <div>
            <button class="btn btn-default" type="button" id="btnAddProperty">Add property</button>
        </div>

        <div style="margin-top:20px;">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
@stop