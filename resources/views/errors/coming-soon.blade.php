<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Soon</title>
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
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .coming-soon-container {
            width: 100%;
        }
        .coming-soon-card {
            max-width: 700px;
            margin: 0 auto;
            background-color: var(--surface);
            border-radius: 20px;
            border: 1px solid var(--border);
        }
        .coming-soon-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            color: var(--primary);
        }
        .coming-soon-message {
            font-size: 1.2rem;
            line-height: 1.7;
            color: var(--text);
        }
        .countdown {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin: 2rem 0;
        }
        .countdown-item {
            background-color: var(--secondary);
            border-radius: 10px;
            padding: 1rem;
            min-width: 80px;
            border: 1px solid var(--border);
        }
        .countdown-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
            color: var(--primary);
        }
        .countdown-label {
            font-size: 0.9rem;
            opacity: 0.8;
            color: var(--text-secondary);
        }
        .card-title {
            color: var(--text);
        }
        .mt-4 p {
            color: var(--text);
        }
    </style>
</head>
<body>
    <div class="coming-soon-container">
        <div class="container">
            <div class="card coming-soon-card border-0">
                <div class="card-body text-center p-5">
                    <div class="coming-soon-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h1 class="card-title mb-4">Coming Soon</h1>
                    <div class="coming-soon-message">
                        @if(isset($setting) && $setting->coming_soon_message)
                            @php
                                $launchTimeFormatted = ($setting->launch_time) 
                                    ? \Carbon\Carbon::parse($setting->launch_time)->format('d/m/Y H:i') 
                                    : 'soon';
                                $message = str_replace('{launch_time}', $launchTimeFormatted, $setting->coming_soon_message);
                            @endphp
                            {!! nl2br(e($message)) !!}
                        @else
                            <p>We're launching soon! Our amazing platform will be available soon.</p>
                        @endif
                    </div>
                    
                    @if(isset($setting) && $setting->launch_time)
                    <div class="countdown" id="countdown">
                        <div class="countdown-item">
                            <span class="countdown-number" id="days">00</span>
                            <span class="countdown-label">Days</span>
                        </div>
                        <div class="countdown-item">
                            <span class="countdown-number" id="hours">00</span>
                            <span class="countdown-label">Hours</span>
                        </div>
                        <div class="countdown-item">
                            <span class="countdown-number" id="minutes">00</span>
                            <span class="countdown-label">Minutes</span>
                        </div>
                        <div class="countdown-item">
                            <span class="countdown-number" id="seconds">00</span>
                            <span class="countdown-label">Seconds</span>
                        </div>
                    </div>
                    @endif
                    
                    <div class="mt-4">
                        <p class="mb-0">Thank you for your patience!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($setting && $setting->launch_time)
    <script>
        // Set the date we're counting down to
        var countDownDate = new Date("{{ \Carbon\Carbon::parse($setting->launch_time)->format('M d, Y H:i:s') }}").getTime();

        // Update the count down every 1 second
        var x = setInterval(function() {
            // Get today's date and time
            var now = new Date().getTime();
            
            // Find the distance between now and the count down date
            var distance = countDownDate - now;
            
            // Time calculations for days, hours, minutes and seconds
            var days = Math.floor(distance / (1000 * 60 * 60 * 24));
            var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            // Output the result in elements with id
            document.getElementById("days").innerHTML = days.toString().padStart(2, '0');
            document.getElementById("hours").innerHTML = hours.toString().padStart(2, '0');
            document.getElementById("minutes").innerHTML = minutes.toString().padStart(2, '0');
            document.getElementById("seconds").innerHTML = seconds.toString().padStart(2, '0');
            
            // If the count down is over, hide the countdown
            if (distance < 0) {
                clearInterval(x);
                document.getElementById("countdown").innerHTML = "LAUNCHING NOW!";
            }
        }, 1000);
    </script>
    @endif
</body>
</html>