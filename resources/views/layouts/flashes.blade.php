@if(session('status'))
    <p class="flash ok">{{ session('status') }}</p>
@endif
@if(session('error'))
    <p class="flash bad">{{ session('error') }}</p>
@endif
@if($errors->any())
    <div class="flash bad">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
