<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <title>Forgot Password | {{ config('panel.portal_name') ?? 'Jharkhand Housing Board' }}</title>
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
                    <div class="ref-portal-badge">
                        <i class="fa-solid fa-desktop"></i> Member Portal
                    </div>
                </div>
            </div>

            <div class="ref-content">
                <div class="ref-content-left">
                    <h3>पासवर्ड रीसेट करने की प्रक्रिया</h3>
                    <ul>
                        <li>अपना सत्यापित (verified) ईमेल दर्ज करें और सुरक्षा कैप्चा भरें, फिर आपको अपने ईमेल पर एक लिंक / ओ.टी.पी. प्राप्त होगा।</li>
                        <li>प्राप्त लिंक / ओ.टी.पी. के माध्यम से अपना नया पासवर्ड (new password) और पुष्टि पासवर्ड (confirm password) दर्ज करें, और कैप्चा के साथ सेव करें।</li>
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

                    @php
                    $otpRequired = session('otp_required', session('password_reset_otp_required', false));
                    $emailValue = old('email', session('email', session('password_reset_email', '')));
                    @endphp

                    <form method="POST" action="{{ $otpRequired ? route('password.verify-otp') : route('password.email') }}">
                        @csrf
                        
                        <div class="ref-input-group">
                            <label for="email">पंजीकृत ईमेल</label>
                            <input id="email" name="email" type="email" value="{{ $emailValue }}" placeholder="अपना पंजीकृत ईमेल डालें" required>
                        </div>

                        @if($otpRequired)
                        <div class="ref-input-group">
                            <label for="otp_code">ओ.टी.पी. कोड (OTP)</label>
                            <input id="otp_code" name="otp_code" type="text" value="{{ old('otp_code') }}" placeholder="6-digit OTP दर्ज करें" inputmode="numeric" pattern="\d*" required>
                        </div>
                        @endif

                        <div class="ref-input-group">
                            <label for="captcha_input">कैप्चा डालें</label>
                            <div class="ref-captcha-row">
                                <div id="captchaCode" class="ref-captcha-box">JH42K</div>
                                <button type="button" class="ref-captcha-refresh" id="refreshCaptcha" title="Refresh"><i class="fa-solid fa-arrows-rotate"></i></button>
                            </div>
                            <input id="captcha_input" name="captcha_input" type="text" placeholder="कैप्चा डालें" autocomplete="off" required>
                        </div>

                        <button type="submit" class="ref-login-btn" id="loginBtn" disabled>
                            {{ $otpRequired ? 'सत्यापित करें' : 'ओ.टी.पी. / लिंक प्राप्त करें' }}
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
        <div>Site is designed & hosted by National Informatics Centre © Jharkhand Housing Board.</div>
        <div class="maroon-footer-social">
            <i class="fa-brands fa-facebook-f"></i>
            <i class="fa-brands fa-youtube"></i>
            <i class="fa-brands fa-x-twitter"></i>
            <i class="fa-brands fa-instagram"></i>
        </div>
    </div>

    <script src="{{ asset('js/login.js') }}"></script>
</body>
</html>