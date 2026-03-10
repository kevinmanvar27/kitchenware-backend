<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #FF6B00;
            --secondary: #F5F5F5;
            --background: #FFFFFF;
            --surface: #FFFFFF;
            --text: #333333;
            --text-secondary: #757575;
            --border: #E0E0E0;
            --success: #4CAF50;
            --error: #F44336;
            --warning: #FF9800;
        }
        
        body {
            background-color: var(--background);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
        }
        .maintenance-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .maintenance-card {
            max-width: 600px;
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            background-color: var(--surface);
            border: 1px solid var(--border);
        }
        .maintenance-icon {
            font-size: 4rem;
            color: var(--primary);
        }
        .maintenance-message {
            font-size: 1.1rem;
            line-height: 1.6;
            color: var(--text);
        }
        .text-muted {
            color: var(--text-secondary) !important;
        }
        .card-title {
            color: var(--text);
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="card maintenance-card border-0 shadow-sm">
            <div class="card-body text-center p-5">
                <div class="maintenance-icon mb-4">
                    <i class="fas fa-tools"></i>
                </div>
                <h1 class="card-title mb-4">Site Under Maintenance</h1>
                <div class="maintenance-message">
                    @if(isset($setting) && $setting->maintenance_message)
                        @php
                            $endTimeFormatted = ($setting->maintenance_end_time) 
                                ? \Carbon\Carbon::parse($setting->maintenance_end_time)->format('d/m/Y H:i') 
                                : 'soon';
                            $message = str_replace('{end_time}', $endTimeFormatted, $setting->maintenance_message);
                        @endphp
                        {!! nl2br(e($message)) !!}
                    @else
                        <p>We are currently under maintenance. The website will be back online soon.</p>
                    @endif
                </div>
                <div class="mt-4">
                    <small class="text-muted">We apologize for any inconvenience.</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>