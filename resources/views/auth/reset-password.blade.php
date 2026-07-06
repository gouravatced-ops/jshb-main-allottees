<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <title>Reset Password | {{ config('panel.portal_name') ?? 'Jharkhand Housing Board' }}</title>
  <meta name="description" content="Jharkhand Housing Board - Official management login portal" />
  <link rel="shortcut icon" type="image/x-icon" href="{{ asset(config('panel.faviconIcon')) }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/font/font.css') }}">
  <link rel="stylesheet" href="{{ asset('css/icons/all.css') }}">
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="ref-login-body">
    
    <div class="ref-main-wrapper">
        <div class="ref-login-box">
            
            <div class="ref-header">
                <div class="ref-header-left">
                    <img src="{{ asset(config('panel.logo')) }}" alt="Logo" onerror="this.src='https://placehold.co/100x70/ffffff/881326?text=Logo'">
                    <div class="ref-header-titles">
                        <h2>{{ config('panel.organization_hindi') }}</h2>
                        <h1>{{ config('panel.organization') }}</h1>
                    </div>
                </div>
                <div class="ref-header-right">
                    @if(config('panel.govermentLogo'))
                        <img src="{{ asset(config('panel.govermentLogo')) }}" alt="State Government Logo" style="height: 60px; margin-right: 15px;" onerror="this.style.display='none'">
                    @endif
                    <div class="ref-portal-badge">
                        <i class="fa-solid fa-desktop"></i> Member Portal
                    </div>
                </div>
            </div>

            <div class="ref-content">
                <div class="ref-content-left">
                    <h3>नया पासवर्ड बनाएँ</h3>
                    <ul>
                        <li>आपका ओ.टी.पी. सफलतापूर्वक सत्यापित हो गया है।</li>
                        <li>अपना नया पासवर्ड (new password) और पुष्टि पासवर्ड (confirm password) दर्ज करें और सुरक्षित करें।</li>
                        <li>पासवर्ड में कम से कम 8 अक्षर होने चाहिए।</li>
                        <li>पासवर्ड सफलतापूर्वक सेव होने के बाद, कृपया अपने नए पासवर्ड के साथ पोर्टल पर फिर से लॉग इन करें।</li>
                    </ul>
                </div>
                
                <div class="ref-divider"></div>
                
                <div class="ref-content-right">
                    @if (session('success'))
                    <div style="color: #15803d; background: #f0fdf4; padding: 10px; border: 1px solid #bbf7d0; margin-bottom: 15px;">
                        <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
                    </div>
                    @endif

                    @if ($errors->any())
                    <div style="color: #b91c1c; background: #fef2f2; padding: 10px; border: 1px solid #fecaca; margin-bottom: 15px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> {{ $errors->first() }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('password.store') }}">
                        @csrf
                        
                        <div class="ref-input-group">
                            <label for="email">पंजीकृत ईमेल</label>
                            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" readonly required style="background: #f1f5f9; color: #475569;">
                        </div>

                        <div class="ref-input-group" style="position: relative;">
                            <label for="password">नया पासवर्ड</label>
                            <input id="password" name="password" type="password" placeholder="Enter new password" required style="padding-right: 2.5rem;">
                            <i id="togglePassword" class="fa-solid fa-eye-slash toggle-pwd" style="position: absolute; right: 10px; bottom: 12px; cursor: pointer; color: #64748b;"></i>
                        </div>

                        <div class="ref-input-group">
                            <label for="password_confirmation">पुष्टि पासवर्ड</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Confirm new password" required>
                        </div>

                        <button type="submit" class="ref-login-btn" id="loginBtn">
                            पासवर्ड रीसेट करें
                        </button>
                    </form>

                    <div style="margin-top: 1.5rem; font-size: 0.95rem; text-align: center;">
                        <a href="{{ route('login') }}" style="color: #881326; text-decoration: none; font-weight: 600;">
                            <i class="fa-solid fa-arrow-left"></i> वापस लॉगिन पर जाएँ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="maroon-footer">
        <div>Site is designed by <a href="https://www.computered.in/" target="_blank" style="text-decoration: none; color:white;">Computer Ed.</a> © {{ config('panel.organization') }}.</div>
        <div class="maroon-footer-social">
            Tech Partner
            <a href="https://www.computered.in/" target="_blank" rel="noopener noreferrer" class="partner-badge">
              <img src="{{ asset(config('panel.techpatrnterLogo')) }}" width="20px" alt="Computer Ed">
            </a> | 
            <i class="fa-brands fa-facebook-f"></i>
            <i class="fa-brands fa-youtube"></i>
            <i class="fa-brands fa-x-twitter"></i>
            <i class="fa-brands fa-instagram"></i>
        </div>
    </div>

    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>