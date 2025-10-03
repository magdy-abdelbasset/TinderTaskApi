<!DOCTYPE html>
<html>
<head>
    <title>Popular User Alert</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #ff4458;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 0 0 8px 8px;
        }
        .user-details {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .highlight {
            color: #ff4458;
            font-weight: bold;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔥 Popular User Alert!</h1>
    </div>
    
    <div class="content">
        <p>Hello Admin,</p>
        
        <p>User <span class="highlight">{{ $user->name }}</span> (ID: {{ $user->id }}) has received <span class="highlight">{{ $likeCount }} likes</span>!</p>
        
        <div class="user-details">
            <h3>User Details:</h3>
            <ul>
                <li><strong>Name:</strong> {{ $user->name }}</li>
                <li><strong>Age:</strong> {{ $user->age }}</li>
                <li><strong>Location:</strong> {{ $user->location }}</li>
                <li><strong>Member since:</strong> {{ $user->created_at->format('F j, Y') }}</li>
                <li><strong>Total likes received:</strong> {{ $likeCount }}</li>
            </ul>
        </div>
        
        <p>This user might need special attention or could be featured as a popular user on the platform.</p>
        
        <p>Best regards,<br>
        Tinder Task API System</p>
    </div>
</body>
</html>
