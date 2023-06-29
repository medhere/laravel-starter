


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
    <form action="{{ route('signup') }}" method="post">
        <input type="text" name="name" placeholder="Name" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="tel" name="phone" placeholder="Password" required>
        <select name="gender" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
        </select>
        <input type="file" name="image" placeholder="Image" required>
        <input type="password" name="password" placeholder=" Password: 8 characters, mixed, 1 number, 1 special" required>
        <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
        <button type="submit">Submit</button>
    </form>

</div>
