@if($errors->any())
    <br/>
    <div class="alert alert-danger">
        <ul class="list-group">
            @foreach($errors->all() as $error)
                <li class="list-group">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
