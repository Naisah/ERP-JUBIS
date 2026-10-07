<!DOCTYPE html>
<html>
<head><title>New Contact Message</title></head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <h2>New Inquiry from Website</h2>
    <p><strong>Name:</strong> {{ $contactData['first_name'] }} {{ $contactData['last_name'] }}</p>
    <p><strong>Email:</strong> {{ $contactData['email'] }}</p>
    <p><strong>Company:</strong> {{ $contactData['company'] ?? 'N/A' }}</p>
    <p><strong>Phone:</strong> {{ $contactData['phone'] ?? 'N/A' }}</p>
    <hr>
    <p><strong>Message:</strong></p>
    <p>{{ $contactData['message'] }}</p>
</body>
</html>
