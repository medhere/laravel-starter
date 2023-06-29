


@if(session('success'))
    {{session('success')}}
@endif

@if ($errors->any())
<div>
    <strong>Whoops!</strong> There were some problems with your input:<br><br>
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div>
    <form action="{{ route('forgotpassword') }}" method="post">
        <input type="text" name="info" placeholder="Email/Password/Username" required>
        <button type="submit">Submit</button>
    </form>
</div>
