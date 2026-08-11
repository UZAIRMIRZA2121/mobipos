<div>
    <p>A new user has registered on the system.</p>
    <p><strong>User Details:</strong></p>
    <ul>
        <li>Name: {{ $user->name }}</li>
        <li>Email: {{ $user->email }}</li>
        <li>Type: {{ $user->type }}</li>
    </ul>
    <p>Their account is currently in a 7-day trial period.</p>
    <p>To approve their account, provide them with the following OTP when they contact you:</p>
    <h2 style="color: #3b82f6;">{{ $otp }}</h2>
</div>
