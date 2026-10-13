<!DOCTYPE html>
<html>
<head><title>New Contact Message</title></head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 24px; background-color: #f9fafb;">
    <div style="max-width: 600px; margin: 0 auto; padding: 24px; background-color: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px;">
    <div style="padding-bottom: 20px; margin-bottom: 24px; border-bottom: 1px solid #e5e7eb; text-align: center;">
        @include('emails.partials.logo')
    </div>
    <h2>New Inquiry from Website</h2>
    <p><strong>Name:</strong> {{ $contactData['first_name'] }} {{ $contactData['last_name'] }}</p>
    <p><strong>Email:</strong> {{ $contactData['email'] }}</p>
    <p><strong>Company:</strong> {{ $contactData['company'] ?? 'N/A' }}</p>
    <p><strong>Phone:</strong> {{ $contactData['phone'] ?? 'N/A' }}</p>
    <hr>
    <p><strong>Message:</strong></p>
    <p>{{ $contactData['message'] }}</p>
    </div>
</body>
</html>
